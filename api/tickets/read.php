<?php
header("Content-Type: application/json");

include '../../config/config.php';
date_default_timezone_set('America/Santiago');

$sql = "SELECT tickets.*, ticket_history.* FROM tickets LEFT JOIN ticket_history ON tickets.id = ticket_history.ticket_id  order by  tickets.id desc";
$result = $conn->query($sql);

$tickets = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ticket_id = $row['id'];
        if (!isset($tickets[$ticket_id])) {
            $tickets[$ticket_id] = [
                'id' => $row['id'],
                'area_solicitante' => $row['area_solicitante'],
                'area_ejecutora' => $row['area_ejecutora'],
                'tipo_atencion' => $row['tipo_atencion'],
                'producto' => $row['producto'],
                'descripcion' => $row['descripcion'],
                'estado' => $row['estado'],
                'fecha_creacion' => $row['fecha_creacion'],
                'email' => $row['email'],
                'historial' => []
            ];
        }
        if ($row['historial_id']) {
            $tickets[$ticket_id]['historial'][] = [
                'fecha' => $row['fecha'],
                'Hestado' => $row['Hestado'],
                'Hdescripcion' => $row['Hdescripcion']
            ];
        }
    }
}

echo json_encode(array_values($tickets));

$conn->close();
?>









