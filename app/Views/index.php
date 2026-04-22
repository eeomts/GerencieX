<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciex - Condomínio Inteligente</title>
    <?php renderAssets(); ?>
</head>
<body>

    <div class="phone-container home">
        <div class="logo-container">
            <h1 class="logo-text">Gerenciex</h1>
            <p class="subtitle">CONDOMÍNIO INTELIGENTE</p>
        </div>

        <div class="content-bottom">
            <p class="instruction">Escolha como deseja continuar</p>

            <a href="<?php echo url('/login'); ?>" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                Sou morador
            </a>

            <a href="<?php echo url('/login'); ?>" class="btn btn-secondary">
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                Sou administrador
            </a>

            <p class="footer-text">Não tem conta? <a href="#" class="link-signup">Cadastre-se</a></p>
        </div>

        <div class="home-indicator"></div>
    </div>

</body>
</html>
