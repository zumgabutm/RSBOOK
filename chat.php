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
    exit;
}

// Obter amigos para enviar mensagens
$stmt = $conn->prepare("SELECT u.id, u.nome FROM amigos a JOIN usuarios u ON a.amigo_id = u.id WHERE a.usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$amigos = $stmt->get_result();

// Se houver uma conversa selecionada, obter as mensagens trocadas
$mensagens = [];
if (isset($_GET['amigo_id'])) {
    $amigo_id = $_GET['amigo_id'];
    
    // Marcar notificações como lidas para o amigo selecionado
    $stmt = $conn->prepare("UPDATE notificacoes SET lida = TRUE WHERE usuario_id = ? AND remetente_id = ?");
    $stmt->bind_param("ii", $usuario_id, $amigo_id);
    $stmt->execute();
    
    // Obter mensagens trocadas com o amigo selecionado
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
    <title>Chat - Rede Social</title>
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
            background: linear-gradient(to right, #3b5998, #4e69a2, #5b7ab4);
            border-radius: 10px;
            max-width: 800px;
            width: 90%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .amigos {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .amigos h3 {
            margin-top: 0;
            color: #333;
        }
        
        .amigos ul {
            list-style-type: none;
            padding: 0;
        }
        
        .amigos ul li {
            margin-bottom: 5px;
        }
        
        .amigos ul li a {
            text-decoration: none;
            color: #333;
        }
        
        .mensagens {
            max-height: 300px;
            overflow-y: scroll;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            background-color: white;
            border-radius: 5px;
        }
        
        .mensagem {
            padding: 5px;
            border-bottom: 1px solid #eee;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        
        .mensagem strong {
            font-weight: bold;
        }
        
        .mensagem p, .mensagem small {
            margin: 5px 0;
        }
        
        .mensagem.enviada {
            background-color: #dcf8c6;
            text-align: right;
        }
        
        .mensagem.recebida {
            background-color: #ffffff;
        }
        
        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
        }
        
        textarea {
            width: calc(100% - 40px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
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
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var mensagensDiv = document.querySelector('.mensagens');
            mensagensDiv.scrollTop = mensagensDiv.scrollHeight;

            document.querySelector('form').addEventListener('submit', function(event) {
                event.preventDefault(); // Impede o envio do formulário pelo método padrão

                var formData = new FormData(this);
                var xhr = new XMLHttpRequest();
                xhr.open("POST", this.action, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var novaMensagem = document.createElement('div');
                        novaMensagem.className = 'mensagem enviada';
                        novaMensagem.innerHTML = '<strong>Você:</strong><p>' + document.getElementById('mensagem').value + '</p><small>Agora</small>';
                        mensagensDiv.appendChild(novaMensagem);
                        document.getElementById('mensagem').value = '';
                        mensagensDiv.scrollTop = mensagensDiv.scrollHeight;
                    }
                };
                xhr.send(formData);
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Chat</h1>

        <div style="text-align: center; margin-top: 20px;">
            <a href="home.php" style="display: inline-block; padding: 15px 30px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">PÁGINA INICIAL</a>
            <a href="javascript:location.reload();" style="display: inline-block; padding: 15px 30px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">RECARREGAR</a>
        </div>
        <br>

        <div class="amigos">
            <h3>Conversas</h3>
            <ul>
                <?php while ($amigo = $amigos->fetch_assoc()): ?>
                    <li><a href="chat.php?amigo_id=<?php echo $amigo['id']; ?>"><?php echo $amigo['nome']; ?></a></li>
                <?php endwhile; ?>
            </ul>
        </div>
        
        <?php if (isset($amigo_id)): ?>
        <div class="mensagens">
            <h3>Conversa com <?php echo $amigo['nome']; ?></h3>
            <?php foreach ($mensagens as $mensagem): ?>
                <div class="mensagem <?php echo ($mensagem['remetente_id'] == $usuario_id) ? 'enviada' : 'recebida'; ?>">
                    <strong><?php echo ($mensagem['remetente_id'] == $usuario_id) ? 'Você' : $amigo['nome']; ?>:</strong>
                    <p><?php echo $mensagem['mensagem']; ?></p>
                    <small><?php echo $mensagem['data_envio']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
        
        <form method="POST" action="chat.php">
            <input type="hidden" name="destinatario_id" value="<?php echo $amigo_id; ?>">
            <label for="mensagem">Mensagem:</label><br>
            <textarea id="mensagem" name="mensagem" rows="3" required></textarea><br>
            <input type="submit" value="Enviar">
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
