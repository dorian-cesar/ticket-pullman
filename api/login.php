<?php
header("Content-Type: application/json");

include '../config/config.php';

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['email']) || !isset($input['password'])) {
    echo json_encode(["success" => false, "message" => "Falta el correo o la contraseña"]);
    exit();
}

$email = $input['email'];
$password = $input['password'];

// Verificar credenciales (debes ajustar esta parte según tu sistema de autenticación)

$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);





if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

   
    // Verificar la contraseña hasheada
    if (password_verify($password, $user['password'])) {
        // Autenticación exitosa
        echo json_encode(["success" => true, "message" => "Autenticación exitosa", "area"=>$user['area']]);
    } else {
        // Contraseña incorrecta
        echo json_encode(["success" => false, "message" => "Correo electrónico o contraseña incorrectos"]);
    }
} else {
    // Usuario no encontrado
    echo json_encode(["success" => false, "message" => "Correo electrónico o contraseña incorrectos"]);
}

$conn->close();
?>