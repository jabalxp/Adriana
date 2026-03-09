<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Atestados - SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background: radial-gradient(circle at center, #1e293b 0%, #020617 100%);">
    <canvas id="canvas-plexus"></canvas>
    <div class="premium-container">
        <div class="login-card" style="max-width: 600px;">
            <div class="logo-senai">
                <span>SENAI</span>
            </div>
            <h2 style="margin-bottom: 2rem; font-weight: 700;">Selecione seu Perfil</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <a href="views/aluno/index.php" style="text-decoration: none; background: rgba(255,255,255,0.05); padding: 2rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s;" onmouseover="this.style.borderColor='#ff3b3b'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-user-graduate" style="font-size: 3rem; color: #ff3b3b; margin-bottom: 1rem; display: block;"></i>
                    <strong style="color: white; font-size: 1.2rem;">Sou Aluno</strong>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">Entregar atestado ou justificativa</p>
                </a>
                
                <a href="views/login.php" style="text-decoration: none; background: rgba(255,255,255,0.05); padding: 2rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s;" onmouseover="this.style.borderColor='#ff3b3b'; this.style.transform='translateY(-5px)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.transform='translateY(0)'">
                    <i class="fa-solid fa-user-tie" style="font-size: 3rem; color: #ff3b3b; margin-bottom: 1rem; display: block;"></i>
                    <strong style="color: white; font-size: 1.2rem;">Sou Docente</strong>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-top: 0.5rem;">Acessar painel de gestão</p>
                </a>
            </div>
        </div>
    </div>
    <script src="assets/js/login-plexus.js"></script>
</body>
</html>
