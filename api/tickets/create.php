<?php
header("Content-Type: application/json");
include '../../config/config.php';
date_default_timezone_set('America/Santiago');

// Obtener los datos enviados en el cuerpo de la solicitud
$input = json_decode(file_get_contents("php://input"), true);



$empresa = $conn->real_escape_string($_POST['empresa']);
$area_ejecutora = $conn->real_escape_string($_POST['areaEjecutora']);
$tipo_atencion = $conn->real_escape_string($_POST['tipoAtencion']);
$producto = $conn->real_escape_string($_POST['producto']);
$descripcion = $conn->real_escape_string($_POST['descripcion']);
$estado = $conn->real_escape_string($_POST['estado']);
$email = $conn->real_escape_string($_POST['email']);
$fecha_creacion = date('Y-m-d H:i:s');

$sql = "INSERT INTO tickets (empresa, area_ejecutora, tipo_atencion, producto, descripcion, estado, fecha_creacion, email) VALUES ('$empresa', '$area_ejecutora', '$tipo_atencion', '$producto', '$descripcion', '$estado', '$fecha_creacion', '$email')";

// echo $sql;
$response = ["success" => false, "message" => ""];

if ($conn->query($sql) === TRUE) {

    $ticket_id = $conn->insert_id;

    // Si hay un archivo, procesarlo
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        $file = $_FILES["file"];
        $fileTmpPath = $file["tmp_name"];
        $fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $newFileName = "$ticket_id.$fileExtension"; // Usar el ID como nombre del archivo
        $uploadPath = "../../uploads/$newFileName";

        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Actualizar la BD con el nombre del archivo
            $updateSql = "UPDATE tickets SET archivo='$newFileName' WHERE id=$ticket_id";
            
            if (!$conn->query($updateSql)) {
                $response["message"] = "Datos guardados, pero error al actualizar la BD con el archivo.";
            }
        }
    }

    $response["success"] = true;
    $response["message"] = "Ticket generado exitosamente";

    // Enviar correo electrónico usando PHPMailer
    require '../../vendor/autoload.php'; // Asegúrate de que la ruta es correcta
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'mailer.wit@gmail.com'; // Cambia esto por tu correo electrónico
        $mail->Password = 'qzyuwykitiekjsku'; // Cambia esto por tu contraseña
        $mail->SMTPSecure = 'tls'; // O 'ssl' si es necesario
        $mail->Port = 587; // Puerto SMTP

        // Destinatarios
        $mail->setFrom('ticket@wit.la', 'Sistema de Tickets');
        $mail->addAddress($email);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = "Nuevo Ticket Creado - ID: $ticket_id";
        $mail->Body    = "
            <h1>Nuevo Ticket Creado</h1>
            <p><strong>ID del Ticket:</strong> $ticket_id</p>
            <p><strong>Empresa:</strong> $empresa</p>
            <p><strong>Área Ejecutora:</strong> $area_ejecutora</p>
            <p><strong>Tipo de Atención:</strong> $tipo_atencion</p>
            <p><strong>Producto:</strong> $producto</p>
            <p><strong>Descripción:</strong> $descripcion</p>
            <p><strong>Estado:</strong> $estado</p>
            <p><strong>Fecha de Creación:</strong> $fecha_creacion</p>
        ";

        $mail->send();
        $response["message"] .= " - Correo enviado exitosamente";
    } catch (Exception $e) {
        $response["message"] .= " - Error al enviar el correo: {$mail->ErrorInfo}";
    }
} else {
    $response["message"] = "Error al generar el ticket: " . $conn->error;
}

echo json_encode($response);

$conn->close();