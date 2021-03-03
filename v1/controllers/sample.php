<?php

class SampleController
{
    public $code = 200;
    public $url;

    function __construct()
    {
        $this->url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST']."/php_restapi_templete/v1/sample/";
    }

    public function get($id=null):array
    {
        $db = new DB();
        if($this->is_set($id)){
            $sql = "SELECT * FROM sample_table WHERE id = :id";
            return $db->select_for_id($sql, $id);
        }
        $sql = "SELECT * FROM sample_table";
        $data = $db->select($sql);
        if(!empty($data)){
            $this->code = 404;
            return ["error" => [
                "type" => "not_in_sample"
            ]];
            
        }
        return $data;
    }

    public function post():array
    {
        $post = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
        if(!array_key_exists("id",$post)){
            $this->code = 400;
            return ["error" => [
                "type" => "invalid_param"
            ]];
        }
        $db = new DB();
        $pdo = $db->pdo();
        $sql = "INSERT INTO sample_table (id) VALUES (:id)";
        $sth = $pdo->prepare($sql);
        $sth->bindValue(":id",$post["id"]);
        $res = $sth->execute();
        $id = $pdo->lastInsertId();
        if(!$res){
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
        $this->code = 201;
        header("Location: ".$this->url.$id);
        return [];
    }

    public function put($id=null):array
    {
        $put = json_decode(mb_convert_encoding(file_get_contents('php://input'),"UTF8","ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN"),true);
        if(!$this->is_set($id)){
            $this->code = 400;
            return ["error" => [
                "type" => "invalid_url"
            ]];
        }
        $original_data = $this->get($id);
        if(empty($origin_data)){
            $this->code = 404;
            return ["error" => [
                "type" => "not_in_sample"
            ]];
        }
        $put = array_merge($original_data, $put);
        $db = new DB();
        $sql = "UPDATE sample_table value=:value WHERE id=:id";
        $sth = $db->pdo()->prepare($sql);
        $sth->bindValue(":value",$put["value"]);
        $sth->bindValue(":id",$put["id"]);
        $res = $sth->execute();
        if(!$res){
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
        return [$this->get($id)[0]];
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
        if(!$res){
            $this->code = 500;
            return ["error" => [
                "type" => "fatal_error"
            ]];
        }
        $this->code = 204;
        return [];
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
}