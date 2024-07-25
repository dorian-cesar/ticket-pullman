<?php
class Ticket {
    private $conn;
    private $table_name = "tickets3";

    public $id;
    public $area;
    public $descripcion;
    public $estado;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . " SET area=:area, descripcion=:descripcion, estado=:estado";
        $stmt = $this->conn->prepare($query);

        $this->area = htmlspecialchars(strip_tags($this->area));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));

        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":estado", $this->estado);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    function read() {
        $query = "SELECT id, area, descripcion, estado, fecha_creacion FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function update($solucion = null) {
        $query = "INSERT INTO ticket_historial3 SET ticket_id=:ticket_id, estado=:estado, solucion=:solucion";
        $stmt = $this->conn->prepare($query);
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $solucion = htmlspecialchars(strip_tags($solucion));

        $stmt->bindParam(':ticket_id', $this->id);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':solucion', $solucion);

        if ($stmt->execute()) {
            $update_query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':estado', $this->estado);
            $update_stmt->bindParam(':id', $this->id);

            if ($update_stmt->execute()) {
                return true;
            }
        }
        return false;
    }
}
?>
