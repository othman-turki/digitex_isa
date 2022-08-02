<?php

require __DIR__ . "./../models/Operation.php";


class OperationController
{
    public function __construct(private Operation $operation)
    {
    }

    public function processRequest(string $handler): void
    {
        $method = $_SERVER["REQUEST_METHOD"];

        switch ($handler) {
            case "bySmartBox":
                $this->operationBySmartBox($method);
                break;
            case "create":
                $this->createOperation($method);
                break;
        }
    }

    private function operationBySmartBox(string $method): void
    {
        switch ($method) {
            case "GET":
                $packetNumber = $_GET["packetNumber"];
                $smartBoxName = explode("/", $_SERVER['REQUEST_URI'])[6] ?? null;
                echo json_encode($this->operation->getOperationBySmartBox($packetNumber, $smartBoxName));
                break;
            case "POST":
                // code goes here in case of POST Request...
                break;
        }
    }

    private function createOperation(string $method): void
    {
        switch ($method) {
            case "GET":
                // code goes here in case of GET Request...
                break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->operation->insertOperation($data);

                echo json_encode($rows ? [
                    "message" => "Operation Inserted"
                ] : "{}");
                break;
        }
    }
}
