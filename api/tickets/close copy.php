<?php
header("Content-Type: application/json");

include '../../config/config.php';
include '../../vendor/autoload.php';

// Obtener los datos enviados en el cuerpo de la solicitud
$input = json_decode(file_get_contents("php://input"), true);



if (!isset($_POST['ticketId']) & !isset($_POST['estado'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del ticket, estado o descripción"]);
    exit();
}
    
    $ticket_id = $_POST['ticketId'];
    $estado = $_POST['estado'];
    $descripcion =  $_POST['solucion'] ;
    $fecha = date('Y-m-d H:i:s');

  





// Manejar el archivo adjunto
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['archivo']['tmp_name'];
    $fileName = $ticket_id . '_' . $_FILES['archivo']['name'];
    $uploadFileDir = '../../uploads/';
    $dest_path = $uploadFileDir . $fileName;

    if(!move_uploaded_file($fileTmpPath, $dest_path)) {
        echo json_encode(["success" => false, "message" => "Error al subir el archivo"]);
        exit();
    }
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Actualizar el estado del ticket
    $sql_update_ticket = "UPDATE tickets SET estado='$estado', descripcion='$descripcion', archivo='$dest_path' WHERE id='$ticket_id'";

    if ($conn->query($sql_update_ticket) === FALSE) {
        throw new Exception("Error al actualizar el estado del ticket: " . $conn->error);
    }

    // Insertar en el historial del ticket
    $historial_descripcion = "Ticket cerrado con solución: $descripcion";
    $sql_insert_historial = "INSERT INTO ticket_history (ticket_id, fecha, estado, descripcion) VALUES ('$ticket_id', '$fecha', '$estado', '$descripcion')";

    if ($conn->query($sql_insert_historial) === FALSE) {
        throw new Exception("Error al insertar en el historial del ticket: " . $conn->error);
    }

    // Confirmar transacción
    $conn->commit();
    
    echo json_encode(["success" => true, "message" => "Ticket cerrado y registrado en el historial"]);
} catch (Exception $e) {
    // Revertir transacción
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();

sendEmail($ticket_id, $estado, $descripcion);

function sendEmail($ticket_id, $estado, $descripcion) {
    // Crear una instancia de PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'mailer.wit@gmail.com'; // Cambia esto por tu correo electrónico
    $mail->Password = 'qzyuwykitiekjsku'; // Cambia esto por tu contraseña
    $mail->SMTPSecure = 'tls'; // O 'ssl' si es necesario
    $mail->Port = 587; // Puerto SMTP

    // Configuración del correo
    $mail->setFrom('noreply@example.com', 'Sistema de Tickets'); // Cambia esto por tu dirección de correo
    $mail->addAddress('dgonzalez@wit.la', 'Dorian Gonzalez');
    $mail->isHTML(true);
    $mail->Subject = 'Ticket Cerrado: #' . $ticket_id;
    $mail->Body = '
        <h1>Ticket Cerrado</h1>
        <p><strong>ID:</strong> ' . $ticket_id . '</p>
        <p><strong>Estado:</strong> ' . $estado . '</p>
        <p><strong>Solución:</strong> ' . $descripcion . '</p>
    ';

    if(!$mail->send()) {
        error_log('Mail error: ' . $mail->ErrorInfo);
    }
}
?>
