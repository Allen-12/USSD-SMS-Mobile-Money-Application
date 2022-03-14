<?php
// http://4fcc-197-232-67-250.ngrok.io/index.php - Callback URL

    include_once 'menu.php';
    include_once 'db.php';
    include_once 'user.php';

    // Read the variables sent via POST from our API
    $sessionId   = $_POST["sessionId"];
    $serviceCode = $_POST["serviceCode"];
    $phoneNumber = $_POST["phoneNumber"];
    $text        = $_POST["text"];

    $user = new User($phoneNumber);

    $db = new DBConnector();
    $pdo = $db->connectDB();

    $menu = new Menu();
    $text = $menu->textMiddleware($text, $user, $sessionId, $pdo);

    if($text == "" && $user->isUserRegistered($pdo))
    {
        // User is registered and $text is empty
        echo "CON " . $menu->mainMenuRegistered($user->readName($pdo));

    }
    else if($text == "" && !$user->isUserRegistered($pdo))
    {
        // User is not registered and $text is empty
        $menu->mainMenuUnRegistered();
    }
    else if(!$user->isUserRegistered($pdo))
    {
        // User is not registered and $text is not empty. User wants to register.
        $textArray = explode("*", $text);
        switch ($textArray[0])
        {
            case 1:
                $menu->registerMenu($textArray, $phoneNumber, $pdo);
                break;
            default:
                $ussdLevel = count($textArray) - 1;
                $menu->persistInvalidEntry($sessionId, $user, $ussdLevel, $pdo);
                echo "CON Invalid menu option selected. Please try again \n" . $menu->mainMenuRegistered($user->readName($pdo));
                break;
        }
    }
    else
    {
        // User is registered and $text is not empty
        $textArray = explode("*", $text);
        switch ($textArray[0])
        {
            case 1:
                $menu->sendMoneyMenu($textArray, $user, $pdo, $sessionId);
                break;
            case 2:
                $menu->withdrawMoneyMenu($textArray, $user, $pdo);
                break;
            case 3:
                $menu->checkBalanceMenu($textArray, $user, $pdo);
                break;
            default:
                // Get index of invalid option in textArray
                $ussdLevel = count($textArray) - 1;
                $menu->persistInvalidEntry($sessionId, $user, $ussdLevel, $pdo);
                echo "CON Invalid menu option selected. Please try again \n" . $menu->mainMenuRegistered($user->readName($pdo));
                break;
        }
    }

?>