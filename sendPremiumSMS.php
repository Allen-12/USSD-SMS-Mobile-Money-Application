<?php
    include_once 'sms.php';
    include_once 'db.php';

    $sms = new Sms('012345678');
    $db = new DbConnector();
    $pdo = $db->connectDB();

    $shortcode = $_POST['shortCode'];
    $keyword = $_POST['keyword'];
    $message = $_POST['message'];

    $response = $sms->sendPremiumSMS($pdo, $shortcode, $keyword, $message);
    echo json_encode($response);
?>