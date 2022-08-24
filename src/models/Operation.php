<?php


class Operation
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getOperationBySmartBox(string $packetNumber, string $smartBoxName): array | string
    {
        // $operationExist = $this->isOperationExist($packetNumber, $smartBoxName);

        // if ($operationExist) {
        //     http_response_code(400);

        //     return "{}";
        // }

        $sql = "SELECT * FROM gamme WHERE DigiTex LIKE '%$smartBoxName%' AND N_pipelette = '$packetNumber' AND operation_state != 1";
        // SELECT * FROM `gamme` WHERE DigiTex LIKE 'ISA201-70' AND N_pipelette = "37245840";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $stmt->closeCursor();

        if (!$results) {
            http_response_code(400);

            return "{}";
        }

        $filtred_results = [];
        array_push($filtred_results, $results[0]);
        for ($i = 1; $i < count($results); $i++) {
            if ($results[$i]["id"] === $results[$i - 1]["id"] + 1) {
                array_push($filtred_results, $results[$i]);
            }
        }
        foreach ($filtred_results as $filtred_result) {
            $op_id = $filtred_result["id"];
            $op_sql = "UPDATE gamme SET operation_state = 1 WHERE id = '$op_id'";

            $op_stmt = $this->conn->prepare($op_sql);
            $op_stmt->execute();
            $op_stmt->closeCursor();
        }

        $result = array(
            'OF' => $filtred_results[0]['OF'],
            'operation_code' => $filtred_results[0]['Ligne_gamme'],
            'designation' => $filtred_results[0]['Operation_gamme'],
            'machine_id' => $filtred_results[0]['Machine_id'],
            'tps_ope_uni' => (float) $filtred_results[0]['Tps_ope_uni'],
            'h_counter' => date("H"),
            'min_counter' => date("i"),
        );

        if (count($filtred_results) > 1) {
            for ($i = 1; $i < count($filtred_results); $i++) {
                $result['operation_code'] .= ',' . $filtred_results[$i]['Ligne_gamme'];
                $result['designation'] .= ',' . $filtred_results[$i]['Operation_gamme'];
                $result['tps_ope_uni'] += (float) $filtred_results[$i]['Tps_ope_uni'];
            }
        }

        $opCodesList = explode(',', $result['operation_code']);
        $opCodesStrList = [];
        foreach ($opCodesList as $op) {
            array_push($opCodesStrList, (int)($op / 10));
        }

        return array(
            'OF' => $result['OF'],
            'operation_code' => $result['operation_code'],
            'operation_code_str' => implode(',', $opCodesStrList),
            'designation' => $result['designation'],
            'machine_id' => $result['machine_id'],
            'QTE_H' => $result['QTE_H'] ?? (string) floor(60 / ((float) $result['tps_ope_uni'])),
            'tps_ope_uni' => (string) $result['tps_ope_uni'],
            'h_counter' => date("H"),
            'min_counter' => date("i"),
        );
    }

    public function insertOperation(array $data): int
    {
        $tagRFID = $data["tag_RFID"];
        $packetNumber = $data["packet_number"];
        $operationCode = $data["operation_code"];
        $designation = $data["designation"];
        $tps_ope_uni = $data["tps_ope_uni"];
        $quantity = $data["quantity"];
        $operatorRN = $data["registration_number"];
        $firstName = $data["first_name"];
        $lastName = $data["last_name"];
        $machineID = $data["machine_id"];
        $digitex = $data["digitex_smart_box"];
        $current_day = date("d/m/Y");
        $current_time = date("H:i:s");

        $sql = "INSERT INTO pack_operation
            (Pack_id, Code_operation, Operation_name, registration_number, Firstname, Lastname, Machine_id, DigiTex, cur_day, cur_time, tps_ope_uni, quantity)
                VALUES
            ('$packetNumber', '$operationCode', '$designation', '$operatorRN', '$firstName', '$lastName', '$machineID', '$digitex', '$current_day', '$current_time', '$tps_ope_uni', '$quantity');";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $stmt->closeCursor();

        if (str_contains(strtolower($designation), "contr")) {
            // echo "yes";
            $sql2 = "UPDATE controle_packet SET Tag_id = '$tagRFID' WHERE State_Tag = 'here';";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute();
            $stmt2->closeCursor();
        }

        return $stmt->rowCount();
    }

    // private function isOperationExist(string $packetNumber, string $smartBoxName): bool
    // {
    //     // $sql = "SELECT * FROM pack_operation
    //     //         WHERE Pack_id = '$packetNumber' AND DigiTex = '$smartBoxName'";

    //     $sql = "SELECT * FROM `pack_operation`
    //                 WHERE DigiTex = '$smartBoxName'
    //                 ORDER BY `pack_operation`.`id` DESC
    //                 LIMIT 1";

    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute();
    //     $result = $stmt->fetch();
    //     $stmt->closeCursor();

    //     $result = $result["Pack_id"] === $packetNumber ? $result : [];

    //     return (bool) $result;
    // }
}
