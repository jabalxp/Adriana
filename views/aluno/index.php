<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início Aluno - SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="background: radial-gradient(circle at center, #1e293b 0%, #020617 100%);">
    <canvas id="canvas-plexus"></canvas>
    <div class="premium-container">
        <div class="login-card" style="max-width: 500px; text-align: left;">
            <div style="text-align: center;">
                <div class="logo-senai">
                    <span>SENAI</span>
                </div>
                <h2 style="margin-bottom: 1rem; font-weight: 700;">Identificação</h2>
                <p style="color: #94a3b8; margin-bottom: 2rem;">Por favor, informe seu <strong>e-mail pessoal</strong> para continuar.</p>
            </div>
            
            <form action="cadastro_atestado.php" method="GET">
                <div class="form-group" style="margin-bottom: 2rem;">
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="seu-email@exemplo.com" required>
                    </div>
                </div>
                
                <div style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border-left: 4px solid #ff3b3b;">
                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <input type="checkbox" id="terms" required style="margin-top: 0.3rem; transform: scale(1.2);">
                        <label for="terms" style="font-size: 0.85rem; line-height: 1.4; color: #cbd5e1;">
                            Estou Ciente e Concordo com <strong>TERMOS DE USO E POLÍTICA DE PRIVACIDADE</strong> do SENAI-SP disponível em: 
                            <a href="https://www.sp.senai.br/termos-de-uso-e-politica-de-privacidade" target="_blank" style="color: #ff3b3b; text-decoration: none;">Link da Política</a>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    Continuar <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
            
            <a href="../../index.php" style="display: block; text-align: center; margin-top: 1.5rem; color: #94a3b8; text-decoration: none; font-size: 0.9rem;">
                <i class="fa-solid fa-chevron-left"></i> Voltar
            </a>
        </div>
    </div>
    <script src="../../assets/js/login-plexus.js"></script>
</body>
</html>
