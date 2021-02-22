<?php

class SampleController
{
    public $code = 200;

    public function get($arg1=null):array
    {
        $db = new DB();
        $sql = "SELECT * FROM table";
        return $db->select($sql);
    }

    public function post():array
    {
        parse_str(file_get_contents('php://input'),$post);
        if(array_key_exists("id"),$post){
            $db = new DB();
            $sql = "INSERT INTO table (id) VALUES (:id)";
            $sth = $db->pdo()->prepare($sql);
            $sth->bindValue(":id",$post["id"]);
            $res = $sth->execute();
            return ["ok" => $res];
        }
    }
}