<?php

declare(strict_types=1);
date_default_timezone_set('Africa/Tunis');

require __DIR__ . "./src/config/Database.php";
require __DIR__ . "./src/config/ErrorHandler.php";

require __DIR__ . "./src/controllers/TimeController.php";
require __DIR__ . "./src/controllers/DigiTexController.php";
require __DIR__ . "./src/controllers/OperatorController.php";
require __DIR__ . "./src/controllers/ProductionLineController.php";
require __DIR__ . "./src/controllers/PacketController.php";
require __DIR__ . "./src/controllers/OperationController.php";
require __DIR__ . "./src/controllers/MonitorController.php";

set_exception_handler("ErrorHandler::handleException");

// JSON's HEADERS
header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=UTF-8");

// SPLIT URI(/URL) BY / INTO PARTS
$parts = explode("/", $_SERVER['REQUEST_URI']);

// TODO: ADD API KEY (ENHANCE SECURITY)
// if ($parts[2] !== "api") {
//     http_response_code(404);
//     exit();
// }

// EXAMPLE:     http://localhost/digitex_isa/api/v3/packet/control
//              => $action = packet / $handler = byRFID
$action = $parts[4] ?? null;
$handler = $parts[5] ?? null;

// INITIALIZE DB
// $db = new Database("127.0.0.1", "db_isa", "ISA", "SmarTex2021");
$db = new Database("127.0.0.1", "db_isa", "root", "");

// INITIALIZE MODELS
$digitex = new DigiTex($db);
$operator = new Operator($db);
$productionLine = new ProductionLine($db);
$packet = new Packet($db);
$operation = new Operation($db);
$monitor = new Monitor($db);

// INITIALIZE CONTROLLERS
// $timeController = new TimeController;
$digitexController = new DigiTexController($digitex);
$operatorController = new OperatorController($operator);
$productionLineController = new ProductionLineController($productionLine);
$packetController = new PacketController($packet);
$operationController = new OperationController($operation);
$monitorController = new MonitorController($monitor);

// ROUTING
switch ($action) {
        // case "date":
        //     $timeController->processRequest($handler);
        //     break;

        // case "time":
        //     $timeController->processRequest($handler);
        //     break;

    case "digitex":
        $digitexController->processRequest($handler);
        break;

    case "operator":
        $operatorController->processRequest($handler);
        break;

    case "productionLine":
        $productionLineController->processRequest($handler);
        break;

    case "packet":
        $packetController->processRequest($handler);
        break;

    case "operation":
        $operationController->processRequest($handler);
        break;

    case "monitor":
        $monitorController->processRequest($handler);
        break;
}
