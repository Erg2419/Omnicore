<?php
session_start();
include 'db.php';

$error = ""; // Inicializar variable de error

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['email'] = $user['email'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login FIBGEN</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0F2027, #203A43, #2C5364);
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(20px);
    padding: 60px 50px;
    border-radius: 30px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    width: 400px;
    text-align: center;
    color: #fff;
    position: relative;
    overflow: hidden;
    animation: fadeIn 1s ease;
}

.logo {
    font-size: 40px;
    font-weight: 700;
    color: #FFD700;
    margin-bottom: 30px;
    letter-spacing: 2px;
    text-shadow: 0 0 15px #FFD700, 0 0 30px #FFC107;
}

.input-group {
    position: relative;
    margin: 20px 0;
}

.input-group input {
    width: 100%;
    padding: 14px 15px;
    padding-right: 45px;
    border-radius: 12px;
    border: none;
    outline: none;
    font-size: 16px;
    background: #ffffff;
    color: #333;
    box-sizing: border-box;
    border: 2px solid transparent;
    transition: border 0.3s ease;
}

.input-group input:focus {
    border: 2px solid #FFD700;
}

.input-group input::placeholder {
    color: #666;
}

.input-group i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #FFD700;
    z-index: 2;
}

button {
    width: 100%;
    padding: 16px;
    margin-top: 10px;
    background: #FFD700;
    color: #0F2027;
    font-weight: 600;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

button:hover {
    background: #FFC107;
    transform: translateY(-2px);
}

.error {
    color: #FF6347;
    margin-top: 10px;
    font-weight: bold;
}

.register-link {
    margin-top: 20px;
    display: inline-block;
    color: #00FFFF;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}

.register-link:hover {
    color: #FFD700;
    text-decoration: underline;
}

@keyframes fadeIn {
    from {opacity:0; transform: translateY(-30px);}
    to {opacity:1; transform: translateY(0);}
}

@media(max-width: 420px){
    .container {width: 90%; padding: 40px 20px;}
}
</style>
</head>
<body>
<div class="container">
    <div class="logo"><i class="fas fa-dumbbell"></i> FIBGEN</div>
    <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post" autocomplete="off">
        <div class="input-group">
            <input type="email" name="email" placeholder="Correo electrónico" required autocomplete="off">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="input-group">
            <input type="password" name="password" placeholder="Contraseña" required autocomplete="new-password">
            <i class="fas fa-lock"></i>
        </div>
        <button type="submit">Entrar</button>
    </form>
    <a href="register.php" class="register-link">¿No tienes cuenta? Registrarse</a>
</div>
</body>
</html>
<?php $conn->close(); ?>