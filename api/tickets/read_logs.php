<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../objects/ticket.php';

$database = new Database();
$db = $database->getConnection();

$ticket = new Ticket($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)){
    $ticket->id = $data->id;

    $stmt = $ticket->readLogs();
    $num = $stmt->rowCount();

    if($num > 0){
        $logs_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $log_item = array(
                "estado" => $estado,
                "fecha" => $fecha
            );
            array_push($logs_arr, $log_item);
        }
        echo json_encode($logs_arr);
    } else {
        echo json_encode(array());
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Datos incompletos."));
}
?>
