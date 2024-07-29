<?php
header("Content-Type: application/json");

include '../../config/config.php';
include '../../vendor/autoload.php';
date_default_timezone_set('America/Santiago');

// Obtener los datos enviados en el cuerpo de la solicitud
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($_POST['ticketId']) || !isset($_POST['estado'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del ticket o el estado"]);
    exit();
}

$ticket_id = $_POST['ticketId'];
$estado = $_POST['estado'];
$solucion = $_POST['solucion'];
$fecha = date('Y-m-d H:i:s');

// Manejar el archivo adjunto
$dest_path = '';
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['archivo']['tmp_name'];
    $fileName = $ticket_id . '_' . $_FILES['archivo']['name'];
    $uploadFileDir = '../../uploads/';
    $dest_path = $uploadFileDir . $fileName;

    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
        echo json_encode(["success" => false, "message" => "Error al subir el archivo"]);
        exit();
    }
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Obtener el email del ticket
    $sql_get_email = "SELECT email FROM tickets WHERE id='$ticket_id'";
    $result = $conn->query($sql_get_email);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
    } else {
        throw new Exception("No se encontró el email del ticket: " . $conn->error);
    }

    // Actualizar el estado del ticket
    $sql_update_ticket = "UPDATE tickets SET estado='$estado', solucion='$solucion', archivo='$dest_path' WHERE id='$ticket_id'";

    if ($conn->query($sql_update_ticket) === FALSE) {
        throw new Exception("Error al actualizar el estado del ticket: " . $conn->error);
    }

    // Insertar en el historial del ticket
    $historial_descripcion = "Ticket cerrado con solución: $solucion";
    $sql_insert_historial = "INSERT INTO ticket_history (ticket_id, fecha, Hestado, Hdescripcion) VALUES ('$ticket_id', '$fecha', '$estado', '$historial_descripcion')";

    if ($conn->query($sql_insert_historial) === FALSE) {
        throw new Exception("Error al insertar en el historial del ticket: " . $conn->error);
    }

    // Confirmar transacción
    $conn->commit();

    // Enviar correo electrónico usando PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'mailer.wit@gmail.com'; // Cambia esto por tu correo electrónico
    $mail->Password = 'qzyuwykitiekjsku'; // Cambia esto por tu contraseña
    $mail->SMTPSecure = 'tls'; // O 'ssl' si es necesario
    $mail->Port = 587; // Puerto SMTP

    $mail->setFrom('tu_correo@example.com', 'Sistema de Tickets');  // Cambia esto a tu dirección de correo electrónico y nombre
    $mail->addAddress($email); // Enviar al solicitante
   // $mail->addAddress('dgonzalez@wit.la'); // Enviar también a dgonzalez@wit.la

    $mail->isHTML(true);
    $mail->Subject = "Ticket Cerrado: #$ticket_id";
    $mail->Body    = "
        <h1>Ticket Cerrado</h1>
        <p><strong>ID del Ticket:</strong> $ticket_id</p>
        <p><strong>Estado:</strong> $estado</p>
        <p><strong>Solucion:</strong> $solucion</p>
        <p><strong>Fecha de Cierre:</strong> $fecha</p>
    ";

    if ($dest_path) {
        $mail->addAttachment($dest_path);
    }

    if (!$mail->send()) {
        throw new Exception('El correo no pudo ser enviado. Error de correo: ' . $mail->ErrorInfo);
    }

    echo json_encode(["success" => true, "message" => "Ticket cerrado, registrado en el historial y correo enviado"]);

} catch (Exception $e) {
    // Revertir transacción
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>
