<?php
    include_once 'db.php';

    $date = $_POST["date"];
    $sessionId   = $_POST["sessionId"];
    $serviceCode = $_POST["serviceCode"];
    $networkCode = $_POST["networkCode"];
    $phoneNumber = $_POST["phoneNumber"];
    $status = $_POST["status"];
    $cost = $_POST["cost"];
    $durationInMillis = $_POST["durationInMillis"];
    $input = $_POST["input"];
    $lastAppResponse = $_POST["lastAppResponse"];
    $errorMessage = $_POST["errorMessage"];

    $db = new DBConnector();
    $pdo = $db->connectDB();

    saveUSSDNotification($pdo,$date, $sessionId, $serviceCode, $networkCode, $phoneNumber, $status, $cost, $durationInMillis, $input, $lastAppResponse, $errorMessage);

    function saveUSSDNotification($pdo, $date, $sessionId, $serviceCode, $networkCode, $phoneNumber, $status, $cost, $durationInMillis, $input, $lastAppResponse, $errorMessage)
    {
        $statement = $pdo->prepare("INSERT INTO ussdnotifications (date_, sessionId, serviceCode, networkCode, phoneNumber, status, cost, durationInMillis, input, lastAppResponse, errorMessage)
                                            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $statement->execute([$date, $sessionId, $serviceCode, $networkCode, $phoneNumber, $status, $cost, $durationInMillis, $input, $lastAppResponse, $errorMessage]);
    }
?>