<?php
    include_once 'sms.php';
    include_once 'db.php';

    $phoneNumber = $_POST['phoneNumber'];
    $shortcode = $_POST['shortCode'];
    $keyword = $_POST['keyword'];
    $updateType = $_POST['updateType'];

    $sms = new Sms($phoneNumber);

    $db = new DBConnector();
    $pdo = $db->connectDB();

    if($updateType == 'addition')
    {
        $sms->subscribeUser($pdo, $shortcode, $keyword);
    }
    else
    {
        $sms->unsubscribeUser($pdo, $shortcode, $keyword);
    }
?>