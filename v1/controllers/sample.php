<?php

class SampleController
{
    public $code = 200;
    public $url;
    public $request_body;

    function __construct()
    {
        $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].mb_substr($_SERVER['SCRIPT_NAME'],0,-9).basename(__FILE__, ".php")."/";
        $this->request_body = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
    }

    public function get($id=null):array
    {
        $db = new DB();
        if($this->is_set($id)){
            return $this->getById($db, $id);
        }else{
            return $this->getAll($db);
        }
    }

    private function getById($db,$id):array
    {
        $sql = "SELECT * FROM sample_table WHERE id = :id";
        $sth = $db->pdo()->prepare($sql);
        $sth->bindValue(":id",$id);
        $res = $sth->execute();
        if($res){
            $data = $sth->fetch(PDO::FETCH_ASSOC);
            if(!empty($data)){
                return $data;
            }else{
                $this->code = 404;
                return ["error" => [
                    "type" => "not_in_sample"
                ]];
            }
        }else{
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
    }
    private function getAll($db):array
    {
        $sql = "SELECT * FROM sample_table";
        $sth = $db->pdo()->prepare($sql);
        $res = $sth->execute();
        if($res){
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
    }

    public function post():array
    {
        $post = $this->request_body;
        if(!array_key_exists("id",$post) || !array_key_exists("name",$post) || !array_key_exists("age",$post)){
            $this->code = 400;
            return ["error" => [
                "type" => "invalid_param"
            ]];
        }
        $db = new DB();
        $pdo = $db->pdo();
        $sql = "INSERT INTO sample_table (id, name, age) VALUES (:id, :name, :age)";
        $sth = $pdo->prepare($sql);
        $sth->bindValue(":id",$post["id"]);
        $sth->bindValue(":name",$post["name"]);
        $sth->bindValue(":age",$post["age"]);
        $res = $sth->execute();
        $id = $pdo->lastInsertId();
        if($res){
            $this->code = 201;
            header("Location: ".$this->url.$id);
            return [];
        }else{
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
        
    }

    public function put($id=null):array
    {
        if(!$this->is_set($id)){
            $this->code = 400;
            return ["error" => [
                "type" => "invalid_url"
            ]];
        }
        $original_data = $this->get($id);
        if(empty($original_data)){
            $this->code = 404;
            return ["error" => [
                "type" => "not_in_sample"
            ]];
        }
        $put = array_merge($original_data, $this->request_body);
        $db = new DB();
        $sql = "UPDATE sample_table SET name=:name,age=:age WHERE id=:id";
        $sth = $db->pdo()->prepare($sql);
        $sth->bindValue(":id",$id);
        $sth->bindValue(":name",$put["name"]);
        $sth->bindValue(":age",$put["age"]);
        $res = $sth->execute();
        if($res){
            return [$this->get($id)];
        }else{
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
        
    }

    public function delete($id=null):array
    {
        if(!$this->is_set($id)){
            $this->code = 400;
            return ["error" => [
                "type" => "invalid_url"
            ]];
        }
        $db = new DB();
        $sql = "DELETE FROM sample WHERE id = :id";
        $sth = $db->pdo()->prepare($sql);
        $sth->bindValue(":id",$id);
        $res = $sth->execute();
        if($res){
            $this->code = 204;
            return [];
        }else{
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
    }

    public function options():array
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET,HEAD,POST,PUT,DELETE");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }

    private function is_set($value):bool
    {
        return !(is_null($value) || $value === "");
    }
}