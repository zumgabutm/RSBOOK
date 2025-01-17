<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$db = 'amigo';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $destinatario_id = $_POST['destinatario_id'];
    $mensagem = $_POST['mensagem'];
    
    // Inserir mensagem na tabela de mensagens
    $stmt = $conn->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $usuario_id, $destinatario_id, $mensagem);
    $stmt->execute();
    $mensagem_id = $stmt->insert_id;
    $stmt->close();

    // Criar notificação para o destinatário
    $stmt = $conn->prepare("INSERT INTO notificacoes (usuario_id, remetente_id, mensagem_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $destinatario_id, $usuario_id, $mensagem_id);
    $stmt->execute();
    $stmt->close();
}

// Obter amigos para enviar mensagens
$stmt = $conn->prepare("SELECT u.id, u.nome FROM amigos a JOIN usuarios u ON a.amigo_id = u.id WHERE a.usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$amigos = $stmt->get_result();

// Obter mensagens trocadas com um amigo específico
$mensagens = [];
if (isset($_GET['amigo_id'])) {
    $amigo_id = $_GET['amigo_id'];
    
    // Marcar notificações como lidas
    $stmt = $conn->prepare("UPDATE notificacoes SET lida = TRUE WHERE usuario_id = ? AND remetente_id = ?");
    $stmt->bind_param("ii", $usuario_id, $amigo_id);
    $stmt->execute();
    
    // Obter mensagens trocadas
    $stmt = $conn->prepare("SELECT m.*, u.nome AS remetente_nome FROM mensagens m JOIN usuarios u ON m.remetente_id = u.id WHERE (m.remetente_id = ? AND m.destinatario_id = ?) OR (m.remetente_id = ? AND m.destinatario_id = ?) ORDER BY m.data_envio");
    $stmt->bind_param("iiii", $usuario_id, $amigo_id, $amigo_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($mensagem = $result->fetch_assoc()) {
        $mensagens[] = $mensagem;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mensagens - Rede Social</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        .container {
            padding: 20px;
            background: linear-gradient(to right, blue, green, pink);
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .container h1 {
            color: white;
            text-align: center;
        }
        
        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
        }
        
        input[type="submit"] {
            background-color: black;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #333;
        }
        
        select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .mensagens {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
        }
        
        .mensagem {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .mensagem p, .mensagem small {
            margin: 5px 0;
        }
        
        .mensagem.recebida {
            background-color: lightblue;
        }
        
        .mensagem.enviada {
            background-color: lightgreen;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mensagens</h1>
        
        <div style="text-align: center; margin-top: 20px;">
    <a href="home.php" style="display: inline-block; padding: 15px 30px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">PÁGINA INICIAL</a>
</div>
<br>
        
        <form method="POST" action="mensagens.php">
            <label for="destinatario_id">Enviar para:</label>
            <select id="destinatario_id" name="destinatario_id" required>
                <?php while ($amigo = $amigos->fetch_assoc()): ?>
                    <option value="<?php echo $amigo['id']; ?>"><?php echo $amigo['nome']; ?></option>
                <?php endwhile; ?>
            </select><br>
            <label for="mensagem">Mensagem:</label><br>
            <textarea id="mensagem" name="mensagem" rows="4" required></textarea><br>
            <input type="submit" value="Enviar">
        </form>
        
        <h2>Conversa</h2>
        <div class="mensagens">
            <?php foreach ($mensagens as $mensagem): ?>
                <div class="mensagem <?php echo ($mensagem['remetente_id'] == $usuario_id) ? 'enviada' : 'recebida'; ?>">
                    <strong><?php echo ($mensagem['remetente_id'] == $usuario_id) ? 'Você' : $mensagem['remetente_nome']; ?>:</strong>
                    <p><?php echo $mensagem['mensagem']; ?></p>
                    <small><?php echo $mensagem['data_envio']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
