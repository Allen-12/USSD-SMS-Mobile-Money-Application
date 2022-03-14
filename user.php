<?php
    include_once 'db.php';
    include_once 'sms.php';

    class User
    {
        protected $name;
        protected $phone;
        protected $pin;
        protected $balance;

        public function __construct($phoneNumber)
        {
            $this->phone = $phoneNumber;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getPhoneNumber()
        {
            return $this->phone;
        }

        public function setPin($PIN)
        {
            $this->pin = $PIN;
        }

        public function getPIN()
        {
            return $this->pin;
        }

        public function setBalance($bal)
        {
            $this->balance = $bal;
        }

        public function getBalance()
        {
            return $this->balance;
        }

        // Registers a user
        public function register($pdo)
        {
            try
            {
                $hashedPIN = password_hash($this->getPIN(), PASSWORD_DEFAULT);
                $statement = $pdo->prepare("INSERT INTO user (name, pin, phone, balance) values (?,?,?,?)");
                $statement->execute([$this->getName(), $hashedPIN, $this->getPhoneNumber(), $this->getBalance()]);
                $sms = new Sms($this->getPhoneNumber());
                $sms->sendSMS("You have been registered successfully!", $this->getPhoneNumber());
            }
            catch (PDOException $e)
            {
                echo $e->getMessage();
            }
        }

        // Checks if user is registered
        public function isUserRegistered($pdo)
        {
            $statement = $pdo->prepare("SELECT * from user WHERE phone=?");
            $statement->execute([$this->getPhoneNumber()]);
            if(count($statement->fetchAll()) > 0)
            {
                return true;
            }
            else
            {
                return false;
            }
        }


        public function readName($pdo)
        {
            $statement = $pdo->prepare("SELECT * from user WHERE phone=?");
            $statement->execute([$this->getPhoneNumber()]);
            $row = $statement->fetch();
            return $row['name'];
        }

        public function readUserID($pdo)
        {
            $statement = $pdo->prepare("SELECT uid from user WHERE phone=?");
            $statement->execute([$this->getPhoneNumber()]);
            $row = $statement->fetch();
            return $row['uid'];
        }

        public function correctPIN($pdo)
        {
            $statement = $pdo->prepare("SELECT pin FROM user where phone=?");
            $statement->execute([$this->getPhoneNumber()]);
            $row = $statement->fetch();

            if($row == null)
            {
                return false;
            }

            if(password_verify($this->getPIN(),$row['pin']))
            {
                return true;
            }

            return false;
        }

        public function checkBalance($pdo)
        {
            $statement = $pdo->prepare("SELECT balance FROM user where phone=?");
            $statement->execute([$this->getPhoneNumber()]);
            $row = $statement->fetch();

            return $row['balance'];
        }
    }
?>