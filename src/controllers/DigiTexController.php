<?php

require __DIR__ . "./../models/DigiTex.php";


class DigiTexController
{
    public function __construct(private DigiTex $digitex)
    {
    }

    public function processRequest(string $handler): void
    {
        $method = $_SERVER["REQUEST_METHOD"];

        switch ($handler) {
            case "byMacAddr":
                $this->digitexByMacAddr($method);
                break;
        }
    }

    private function digitexByMacAddr(string $method): void
    {
        switch ($method) {
            case "GET":
                $macAddr = $_GET["macAddr"];
                $ipAddr = $_GET["ipAddr"];
                // $this->digitex->getDigitexByMacAddr($macAddr, $ipAddr);  // debug
                echo json_encode($this->digitex->getDigitexByMacAddr($macAddr, $ipAddr));
                break;
            case "POST":
                // code goes here in case of POST Request...
                break;
        }
    }
}
