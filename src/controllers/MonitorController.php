<?php

require __DIR__ . "./../models/Monitor.php";


class MonitorController
{
    public function __construct(private Monitor $monitor)
    {
    }

    public function processRequest(string $handler): void
    {
        $method = $_SERVER["REQUEST_METHOD"];

        switch ($handler) {
            case "bySmartBox":
                $this->monitorBySmartBox($method);
                break;
            case "notification":
                $this->createNotification($method);
                break;
        }
    }

    private function monitorBySmartBox(string $method): void
    {
        switch ($method) {
            case "GET":
                $smartBox = explode("/", $_SERVER['REQUEST_URI'])[6] ?? null;
                echo json_encode($this->monitor->getMonitorBySmartBox($smartBox));
                break;

            case "POST":
                // code goes here in case of POST Request...
                break;
        }
    }

    private function createNotification(string $method): void
    {
        switch ($method) {
            case "GET":
                // code goes here in case of GET Request...
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                $rows = $this->monitor->insertNotification($data);

                echo json_encode($rows ? [
                    "message" => "Notification Inserted"
                ] : "{}");
                break;
        }
    }
}
