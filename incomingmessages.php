<?php
    include_once 'util.php';
    include_once 'db.php';
    include_once 'user.php';

    // Receive data from gateway
    $phoneNumber = $_POST['from'];
    $text = $_POST['text'];

    $user = new User($phoneNumber);

    $db = new DBConnector();
    $pdo = $db->connectDB();

    $explodedText = explode(" ", $text);

    $user->setName($explodedText[0]);
    $user->setPin($explodedText[1]);
    $user->setBalance(Util::$USER_BALANCE);

    $user->register($pdo);

    echo "User registered successfully";
?>