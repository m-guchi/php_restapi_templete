<?php

include(__DIR__ . "/config/database.php");

class DB
{
    public $pdo;

    public function __construct()
    {
        $this->pdo = $this->pdo();
    }

    public function pdo()
    {
        global $setting;
        try{
            $driver_option = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($setting["dsn"],$setting["user"],$setting["password"],$driver_option);
        }catch(PDOException $error){
            header("Content-Type: application/json; charset=utf-8", true, 500);
            echo json_encode(["error" => ["type" => "server_error","message"=>$error->getMessage()]]);
            die();
        }
        return $pdo;
    }
}