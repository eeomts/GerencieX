<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Morador - Gerenciex</title>
    <?php renderAssets(); ?>
</head>
<body class="page-login">

    <div class="phone-container login">
       

        <div class="header">
            <a href="<?php echo url('/'); ?>" class="back-button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <p class="header-tag">Acesso Morador</p>
            <h2 class="header-title">Bem-vindo de volta</h2>
        </div>

        <div class="login-card">
            <form method="POST" action="/login">
                <div class="form-group">
                    <label class="label">E-mail</label>
                    <div class="input-container">
                        <svg class="input-icon icon-svg" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <input type="email" name="email" class="input-field" placeholder="voce@email.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Senha</label>
                    <div class="input-container">
                        <svg class="input-icon icon-svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                        <svg class="password-toggle icon-svg" width="20" height="20" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="label">Token do Condomínio</label>
                    <div class="input-container">
                        <svg class="input-icon icon-svg" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        <input type="text" name="token" class="input-field" placeholder="Ex: TORRES-AZUL-2847" required>
                    </div>
                </div>

                <a href="#" class="forgot-password">Esqueci a senha</a>

                <button type="submit" class="btn-submit">Entrar</button>

                <p class="footer-text">Primeira vez aqui? <a href="#" class="link-create">Criar conta</a></p>
            </form>
        </div>

        <div class="home-indicator"></div>
    </div>

</body>
</html>
