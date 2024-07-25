<?php
header("Content-Type: application/json");

// Obtener los datos enviados en el cuerpo de la solicitud
$input = json_decode(file_get_contents("php://input"));

// Conectar a la base de datos
$servername = "ls-8ce02ad0b7ea586d393e375c25caa3488acb80a5.cylsiewx0zgx.us-east-1.rds.amazonaws.com";
$username = "dbmasteruser";
$password = ':&T``E~r:r!$1c6d:m143lzzvGJ$NuP;';
$dbname = "control_ticket";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$email = $conn->real_escape_string($input->email);
$password = $input->password;
$area = $conn->real_escape_string($input->area);

$response = ["success" => false, "message" => "Invalid credentials"];

// Consulta para verificar las credenciales del usuario

$sql = "SELECT * FROM users WHERE email = '$email' AND area = '$area'";
$result = $conn->query($sql);

//echo $user['password'];



if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verificar la contraseña
    if (password_verify($password, $user['password'])) {
        $response["success"] = true;
        $response["message"] = "Login successful";
     
       
    }
}

echo json_encode($response);

$conn->close();
?>
