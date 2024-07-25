<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/ticket.php';

$database = new Database();
$db = $database->getConnection();

$ticket = new Ticket($db);

$stmt = $ticket->read();
$num = $stmt->rowCount();

if($num > 0){
    $tickets_arr = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $ticket_item = array(
            "id" => $id,
            "area" => $area,
            "descripcion" => $descripcion,
            "estado" => $estado,
            "fecha_creacion" => $fecha_creacion
        );
        $historial_stmt = $db->prepare("SELECT estado, fecha_cambio, solucion FROM ticket_historial3 WHERE ticket_id = :ticket_id ORDER BY fecha_cambio DESC");
        $historial_stmt->bindParam(':ticket_id', $id);
        $historial_stmt->execute();
        $historial = $historial_stmt->fetchAll(PDO::FETCH_ASSOC);
        $ticket_item['historial'] = $historial;
        array_push($tickets_arr, $ticket_item);
    }
    echo json_encode($tickets_arr);
} else {
    echo json_encode(array());
}
?>
