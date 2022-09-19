<?php


class DigiTex
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getDigitexByMacAddr(string $macAddr, string $ipAddr): array | string
    {
        // http://192.168.1.100/digitex_isa/api/v3/digitex/byMacAddr/?macAddr=84:CC:A8:7A:F1:68&ipAddr=192.168.1.36
        $sql = "SELECT * FROM `digitex` WHERE `digitex`.`mac_addr` = :mac_addr";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':mac_addr' => $macAddr]);
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            $sql2 = "INSERT INTO `digitex` (box_name, mac_addr, ip_addr) 
                    VALUES ('UNKNOWN', :mac_addr, :ip_addr)";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([':mac_addr' => $macAddr, ':ip_addr' => $ipAddr]);
            $stmt->fetch();
            $stmt->closeCursor();

            return "{}";
        }

        if ($result && $result['ip_addr'] !== $ipAddr) {
            $sql3 = "UPDATE `digitex` SET `ip_addr` = :ip_addr WHERE `digitex`.`mac_addr` = :mac_addr";
            $stmt3 = $this->conn->prepare($sql3);
            $stmt3->execute([':mac_addr' => $macAddr, ':ip_addr' => $ipAddr]);
            $stmt3->fetch();
            $stmt3->closeCursor();

            $result['ip_addr'] = $ipAddr;
        }

        return $result;
    }
}
