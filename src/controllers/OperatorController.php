<?php

require __DIR__ . "./../models/Operator.php";


class OperatorController
{
    public function __construct(private Operator $operator)
    {
    }

    public function processRequest(string $handler): void
    {
        $method = $_SERVER["REQUEST_METHOD"];

        switch ($handler) {
            case "byRFID":
                $this->operatorByRFID($method);
                break;
            case "presence":
                $this->operatorPresenceTime($method);
                break;
            case "downtime":
                $this->operatorDownTime($method);
                break;
                break;
            case "performance":
                $this->operatorPerformance($method);
                break;
        }
    }

    private function operatorByRFID(string $method): void
    {
        switch ($method) {
            case "GET":
                $RFID = explode("/", $_SERVER['REQUEST_URI'])[6] ?? null;
                echo json_encode($this->operator->getOperatorByRFID($RFID));
                break;
            case "POST":
                // code goes here in case of POST Request...
                break;
        }
    }

    private function operatorPresenceTime(string $method): void
    {
        switch ($method) {
            case "GET":
                // code goes here in case of GET Request...

                break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->operator->addOperatorPresenceTime($data);

                echo json_encode($rows ? [
                    "message" => "Presence Inserted"
                ] : "{}");
                break;
        }
    }

    private function operatorDownTime(string $method): void
    {
        switch ($method) {
            case "GET":
                // code goes here in case of GET Request...

                break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->operator->addOperatorDownTime($data);

                echo json_encode($rows ? [
                    "message" => "Downtime Inserted"
                ] : "{}");
                break;
        }
    }

    private function operatorPerformance(string $method): void
    {
        switch ($method) {
            case "GET":
                $registrationNumber = explode("/", $_SERVER['REQUEST_URI'])[6] ?? null;
                echo json_encode($this->operator->getOperatorPerformance($registrationNumber));
                break;

            case "POST":
                // code goes here in case of POST Request...
                break;
        }
    }
}
