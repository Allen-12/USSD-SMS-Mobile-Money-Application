<?php
    class Agent
    {
        protected $name;
        protected $number;

        public function __construct($number)
        {
            $this->number = $number;
        }

        public function getNumber()
        {
            return $this->number;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function readNameByNumber($pdo)
        {
            $statement = $pdo->prepare("SELECT name FROM agent WHERE agentNumber=?");
            $statement->execute([$this->getNumber()]);
            $row = $statement->fetch();
            if($row != null)
            {
                return $row['name'];
            }
            else
            {
                return false;
            }
        }

        public function readIDByNumber($pdo)
        {
            $statement = $pdo->prepare("SELECT aid FROM agent WHERE agentNumber=?");
            $statement->execute([$this->getNumber()]);
            $row = $statement->fetch();
            if($row != null)
            {
                return $row['aid'];
            }
            else
            {
                return false;
            }
        }
    }
?>