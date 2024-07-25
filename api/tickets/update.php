<?php
header("Content-Type: application/json");

include '../../config/config.php';
require '../../vendor/autoload.php'; // Incluir PHPMailer

// Obtener los datos enviados en el cuerpo de la solicitud
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id']) || !isset($input['estado'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del ticket o el estado"]);
    exit();
}

$ticket_id = $input['id'];
$estado = $input['estado'];
$descripcion = isset($input['descripcion']) ? $input['descripcion'] : '';
$fecha = date('Y-m-d H:i:s');

// Iniciar transacción
$conn->begin_transaction();

try {
    // Actualizar el estado del ticket
    $sql_update_ticket = "UPDATE tickets SET estado='$estado'";
    if ($estado == 'cerrado') {
        $sql_update_ticket .= ", descripcion='$descripcion'";
    }
    $sql_update_ticket .= " WHERE id='$ticket_id'";

    if ($conn->query($sql_update_ticket) === FALSE) {
        throw new Exception("Error al actualizar el estado del ticket: " . $conn->error);
    }

    // Insertar en el historial del ticket
    $historial_descripcion = $estado == 'cerrado' ? "Ticket cerrado con solución: $descripcion" : "Estado actualizado a $estado";
    $sql_insert_historial = "INSERT INTO ticket_history (ticket_id, fecha, estado, descripcion) VALUES ('$ticket_id', '$fecha', '$estado', '$historial_descripcion')";

    if ($conn->query($sql_insert_historial) === FALSE) {
        throw new Exception("Error al insertar en el historial del ticket: " . $conn->error);
    }

    // Si el estado es 'cerrado', enviar correo
    if ($estado == 'cerrado') {
        sendEmail($ticket_id, $estado, $descripcion);
    }

    // Confirmar transacción
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Estado del ticket actualizado y registrado en el historial"]);
} catch (Exception $e) {
    // Revertir transacción
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();

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
    $mail->addAddress('epaz@wit.la', 'Dorian Gonzalez');
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
