<?php

use Ramsey\Uuid\Uuid;

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
        if(is_null($id)||$id===""){
            return $this->getAll();
        }
        return $this->getById($id);
    }

    public function post():array
    {
        return $this->postSample();
    }

    
    public function put($id=null):array
    {
        if(is_null($id)||$id===""){
            $this->code = 400;
            return ["error" => [
                "type" => "invalid_url"
            ]];
        }
        return $this->putSample($id);
    }

    public function delete($id=null):array
    {
        // DELETE FROM sample WHERE id = :id
        $this->code = 204;
        return [];
    }
        
    public function options():array
    {
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Allow-Headers: Content-Type");
        return [];
    }



    private function getAll():array
    {
        $db = new DB();

        try{
            $sql = "SELECT * FROM sample_table";
            $sth = $db->pdo->prepare($sql);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getById($id):array
    {
        $db = new DB();

        try{
            $sql = "SELECT * FROM sample_table WHERE id = :id";
            $sth = $db->pdo->prepare($sql);
            $sth->bindValue(":id",$id);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if(empty($data)){
            $this->code = 404;
            return ["error" => [
                "type" => "not_in_sample"
            ]];
        }
        return $data;
    }

    private function postSample():array
    {
        $post = $this->request_body;
        if(!array_key_exists("id",$post) || !array_key_exists("name",$post)){
            $this->code = 400;
            return ["error" => [
                "type" => "invalid_param"
            ]];
        }

        $db = new DB();

        try{
            $sql = "INSERT INTO sample_table (id, name, age) VALUES (:id, :name, :age)";
            $sth = $db->pdo->prepare($sql);
            $sth->bindValue(":id",Uuid::uuid4());
            $sth->bindValue(":name",$post["name"]);
            $sth->bindValue(":age",$post["age"]);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error",
            ]];
        }

        $this->code = 204;
        return [];
    }

    private function putSample($id):array
    {
        $original_data = $this->get($id);
        if(empty($original_data)){
            $this->code = 404;
            return ["error" => [
                "type" => "not_in_sample"
            ]];
        }
        $put = array_merge($original_data, $this->request_body);
        $db = new DB();

        try{
            $sql = "UPDATE sample_table SET name=:name,age=:age WHERE id=:id";
            $sth = $db->pdo->prepare($sql);
            $sth->bindValue(":id",$id);
            $sth->bindValue(":name",$put["name"]);
            $sth->bindValue(":age",$put["age"]);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error",
            ]];
        }

        return [$this->get($id)];
    }
}