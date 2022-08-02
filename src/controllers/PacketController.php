<?php

require __DIR__ . "./../models/Packet.php";


class PacketController
{
    public function __construct(private Packet $packet)
    {
    }

    public function processRequest(string $handler): void
    {
        $method = $_SERVER["REQUEST_METHOD"];

        switch ($handler) {
            case "byRFID":
                $this->packetByRFID($method);
                break;
        }
    }

    private function packetByRFID(string $method): void
    {
        switch ($method) {
            case "GET":
                $RFID = explode("/", $_SERVER['REQUEST_URI'])[6] ?? null;
                echo json_encode($this->packet->getPacketByRFID($RFID));
                break;
            case "POST":
                // code goes here in case of POST Request...
                break;
        }
    }
}
