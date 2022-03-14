<?php
    class Transaction
    {
        protected $amount;
        protected $transactionType;

        public function __construct($amount, $type)
        {
            $this->amount = $amount;
            $this->transactionType = $type;
        }

        public function getAmount()
        {
            return $this->amount;
        }

        public function getTransactionType()
        {
            return $this->transactionType;
        }

        public function sendMoney($pdo, $uid, $ruid, $newSenderBalance, $newReceiverBalance)
        {
            $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
            
            try
            {
                $pdo->beginTransaction();

                $statement1 = $pdo->prepare("INSERT INTO transaction (amount, uid, ruid, ttype) VALUES (?,?,?,?)");
                $statement2 = $pdo->prepare("UPDATE user SET balance=? WHERE uid=?");

                $statement1->execute([$this->getAmount(), $uid, $ruid, $this->getTransactionType()]);
                $statement2->execute([$newSenderBalance, $uid]);
                $statement2->execute([$newReceiverBalance, $ruid]);

                $pdo->commit();
                return true;
            }
            catch (Exception $e)
            {
                $pdo->rollBack();
                return "An error was encountered. Please try again later.";
            }
        }

        public function withdrawCash($pdo, $uid, $aid, $newBalance)
        {
            $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
            
            try
            {
                $pdo->beginTransaction();

                $statement1 = $pdo->prepare("INSERT INTO transaction (amount, uid, aid, ttype) VALUES (?,?,?,?)");
                $statement2 = $pdo->prepare("UPDATE user SET balance=? WHERE uid=?");

                $statement1->execute([$this->getAmount(), $uid, $aid, $this->getTransactionType()]);
                $statement2->execute([$newBalance, $uid]);

                $pdo->commit();
                return true;
            }
            catch (PDOException $e)
            {
                $pdo->rollBack();
                echo $e->getMessage();
                return "An error occurred. Please try again later.";
            }
        }
    }
?>