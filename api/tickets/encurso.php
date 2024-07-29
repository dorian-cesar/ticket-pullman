<?php
header("Content-Type: application/json");

include '../../config/config.php';
date_default_timezone_set('America/Santiago');

// Obtener los datos enviados en el cuerpo de la solicitud
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id']) || !isset($input['estado'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del ticket o el estado"]);
    exit();
}

$ticket_id = $input['id'];
$estado = $input['estado'];
$fecha = date('Y-m-d H:i:s');

// Iniciar transacción
$conn->begin_transaction();

try {
    // Actualizar el estado del ticket
    $sql_update_ticket = "UPDATE tickets SET estado='$estado' WHERE id='$ticket_id'";

    if ($conn->query($sql_update_ticket) === FALSE) {
        throw new Exception("Error al actualizar el estado del ticket: " . $conn->error);
    }

    // Insertar en el historial del ticket
    $historial_descripcion = "Estado actualizado a $estado";
    $sql_insert_historial = "INSERT INTO ticket_history (ticket_id, fecha, Hestado, Hdescripcion) VALUES ('$ticket_id', '$fecha', '$estado', '$historial_descripcion')";

    if ($conn->query($sql_insert_historial) === FALSE) {
        throw new Exception("Error al insertar en el historial del ticket: " . $conn->error);
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
?>
