<?php

declare(strict_types=1);

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Src\Database\Database;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must run in CLI mode." . PHP_EOL);
    exit(1);
}

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

if (!is_file(PATH_AUTOLOAD)) {
    fwrite(STDERR, "Composer autoload not found. Run 'composer install'." . PHP_EOL);
    exit(1);
}

require_once PATH_AUTOLOAD;

$sqlDirectory = PATH_SQL_DIRECTORY;
$databaseClassPath = PATH_DATABASE_CLASS;
$logDirectory = PATH_LOG_DIRECTORY;
$logFilePath = PATH_LOG_MIGRATE;
$runAgain = hasCliArgument($argv, 'again') || hasCliArgument($argv, '--again');

$logger = null;

try {
    $logger = createLogger($logDirectory);
    $logger->info('Migration started', [
        'sql_directory' => $sqlDirectory,
        'mode' => $runAgain ? 'again' : 'migrate',
    ]);

    if (!class_exists(Database::class)) {
        if (!is_file($databaseClassPath)) {
            $message = 'Database class file not found in src/Database/Database.php.';
            $logger->error($message, ['class_path' => $databaseClassPath]);
            fwrite(STDERR, 'ERRO: ' . $message . PHP_EOL);
            fwrite(STDERR, 'Log: ' . $logFilePath . PHP_EOL);
            exit(1);
        }

        require_once $databaseClassPath;
    }

    if (!class_exists(Database::class)) {
        $message = 'Database class Src\\Database\\Database not loaded.';
        $logger->error($message, ['class_path' => $databaseClassPath]);
        fwrite(STDERR, 'ERRO: ' . $message . PHP_EOL);
        fwrite(STDERR, 'Log: ' . $logFilePath . PHP_EOL);
        exit(1);
    }

    $pdo = Database::connection();

    if ($runAgain) {
        $dropResult = dropTablesForFreshMigration($pdo, $sqlDirectory, $logger);

        if ($dropResult['ok'] !== true) {
            $dropError = $dropResult['error'];
            $dropErrorFile = (string) ($dropError['file'] ?? 'unknown');
            $dropErrorMessage = (string) ($dropError['message'] ?? 'Unknown error');

            fwrite(STDERR, 'ERRO em ' . $dropErrorFile . ': ' . $dropErrorMessage . PHP_EOL);
            fwrite(STDERR, 'Log: ' . $logFilePath . PHP_EOL);
            exit(1);
        }
    }

    $result = runMigrations($pdo, $sqlDirectory, $logger);

    if ($result['ok'] === true) {
        $logger->info('Migration finished successfully', [
            'created' => $result['created'],
            'skipped' => $result['skipped'],
        ]);
        echo 'SUCESSO' . PHP_EOL;
        exit(0);
    }

    $error = $result['error'];
    $errorFile = (string) ($error['file'] ?? 'unknown');
    $errorMessage = (string) ($error['message'] ?? 'Unknown error');
    $errorTable = (string) ($error['table'] ?? 'unknown');

    $logger->error('Migration finished with error', [
        'created' => $result['created'],
        'skipped' => $result['skipped'],
        'failed' => $result['failed'],
        'file' => $errorFile,
        'table' => $errorTable,
        'error' => $errorMessage,
    ]);

    fwrite(STDERR, 'ERRO em ' . $errorFile . ': ' . $errorMessage . PHP_EOL);
    fwrite(STDERR, 'Log: ' . $logFilePath . PHP_EOL);
    exit(1);
} catch (Throwable $exception) {
    if ($logger instanceof Logger) {
        $logger->critical('Fatal migration error', [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }

    fwrite(STDERR, 'ERRO: ' . $exception->getMessage() . PHP_EOL);
    fwrite(STDERR, 'Local: ' . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL);
    fwrite(STDERR, 'Log: ' . $logFilePath . PHP_EOL);
    exit(1);
}

function createLogger(string $logDirectory): Logger
{
    if (!is_dir($logDirectory) && !mkdir($logDirectory, 0775, true) && !is_dir($logDirectory)) {
        throw new RuntimeException('Could not create log directory: ' . $logDirectory);
    }

    $logFilePath = PATH_LOG_MIGRATE;

    $logger = new Logger('database-migrate');
    $handler = new StreamHandler($logFilePath, Level::Info);
    $formatter = new LineFormatter(
        "[%datetime%] %level_name%: %message% %context%" . PHP_EOL,
        'Y-m-d H:i:s',
        true,
        true
    );

    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);

    return $logger;
}

function hasCliArgument(array $arguments, string $needle): bool
{
    foreach ($arguments as $argument) {
        if ((string) $argument === $needle) {
            return true;
        }
    }

    return false;
}

function dropTablesForFreshMigration(PDO $pdo, string $sqlDirectory, Logger $logger): array
{
    if (!is_dir($sqlDirectory)) {
        throw new RuntimeException('SQL directory not found: ' . $sqlDirectory);
    }

    $files = glob($sqlDirectory . DIRECTORY_SEPARATOR . '*.sql');

    if ($files === false || $files === []) {
        $logger->warning('No SQL files found for migrate again', ['sql_directory' => $sqlDirectory]);
        return [
            'ok' => true,
            'error' => null,
        ];
    }

    sort($files, SORT_NATURAL | SORT_FLAG_CASE);

    $tableToFile = [];

    foreach ($files as $filePath) {
        $fileName = basename($filePath);
        $sql = trim((string) file_get_contents($filePath));

        if ($sql === '') {
            continue;
        }

        $tableName = extractTableName($sql);

        if ($tableName === null) {
            $logger->warning('Could not extract table name for drop', ['file' => $fileName]);
            continue;
        }

        $tableToFile[$tableName] = $fileName;
    }

    if ($tableToFile === []) {
        $logger->warning('No tables extracted for migrate again drop step');
        return [
            'ok' => true,
            'error' => null,
        ];
    }

    $tables = array_reverse(array_keys($tableToFile));
    $currentTable = 'unknown';

    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

    try {
        foreach ($tables as $tableName) {
            $currentTable = $tableName;

            if (preg_match('/^[a-zA-Z0-9_]+$/', $tableName) !== 1) {
                $logger->warning('Skipped table drop with invalid table name', [
                    'table' => $tableName,
                    'file' => $tableToFile[$tableName] ?? null,
                ]);
                continue;
            }

            $pdo->exec('DROP TABLE IF EXISTS `' . $tableName . '`');
            $logger->info('Dropped table for migrate again', [
                'table' => $tableName,
                'file' => $tableToFile[$tableName] ?? null,
            ]);
        }
    } catch (PDOException $exception) {
        $logger->error('Drop tables failed on migrate again', [
            'table' => $currentTable,
            'file' => $tableToFile[$currentTable] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);

        return [
            'ok' => false,
            'error' => [
                'file' => $tableToFile[$currentTable] ?? 'unknown',
                'table' => $currentTable,
                'message' => $exception->getMessage(),
            ],
        ];
    } finally {
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        } catch (PDOException $exception) {
            $logger->warning('Could not restore FOREIGN_KEY_CHECKS after migrate again', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    return [
        'ok' => true,
        'error' => null,
    ];
}

function runMigrations(PDO $pdo, string $sqlDirectory, Logger $logger): array
{
    if (!is_dir($sqlDirectory)) {
        throw new RuntimeException('SQL directory not found: ' . $sqlDirectory);
    }

    $files = glob($sqlDirectory . DIRECTORY_SEPARATOR . '*.sql');

    if ($files === false || $files === []) {
        $logger->warning('No SQL files found', ['sql_directory' => $sqlDirectory]);
        return [
            'ok' => true,
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
            'error' => null,
        ];
    }

    sort($files, SORT_NATURAL | SORT_FLAG_CASE);

    $created = 0;
    $skipped = 0;

    foreach ($files as $filePath) {
        $fileName = basename($filePath);
        $sql = trim((string) file_get_contents($filePath));

        if ($sql === '') {
            $logger->warning('Skipped empty SQL file', ['file' => $fileName]);
            $skipped++;
            continue;
        }

        $tableName = extractTableName($sql);

        if ($tableName !== null && tableExists($pdo, $tableName)) {
            $logger->info('Skipped migration because table already exists', [
                'file' => $fileName,
                'table' => $tableName,
            ]);
            $skipped++;
            continue;
        }

        try {
            $pdo->exec($sql);
            $logger->info('Migration executed', ['file' => $fileName, 'table' => $tableName]);
            $created++;
        } catch (PDOException $exception) {
            if (isTableAlreadyExistsError($exception)) {
                $logger->info('Skipped migration due to table already exists exception', [
                    'file' => $fileName,
                    'table' => $tableName,
                    'error' => $exception->getMessage(),
                ]);
                $skipped++;
                continue;
            }

            $logger->error('Migration execution failed', [
                'file' => $fileName,
                'table' => $tableName,
                'error' => $exception->getMessage(),
            ]);

            return [
                'ok' => false,
                'created' => $created,
                'skipped' => $skipped,
                'failed' => 1,
                'error' => [
                    'file' => $fileName,
                    'table' => $tableName,
                    'message' => $exception->getMessage(),
                ],
            ];
        }
    }

    return [
        'ok' => true,
        'created' => $created,
        'skipped' => $skipped,
        'failed' => 0,
        'error' => null,
    ];
}

function extractTableName(string $sql): ?string
{
    $pattern = '/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i';

    if (preg_match($pattern, $sql, $matches) === 1) {
        return $matches[1];
    }

    return null;
}

function tableExists(PDO $pdo, string $tableName): bool
{
    $statement = $pdo->prepare(
        'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table LIMIT 1'
    );
    $statement->execute(['table' => $tableName]);

    return (bool) $statement->fetchColumn();
}

function isTableAlreadyExistsError(PDOException $exception): bool
{
    $sqlState = $exception->errorInfo[0] ?? null;
    $driverCode = (int) ($exception->errorInfo[1] ?? 0);
    $message = strtolower($exception->getMessage());

    return $sqlState === '42S01' || $driverCode === 1050 || str_contains($message, 'already exists');
    
}
