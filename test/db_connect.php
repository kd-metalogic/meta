<?php
class DB
{
    public $mysql;

    public function __construct()
    {
        $this->connectDB();
    }

    public function connectDB()
    {
        $this->mysql = new mysqli('localhost', 'myadmin', '%Jc3VBax', 'test');
        if ($this->mysql->connect_error) {
            //var_dump(error_log($this->mysql->connect_error));
            exit;
        } else {
            $this->mysql->set_charset("utf8");
        }
    }

    public function closeDB()
    {
        $this->mysql->close();
    }
}

class DbRead extends DB
{
    public function __construct()
    {
        parent::__construct();
    }

    public $result;
    public function searchDB($query)
    {
        $this->result=$this->mysql->query($query);
    }
}

class DbWrite extends DB
{
    public function __construct()
    {
        parent::__construct();
    }

    public $errstate;

    public $result;
    public function write($query)
    {
        try {
            $this->mysql->begin_transaction();
            $this->result=$this->mysql->query($query);
        } catch (Exception $err) {
            $this->mysql->rollback();
            $this->errstate = $err->getCode();
            if ($err->getCode()==23000) {
                $this->result = ErrState::OVERLAP;
            } else {
                $this->result = ErrState::WRITE;
            }
        }
        $this->mysql->commit();
        $this->closeDB();
        return $this->result;
    }
}

class ErrState
{
    const OVERLAP = 1;
    const WRITE   = 2;
    const READ    = 3;
    const ACCESS  = 4;
}
