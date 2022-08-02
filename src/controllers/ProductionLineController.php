<?php

require __DIR__ . "./../models/ProductionLine.php";


class ProductionLineController
{
    public function __construct(private ProductionLine $productionLine)
    {
    }

    public function processRequest(string $handler): void
    {
        $method = $_SERVER["REQUEST_METHOD"];

        switch ($handler) {
            case "bySmartBox":
                $this->productionLineBySmartBox($method);
                break;
        }
    }

    private function productionLineBySmartBox(string $method): void
    {
        switch ($method) {
            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->productionLine->updateProductionLineBySmartBox($data);

                echo json_encode($rows ? [
                    "message" => "Production Line updated"
                ] : "{}");
                break;
        }
    }
}
