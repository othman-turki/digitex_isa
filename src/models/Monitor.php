<?php


class Monitor
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getMonitorBySmartBox(string $smartBox)
    {
        $sql = "SELECT * FROM chaine_201
                WHERE DigiTex = '$smartBox'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            http_response_code(404);

            return "{}";
        }

        return array(
            'id' => $result['id'],
            'first_name' => $result['Firstname'],
            'last_name' => $result['Lastname'],
            'monitor' => $result['Instructor_name'],
        );
    }

    public function insertNotification(array $data): int
    {
        $productionLine = $data["production_line"];
        $packetNumber = $data["packet_number"];
        $smartBox = $data["digitex_smart_box"];
        $firstName = $data["first_name"];
        $lastName = $data["last_name"];
        $monitorName = $data["monitor_name"];
        $current_time = date("H:i:s");

        $sql = "INSERT INTO notification
            (Chaine_id, piplette_num, DigiTex, Firstname, Lastname, Instructor_name, Call_instructor, Instant_call_instructor, Instructor_arrival_time, Call_maintainer, Instant_call_maintainer, Maintainer_arrival_time)
                VALUES
            ('$productionLine','$packetNumber','$smartBox','$firstName','$lastName','$monitorName','true','$current_time','','','','')";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        return $stmt->rowCount();
    }
}
