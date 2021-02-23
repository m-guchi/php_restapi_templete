<?php

include(__DIR__ . "/config/database.php");

class DB
{
    function pdo()
    {
        global $setting;
        try{
            $driver_option = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($setting["dsn"],$setting["user"],$setting["password"],$driver_option);
        }catch(PDOException $error){
            echo "error:".$error->getMessage();
            die();
        }
        return $pdo;
    }

    function select($sql)
    {
        $pdo = $this->pdo();
        $sth = $pdo->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
}