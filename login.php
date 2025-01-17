<?php
$host = 'localhost';
$db = 'amigo';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND senha = ?");
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['usuario_id'] = $result->fetch_assoc()['id'];
        header('Location: home.php');
    } else {
        echo "Credenciais inválidas.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Rede Social</title>
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
        
        .card {
            background: linear-gradient(to right, blue, green, pink);
            padding: 20px;
            border-radius: 10px;
            max-width: 300px;
            width: 90%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            text-align: center;
            color: white;
        }
        
        .card label {
            color: white;
        }
        
        .card input[type="email"],
        .card input[type="password"],
        .card input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: none;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        .card input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        
        .card input[type="submit"]:hover {
            background-color: #45a049;
        }
        
        .card p {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: white;
        }
        
        .card p a {
            color: white;
            text-decoration: none;
        }
        
        .card p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Login</h2>
        
        <form method="POST" action="login.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required><br>
            <input type="submit" value="Conectar">
        </form>
        
        <p>Não tem uma conta? <a href="register.php">Inscreva-se</a></p>
    </div>
</body>
</html>
