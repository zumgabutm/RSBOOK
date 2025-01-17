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
    $email_amigo = $_POST['email_amigo'];
    
    // Verificar se o usuário existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email_amigo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $amigo_id = $result->fetch_assoc()['id'];
        
        // Verificar se já são amigos
        $stmt = $conn->prepare("SELECT * FROM amigos WHERE usuario_id = ? AND amigo_id = ?");
        $stmt->bind_param("ii", $usuario_id, $amigo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Adicionar amizade
            $stmt = $conn->prepare("INSERT INTO amigos (usuario_id, amigo_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $usuario_id, $amigo_id);
            $stmt->execute();
            
            $stmt = $conn->prepare("INSERT INTO amigos (usuario_id, amigo_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $amigo_id, $usuario_id);
            $stmt->execute();
            
            echo "Amigo adicionado com sucesso!";
        } else {
            echo "Vocês já são amigos.";
        }
    } else {
        echo "Usuário não encontrado.";
    }
}

// Obter lista de amigos
$stmt = $conn->prepare("SELECT u.id, u.nome FROM amigos a JOIN usuarios u ON a.amigo_id = u.id WHERE a.usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$amigos = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Amigos - Rede Social</title>
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
        
        form {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        input[type="email"], input[type="submit"] {
            padding: 10px;
            width: calc(100% - 20px);
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        input[type="submit"] {
            background-color: black;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #333;
        }
        
        h1, h2 {
            text-align: center;
            color: white;
        }
        
        ul {
            list-style-type: none;
            padding: 0;
        }
        
        ul li {
            background-color: white;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
    <a href="home.php" style="display: inline-block; padding: 15px 30px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">PÁGINA INICIAL</a>
        <h1>Amigos</h1>
        <form method="POST" action="amigos.php">
            <label for="email_amigo">Adicionar Amigo (Email):</label>
            <input type="email" id="email_amigo" name="email_amigo" required><br>
            <input type="submit" value="Adicionar">
        </form>
        
        <h2>Lista de Amigos</h2>
        <ul>
            <?php while ($amigo = $amigos->fetch_assoc()): ?>
                <li><?php echo $amigo['nome']; ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
