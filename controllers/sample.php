<?php

class SampleController
{
    public $code = 200;

    public function get($id=null):array
    {
        $db = new DB();
        if($this->is_set($id)){
            $sql = "SELECT * FROM sample_table WHERE id = :id";
            return $db->select_for_id($sql, $id);
        }else{
            $sql = "SELECT * FROM sample_table";
            return $db->select($sql);
        }
    }

    public function post():array
    {
        $post = $this->response_body();
        if(array_key_exists("id",$post)){
            $db = new DB();
            $sql = "INSERT INTO sample_table (id) VALUES (:id)";
            $sth = $db->pdo()->prepare($sql);
            $sth->bindValue(":id",$post["id"]);
            $res = $sth->execute();
            return ["ok" => $res];
        }else{
            return ["ok" => false];
        }
    }

    public function put($id=null):array
    {
        $put = $this->response_body();
        if($this->is_set($id)){
            $original_data = $this->get($id);
            $put = array_merge($original_data, $put);
            $db = new DB();
            $sql = "UPDATE sample_table value=:value WHERE id=:id";
            $sth = $db->pdo()->prepare($sql);
            $sth->bindValue(":value",$put["value"]);
            $sth->bindValue(":id",$put["id"]);
            $res = $sth->execute();
            return ["ok" => $res, "data" => $this->get($id)];
        }else{
            return ["ok" => false];
        }
    }

    public function options()
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET,HEAD,POST,PUT,DELETE");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }

    private function is_set($value):bool
    {
        return !(is_null($value) || $value === "");
    }
    private function response_body()
    {
        parse_str(file_get_contents('php://input'),$body);
        // $body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
        return $body;
    }
}