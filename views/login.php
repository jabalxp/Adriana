<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gerenciamento SENAI</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <canvas id="canvas-plexus"></canvas>
    
    <div class="premium-container">
        <div class="login-card">
            <div class="logo-senai">
                <span>SENAI</span>
            </div>
            
            <form action="../api/auth.php" method="POST">
                <div class="form-group">
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="E-mail" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-shield-halved input-icon"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="*****" required>
                        <i class="fa-regular fa-eye input-eye" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    Entrar <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <script src="../assets/js/login-plexus.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
