<?php
    include_once 'util.php';
    include_once 'user.php';
    include_once 'transactions.php';
    include_once 'agent.php';
    include_once 'sms.php';

    class Menu
    {
        protected $text;
        protected $sessionID;

        // public function __construct($text,$sessionID)
        // {
        //     $this->text = $text;
        //     $this->sessionID = $sessionID;
        // }
        public function __construct()
        {
            
        }

        public function mainMenuRegistered($name)
        {
            $response = "Welcome $name. Reply with \n";
            $response .= "1. Send Money \n";
            $response .= "2. Withdraw \n";
            $response .= "3. Check Balance";
            return $response;
        }

        public function mainMenuUnRegistered()
        {
            $response = "CON Welcome to this Test App. Reply with \n";
            $response .= "1. Register \n";
            echo $response;
        }

        public function registerMenu($textArray, $phoneNumber, $pdo)
        {
            $level = count($textArray);

            switch ($level)
            {
                case 1:
                    echo 'CON Please enter your full name';
                    break;
                case 2:
                    echo 'CON Please set a new PIN for your account';
                    break;
                case 3:
                    echo 'CON Please re-enter your PIN';
                    break;
                case 4:
                    $name = $textArray[1];
                    $pin = $textArray[2];
                    $confirmPin  = $textArray[3];
                    if($pin != $confirmPin)
                    {
                        echo 'END Your PINs do not match. Please try again.';
                    }
                    else
                    {
                        $user = new User($phoneNumber);
                        $user->setName($name);
                        $user->setPin($pin);
                        $user->setBalance(Util::$USER_BALANCE);
                        $user->register($pdo);
                        echo 'END You have been registered successfully.';
                    }
                    break;
                default:
                    
                    break;
            }
        }

        public function sendMoneyMenu($textArray, $sender, $pdo, $sessionId)
        {
            $level = count($textArray);

            $receiver = null;
            $nameOfReceiver = null;
            $response = "";

            switch ($level)
            {
                case 1:
                    echo 'CON Please enter the mobile number of the intended recipient';
                    break;
                case 2:
                    echo 'CON Please enter the amount you want to send';
                    break;
                case 3:
                    echo 'CON Please input your PIN to confirm the transaction';
                    break;
                case 4:
                    $receiver = new User($this->addCountryCodeToPhoneNumber($textArray[1]));
                    $nameOfReceiver = $receiver->readName($pdo);

                    $response .= "Are you sure you want to send $textArray[2] to $nameOfReceiver - $textArray[1]? \n";
                    $response .= "1. Yes \n";
                    $response .= "2. No \n";
                    $response .= Util::$GO_BACK . ". Back \n";
                    $response .= Util::$GO_TO_MAIN_MENU . ". Main Menu \n";
                    echo "CON " . $response;
                    break;
                case 5:
                    switch ($textArray[4])
                    {
                        case 1:
                            $pin = $textArray[3];
                            $amount = $textArray[2];
                            $transactionType = "send";
                            $sender->setPin($pin);
                            $newSenderBalance = $sender->checkBalance($pdo) - $amount - Util::$TRANSACTION_FEE;
                            $receiver = new User($this->addCountryCodeToPhoneNumber($textArray[1]));
                            $newReceiverBalance = $receiver->checkBalance($pdo) + $amount;

                            if($sender->correctPIN($pdo) == false)
                            {
                                echo "END You entered a wrong PIN.";
                            }
                            else
                            {
                                $transaction = new Transaction($amount, $transactionType);
                                $result = $transaction->sendMoney($pdo, $sender->readUserID($pdo), $receiver->readUserID($pdo),$newSenderBalance, $newReceiverBalance);
                                if($result)
                                {
                                    echo "END We are processing your request. You will receive an SMS shortly.";
                                    // Send SMS
                                }
                                else
                                {
                                    echo "END $result";
                                }
                            }
                            // echo 'END Your request is being processed';
                            break;
                        case 2:
                            echo "END You have cancelled the transaction. Thank you for using this service.";
                            break;
                        case Util::$GO_BACK:
                            echo "END You have requested to go back one step.";
                            break;
                        case Util::$GO_TO_MAIN_MENU:
                            echo "END You have requested to go back to the main menu.";
                            break;
                        default:
                            echo "END Invalid choice. Please try again.";
                            break;
                    }
                    break;
                default:    
                    echo 'END Invalid choice. Please try again.';
                    break;
            }
        }

        public function withdrawMoneyMenu($textArray, $user, $pdo)
        {
            $level = count($textArray);

            switch ($level)
            {
                case 1:
                    echo "CON Please enter the agent number";
                    break;
                case 2:
                    echo "CON Please enter the amount you would like to withdraw";
                    break;
                case 3:
                    echo "CON Please input your PIN";
                    break;
                case 4:
                    $agent = new Agent($textArray[1]);
                    $agentName = $agent->readNameByNumber($pdo);
                    $response = "CON Are you sure you want to withdraw $textArray[2] from agent with name $agentName? \n";
                    $response .= "1. Yes \n";
                    $response .= "2. No \n";
                    $response .= Util::$GO_BACK . ". Back \n";
                    $response .= Util::$GO_TO_MAIN_MENU . ". Main Menu \n";
                    echo $response;
                    break;
                case 5:
                    switch ($textArray[4])
                    {
                        case 1:
                            $user->setPin($textArray[3]);
                            if(!$user->correctPIN($pdo))
                            {
                                echo "END You entered the wrong PIN";
                                return;
                            }
                            if($user->checkBalance($pdo) < $textArray[2] + Util::$TRANSACTION_FEE)
                            {
                                echo "END You have insufficient funds to complete the transaction";
                                return;
                            }
                            $agent = new Agent($textArray[1]);
                            $agentName = $agent->readNameByNumber($pdo);
                            $transactionType = "withdraw";

                            $withdrawTransaction = new Transaction($textArray[2], $transactionType);
                            $newBalance = $user->checkBalance($pdo) - $textArray[2] - Util::$TRANSACTION_FEE;
                            $result = $withdrawTransaction->withdrawCash($pdo, $user->readUserID($pdo), $agent->readIDByNumber($pdo), $newBalance);
                            if($result)
                            {
                                echo 'END Your withdrawal request is being processed.';
                            }
                            else
                            {
                                echo "END $result";
                            }
                            break;
                        case 2:
                            echo "END You have cancelled the withdrawal transaction. Thank you for using this service.";
                            break;
                        case Util::$GO_BACK:
                            echo "END You have requested to go back one step.";
                            break;
                        case Util::$GO_TO_MAIN_MENU:
                            echo "END You have requested to go back to the main menu.";
                            break;
                        default:
                            echo "END Invalid choice. Please try again.";
                            break;
                    }
                    break;
                default:
                    echo "END Invalid choice. Please try again.";
                    break;
            }
        }

        public function checkBalanceMenu($textArray, $user, $pdo)
        {
            $level = count($textArray);

            switch ($level)
            {
                case 1:
                    echo "CON Please input your PIN";
                    break;
                case 2:
                    $user->setPin($textArray[1]);
                    if($user->correctPIN($pdo))
                    {
                        $message = "Your wallet balance is " . $user->checkBalance($pdo) . ". Thank you for using this service";
                        $sms = new Sms($user->getPhoneNumber());
                        $result = $sms->sendSMS($message,$user->getPhoneNumber());
                        if($result['status'] == "Success")
                        {
                            echo "END You will receive an SMS with your balance shortly.";
                        }
                        else
                        {
                            echo "END There was an error retrieving your balance. Please try again later";
                        }               
                    }
                    else
                    {
                        echo "END You entered the wrong PIN.";
                    }
                    break;
                default:
                    echo "END Invalid choice. Please try again.";
                    break;
            }
        }

        // Removes invalid entries, handles go back and go to main menu options
        public function textMiddleware($text, $user, $sessionID, $pdo)
        {
            return $this->removeInvalidEntry($this->goBack($this->goToMainMenu($text)), $user, $sessionID, $pdo);
        }

        public function goBack($text)
        {
            $explodedText = explode("*", $text);
            while(array_search(Util::$GO_BACK, $explodedText) != false)
            {
                $firstIndex = array_search(Util::$GO_BACK, $explodedText);
                array_splice($explodedText, $firstIndex - 1, 2);
            }
            return join("*", $explodedText);
        }

        public function goToMainMenu($text)
        {
            $explodedText = explode("*", $text);
            while(array_search(Util::$GO_TO_MAIN_MENU, $explodedText) != false)
            {
                $firstIndex = array_search(Util::$GO_TO_MAIN_MENU, $explodedText);
                $explodedText = array_slice($explodedText, $firstIndex + 1);
            }
            return join("*", $explodedText);
        }

        public function persistInvalidEntry($sessionId, $user, $ussdLevel, $pdo)
        {
            $statement = $pdo->prepare("INSERT INTO ussdsession (sessionId, ussdLevel) VALUES (?,?)");
            $statement->execute([$sessionId, $ussdLevel]);
            // $statement = null;
        }

        public function removeInvalidEntry($ussdString, $user, $sessionID, $pdo)
        {
            $statement = $pdo->prepare("SELECT ussdLevel FROM ussdsession WHERE sessionId=?");
            $statement->execute([$sessionID]);
            $result = $statement->fetchAll();
            if (count($result) == 0)
            {
                return $ussdString;
            }
            
            $ussdStringArray = explode("*", $ussdString);
            
            foreach($result as $value)
            {
                unset($ussdStringArray[$value['ussdLevel']]);
            }

            $ussdStringArray = array_values($ussdStringArray);

            return join("*", $ussdStringArray);
        }

        public function addCountryCodeToPhoneNumber($phoneNumber)
        {
            return Util::$COUNTRY_CODE . substr($phoneNumber, 1);
        }
    }
?>