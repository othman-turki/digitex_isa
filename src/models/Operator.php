<?php


class Operator
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getOperatorByRFID(string $RFID): array | string
    {
        $sql = "SELECT * FROM rendement
                WHERE RFID = :rfid";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':rfid', $RFID);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if (!$result) {
            http_response_code(400);

            return "{}";
        }

        return array(
            'id' => $result['id'],
            'first_name' => $result['Firstname'],
            'last_name' => $result['Lastname'],
            'registration_number' => $result['registration_num'],
            'current_time' => date("H:i:s"),
        );
    }

    public function addOperatorPresenceTime(array $data): int
    {
        $digitex = $data["digitex_smart_box"];
        $opRFID = $data["operator_rfid"];
        $operatorRN = $data["registration_number"];
        $firstName = $data["first_name"];
        $lastName = $data["last_name"];
        $inOut = $data["in_out"];
        $current_day = date("d/m/Y");
        $current_time = date("H:i:s");

        $sql = "INSERT INTO presence (digitex, registration_number, first_name, last_name, cur_day, cur_time, in_out)
                VALUES ('$digitex', '$operatorRN', '$firstName', '$lastName', '$current_day', '$current_time', '$inOut')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        if ($inOut === 1) {
            $this->operatorPerformance($digitex, $opRFID, $operatorRN, $firstName, $lastName, $current_day, $current_time);
        }

        return $stmt->rowCount();
    }

    public function addOperatorDownTime(array $data): int
    {
        $digitex = $data["digitex_smart_box"];
        $operatorRN = $data["registration_number"];
        $firstName = $data["first_name"];
        $lastName = $data["last_name"];
        $downtime = $data["downtime"];
        $current_day = date("d/m/Y");
        $current_time = date("H:i:s");

        $sql = "INSERT INTO downtimes
            (digitex, registration_number, first_name, last_name, cur_day, cur_time, downtime)
                VALUES
            ('$digitex', '$operatorRN', '$firstName', '$lastName', '$current_day', '$current_time', '$downtime')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        return $stmt->rowCount();
    }

    private function operatorPerformance(string $digitex, string $opRFID, string $operatorRN, string $firstName, string $lastName, string $current_day, string $current_time): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM pack_operation WHERE Firstname = :firstName AND Lastname = :lastName AND cur_day = :curDay");
        $stmt->execute(['firstName' => $firstName, 'lastName' => $lastName, 'curDay' => $current_day]);
        $results = $stmt->fetchAll();
        $stmt->closeCursor();

        $productionTime = 0;
        $presenceTime = 0;
        $downtimes = 0;

        foreach ($results as $result) {
            // $packetNumber = $result['Pack_id'];
            // $operationCode = $result['Code_operation'];
            // $stmt1 = $this->conn->prepare("SELECT * FROM pipelettes WHERE N_pipelette = :packetNumber");
            // $stmt1->execute(['packetNumber' => $packetNumber]);
            // $result1 = $stmt1->fetch();
            // $stmt1->closeCursor();
            // $qte = $result1['Qte_a_monter'];

            // // ISA
            // $stmt2 = $this->conn->prepare("SELECT * FROM gamme WHERE Ligne_gamme = :operationCode AND N_pipelette = :packetNumber");
            // $stmt2->execute(['operationCode' => $operationCode, 'packetNumber' => $packetNumber]);

            // // ETC
            // // $stmt2 = $this->conn->prepare("SELECT * FROM gamme WHERE Ligne_gamme = :operationCode");
            // // $stmt2->execute(['operationCode' => $operationCode]);

            // $result2 = $stmt2->fetch();
            // $stmt2->closeCursor();
            // $temps_uni = $result2['Tps_ope_uni'];

            $temps_uni = $result['tps_ope_uni'];
            $qte = $result['quantity'];

            $productionTime += (float) $qte * (float) $temps_uni;
        }

        $stmt3 = $this->conn->prepare("SELECT * FROM presence WHERE first_name = :firstName AND last_name = :lastName AND cur_day = :curDay");
        $stmt3->execute(['firstName' => $firstName, 'lastName' => $lastName, 'curDay' => $current_day]);
        $results3 = $stmt3->fetchAll();
        $stmt3->closeCursor();

        $processedPresenceTimes = [];
        array_push($processedPresenceTimes, $results3[0]);
        for ($i = 1; $i < count($results3); $i++) {
            if ($results3[$i]["in_out"] !== $results3[$i - 1]["in_out"]) {
                array_push($processedPresenceTimes, $results3[$i]);
            }
        }
        for ($i = 0; $i < count($processedPresenceTimes); $i++) {
            $presenceTime = !$processedPresenceTimes[$i]['in_out'] ? $presenceTime - (int) strtotime($processedPresenceTimes[$i]['cur_time']) : $presenceTime + (int) strtotime($processedPresenceTimes[$i]['cur_time']);
        }


        $stmt4 = $this->conn->prepare("SELECT * FROM downtimes WHERE digitex = :digitex AND first_name = :firstName AND last_name = :lastName AND cur_day = :curDay");
        $stmt4->execute(['digitex' => $digitex, 'firstName' => $firstName, 'lastName' => $lastName, 'curDay' => $current_day]);
        $results4 = $stmt4->fetchAll();
        $stmt4->closeCursor();

        if ($results4) {
            foreach ($results4 as $result4) {
                $downtimes += (int) $result4['downtime'];
            }
        }

        $presenceTime = round($presenceTime / 60.0, 3);
        $performance = round((($productionTime + $downtimes) / $presenceTime), 3);

        // INSERT IF NOT EXIST ELSE UPDATE
        $stmt5 = $this->conn->prepare("SELECT * FROM performances WHERE first_name = '$firstName' AND last_name = '$lastName' AND cur_day = '$current_day'");
        $stmt5->execute();
        $result5 = $stmt5->fetch();
        $stmt5->closeCursor();

        if (!$result5) {
            $sql6 = "INSERT INTO performances
            (op_rfid, registration_num, first_name, last_name, production_time, presence_time, downtimes, performance, cur_day, cur_time)
                VALUES
            ('$opRFID', '$operatorRN', '$firstName', '$lastName', '$productionTime', '$presenceTime', '$downtimes', '$performance', '$current_day', '$current_time')";
            $stmt6 = $this->conn->prepare($sql6);
            $stmt6->execute();
            $stmt6->closeCursor();
        } else {
            $sql7 = "UPDATE performances
                SET production_time = '$productionTime', presence_time= '$presenceTime', downtimes = '$downtimes', performance = '$performance', cur_time = '$current_time'
                WHERE first_name = '$firstName' AND last_name = '$lastName' AND cur_day = '$current_day'";

            $stmt7 = $this->conn->prepare($sql7);
            $stmt7->execute();
            $stmt7->closeCursor();
        }
        return;
    }

    public function getOperatorPerformance(string $registrationNumber): array
    {
        $current_day = date("d/m/Y");
        // $current_time = date("H:i:s");

        $sql = "SELECT * FROM performances
                WHERE 
                    registration_num = '$registrationNumber'
                    AND cur_day = '$current_day'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();

        return array(
            // "id" => $result["id"],
            "registration_number" => $result["registration_num"],
            "first_name" => $result["first_name"],
            "last_name" => $result["last_name"],
            "production_time" => $result["production_time"],
            "presence_time" => $result["presence_time"],
            "downtimes" => $result["downtimes"],
            "performance" => $result["performance"],
            "current_day" => $current_day,
        );
    }
}
