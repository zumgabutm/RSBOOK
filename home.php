<?php
// Inicia a sessão no início do arquivo PHP
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Rede Social</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            overflow: hidden; /* Oculta overflow para o efeito do foguete */
            background: url('https://e0.pxfuel.com/wallpapers/334/805/desktop-wallpaper-xbox-logo-gaming-poster-print-metal-posters-displate-em-2020-papel-de-parede-games-m-para-celular-papeis-de-parede-de-jogos-xbox-android.jpg') repeat;
        }

        .star-lite {
            position: absolute;
            top: 50%;
            left: -200px; /* Inicia fora da tela à esquerda */
            width: 200px;
            height: 200px;
            background-image: url('https://images.emojiterra.com/google/noto-emoji/unicode-15/animated/1f680.gif');
            background-size: cover;
            animation: starLite 10s linear infinite;
            z-index: 1; /* Posiciona o foguete acima do conteúdo */
        }

        .star-lite-2 {
            position: absolute;
            top: 70%;
            left: -200px; /* Inicia fora da tela à esquerda */
            width: 200px;
            height: 200px;
            background-image: url('https://images.emojiterra.com/google/noto-emoji/unicode-15/animated/1f680.gif');
            background-size: cover;
            animation: starLite-2 20s linear infinite;
            z-index: 1; /* Posiciona o foguete acima do conteúdo */
        }

        @keyframes starLite {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes starLite-2 {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .container {
            position: relative;
            z-index: 2; /* Posiciona o conteúdo acima do foguete */
            padding: 20px;
            background: rgba(59, 89, 152, 0.5); /* Fundo com opacidade de 50% */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
            width: 90%;
            text-align: center;
            color: white;
            margin: 100px auto 0; /* Espaçamento ajustado para o topo */
        }

        .container h1 {
            font-size: 2em;
            position: relative; /* Para que o z-index funcione */
            z-index: 2; /* Posiciona o texto acima do foguete */
            margin-top: 50px; /* Ajuste para o espaço do foguete */
        }

        .menu {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .menu a {
            text-decoration: none;
            color: white;
            padding: 10px 20px;
            background: #4267B2; /* Cor de fundo do botão estilo Facebook */
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .menu a:hover {
            background-color: #3a5b91; /* Cor de fundo do botão estilo Facebook ao passar o mouse */
        }

        /* Estilização do papel de parede */
        .papel-parede {
            position: absolute;
            width: 100%;
            height: 50%;
            background: url('papel-de-parede.jpg') repeat;
            top: 0;
            left: 0;
            z-index: 0; /* Coloca o papel de parede atrás de todo o conteúdo */
        }

        .papel-parede.bottom {
            top: auto;
            bottom: 0;
        }
    </style>
</head>
<body>
    <!-- Elemento para a animação do foguete STAR LITE -->
    <div class="star-lite"></div>
    <div class="star-lite-2"></div>
    
    <!-- Papel de parede superior -->
    <div class="papel-parede"></div>
    
    <div class="container">
        <?php
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: login.php');
            exit;
        }

        $host = 'localhost';
        $db = 'rede';
        $user = 'root';
        $pass = '';

        $conn = new mysqli($host, $user, $pass, $db);

        $usuario_id = $_SESSION['usuario_id'];

        // Obter informações do usuário
        $stmt = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        // Obter número de novas mensagens
        $stmt = $conn->prepare("SELECT COUNT(*) AS novas_mensagens FROM notificacoes WHERE usuario_id = ? AND lida = FALSE");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notificacoes = $result->fetch_assoc();
        ?>

        <h1>Bem-vindo, <?php echo $usuario['nome']; ?>!</h1>
        <div class="menu">
            <a href="amigos.php">Amigos</a>
            <a href="chat.php">Mensagens (<?php echo $notificacoes['novas_mensagens']; ?>)</a>
            <a href="chat.php">Chat</a>
            <a href="logout.php">Logout</a>
        </div>
        <!-- Conteúdo da página inicial -->
    </div>
    
    <!-- Papel de parede inferior -->
    <div class="papel-parede bottom"></div>
</body>
</html>
