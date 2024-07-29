<?php
header("Content-Type: application/json");

include '../../config/config.php';

$sql = "SELECT tickets.*, ticket_history.* FROM tickets LEFT JOIN ticket_history ON tickets.id = ticket_history.ticket_id ";
$result = $conn->query($sql);

foreach ($result as $item){
    echo $item['area_solicitante'];
}

