<?php

declare(strict_types=1);

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must run in CLI mode." . PHP_EOL);
    exit(1);
}

$projectRoot = dirname(__DIR__, 3);
$autoloadPath = $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!is_file($autoloadPath)) {
    fwrite(STDERR, "Composer autoload not found. Run 'composer install'." . PHP_EOL);
    exit(1);
}

require_once $autoloadPath;

$sqlDirectory = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'table';
$configPath = $projectRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
$logDirectory = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'logs';
$logFilePath = $logDirectory . DIRECTORY_SEPARATOR . 'migrate.log';

$logger = null;

try {
    $logger = createLogger($logDirectory);
    $logger->info('Migration started', ['sql_directory' => $sqlDirectory]);

    $databaseConfig = loadDatabaseConfig($configPath);

    if ($databaseConfig === null) {
        $message = 'Database configuration not found in config/database.php.';
        $logger->error($message, ['config_path' => $configPath]);
        fwrite(STDERR, 'ERRO: ' . $message . PHP_EOL);
        fwrite(STDERR, 'Log: ' . $logFilePath . PHP_EOL);
        exit(1);
    }

    $pdo = createPdo($databaseConfig);
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

    $logFilePath = $logDirectory . DIRECTORY_SEPARATOR . 'migrate.log';

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

function loadDatabaseConfig(string $configPath): ?array
{
    $config = [];

    if (is_file($configPath) && filesize($configPath) > 0) {
        $loaded = include $configPath;

        if (is_array($loaded)) {
            $config = normalizeDatabaseConfig($loaded);
        }
    }

    $constantMap = [
        'driver' => getDefinedConstant('DB_DRIVER'),
        'host' => getDefinedConstant('DB_HOST'),
        'port' => getDefinedConstant('DB_PORT'),
        'database' => getDefinedConstant('DB_DATABASE'),
        'username' => getDefinedConstant('DB_USER'),
        'password' => getDefinedConstant('DB_PASSWORD'),
        'charset' => getDefinedConstant('DB_CHARSET'),
    ];

    foreach ($constantMap as $key => $value) {
        if ($value !== null && $value !== '') {
            $config[$key] = $value;
        }
    }

    $config['driver'] = $config['driver'] ?? 'mysql';
    $config['charset'] = $config['charset'] ?? 'utf8mb4';
    $config['port'] = $config['port'] ?? 3306;

    if (isset($config['port'])) {
        $config['port'] = (int) $config['port'];
    }

    $requiredKeys = ['host', 'database', 'username'];

    foreach ($requiredKeys as $requiredKey) {
        if (!isset($config[$requiredKey]) || $config[$requiredKey] === '') {
            return null;
        }
    }

    return $config;
}

function getDefinedConstant(string $constantName)
{
    if (!defined($constantName)) {
        return null;
    }

    return constant($constantName);
}

function normalizeDatabaseConfig(array $config): array
{
    if (isset($config['connections']) && is_array($config['connections'])) {
        $defaultConnection = $config['default'] ?? null;

        if (is_string($defaultConnection) && isset($config['connections'][$defaultConnection]) && is_array($config['connections'][$defaultConnection])) {
            $config = $config['connections'][$defaultConnection];
        } else {
            $firstConnection = reset($config['connections']);

            if (is_array($firstConnection)) {
                $config = $firstConnection;
            }
        }
    }

    return [
        'driver' => (string) ($config['driver'] ?? $config['db_driver'] ?? 'mysql'),
        'host' => (string) ($config['host'] ?? $config['db_host'] ?? ''),
        'port' => (int) ($config['port'] ?? $config['db_port'] ?? 3306),
        'database' => (string) ($config['database'] ?? $config['dbname'] ?? $config['db_database'] ?? ''),
        'username' => (string) ($config['username'] ?? $config['user'] ?? $config['db_user'] ?? ''),
        'password' => (string) ($config['password'] ?? $config['pass'] ?? $config['db_password'] ?? ''),
        'charset' => (string) ($config['charset'] ?? $config['db_charset'] ?? 'utf8mb4'),
    ];
}

function createPdo(array $config): PDO
{
    if (strtolower((string) $config['driver']) !== 'mysql') {
        throw new RuntimeException('Only mysql driver is currently supported by this migration script.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'],
        (int) $config['port'],
        $config['database'],
        $config['charset']
    );

    return new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
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
