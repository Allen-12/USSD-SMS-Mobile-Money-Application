<?php
    require 'vendor/autoload.php';
    use AfricasTalking\SDK\AfricasTalking;

    include_once 'db.php';
    include_once 'util.php';

    class Sms
    {
        protected $phone;
        protected $AT;

        public function __construct($phone)
        {
            $this->phone = $phone;
            $this->AT = new AfricasTalking(Util::$API_USERNAME, Util::$API_KEY);
        }

        public function getPhone()
        {
            return $this->phone;
        }

        public function sendSMS($message, $recipients)
        {
            $sms = $this->AT->sms();

            $result = $sms->send([
                'to' => $recipients,
                'message' => $message,
                'from' => Util::$AT_SHORTCODE,
            ]);

            return $result;
        }

        public function fetchRecipients()
        {
            $db = new DBConnector();
            $pdo = $db->connectDB();

            $statement = $pdo->prepare("SELECT phone FROM user");
            $statement->execute();
            $result = $statement->fetchAll();

            $recipients = array();

            foreach($result as $row)
            {
                array_push($recipients,$row['phone']);
            }

            return join(",", $recipients);
        }

        public function subscribeUser($pdo, $shortCode, $keyword)
        {
            $statement = $pdo->prepare("INSERT INTO subscribers (phoneNumber, shortcode, keyword, isActive) VALUES (?,?,?,?)");
            $statement->execute([$this->getPhone(), $shortCode, $keyword, 1]);
            echo "Added user to DB";
        }

        public function unsubscribeUser($pdo, $shortCode, $keyword)
        {
            $statement = $pdo->prepare("UPDATE subscribers SET isActive=? WHERE phoneNumber=? AND shortcode=? AND keyword=?");
            $statement->execute([0, $this->getPhone(), $shortCode, $keyword]);
        }

        public function subscribeUserWithToken($shortCode, $keyword, $phone)
        {
            $content = $this->AT->content();
            $checkoutToken = $this->getToken();
            $response = $content->createSubscription([
                'shortCode' => $shortCode,
                'keyword' => $keyword,
                'phoneNumber' => $phone,
                'checkoutToken' => $checkoutToken,
            ]);
        }

        public function getToken($phone)
        {
            $token = $this->AT->token();
            $tokenResult = $token->createCheckoutToken([
                'phoneNumber' => $phone,
            ]);
            $checkoutToken = $tokenResult['data']->token;
            return $checkoutToken;
        }

        public function sendPremiumSMS($pdo, $shortCode, $keyword, $message)
        {
            $recipients = $this->fetchActivePhoneNumbers($pdo, $shortCode, $keyword);
            $content = $this->AT->content();
            $content->send([
                'message' => $message,
                'to' => $recipients,
                'from' => $shortCode,
                'keyword' => $keyword,
            ]);
        }

        public function fetchActivePhoneNumbers($pdo, $shortCode, $keyword)
        {
            $statement = $pdo->prepare("SELECT phoneNumber FROM subscribers WHERE isActive=? AND shortcode=? AND keyword=?");
            $statement->execute([1, $shortCode, $keyword]);
            $activePhoneNumbers = $statement->fetchAll();
            $recipients = array();
            foreach ($activePhoneNumbers as $phone)
            {
                array_push($recipients, $phone['phoneNumber']);
            }

            return $recipients;
        }

        public function fetchInactivePhoneNumbers()
        {
            
        }
    }
?>