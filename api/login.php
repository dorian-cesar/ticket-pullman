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

$sql_query = "SELECT users.email AS userEmail, 
users.password, 
roles.nombre AS nombreRol, 
empresas.nombre AS nombreEmpresa, 
empresas.id AS idEmpresa, 
areas.nombre AS nombreArea 
FROM users 
JOIN roles ON roles.id = users.rol_id 
JOIN empresas ON empresas.id = users.empresa_id 
JOIN areas ON areas.id = users.area_id 
WHERE users.email='$email' ";

$result = $conn->query($sql_query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();


    // Verificar la contraseña hasheada
    if (password_verify($password, $user['password'])) {
        // Autenticación exitosa
        echo json_encode(["success" => true, "message" => "Autenticación exitosa", "rol" => $user['nombreRol'], "email" => $user['userEmail'], "empresaName" => $user['nombreEmpresa'], "empresaID" => $user['idEmpresa'], "area" => $user['nombreArea']]);
    } else {
        // Contraseña incorrecta
        echo json_encode(["success" => false, "message" => "Correo electrónico o contraseña incorrectos"]);
    }
} else {
    // Usuario no encontrado
    echo json_encode(["success" => false, "message" => "Correo electrónico o contraseña incorrectos"]);
}

$conn->close();