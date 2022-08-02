<?php


class Packet
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getPacketByRFID(string $RFID): array | string
    {
        $sql = "SELECT * FROM pipelettes
                WHERE Tag_id = :rfid";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':rfid', $RFID);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            http_response_code(404);

            return "{}";
        }

        return array(
            'id' => $result['id'],
            'quantity' => $result['Qte_a_monter'],
            'packet_number' => $result['N_pipelette'],
            'current_time' => date("H:i:s"),
        );
    }
}
