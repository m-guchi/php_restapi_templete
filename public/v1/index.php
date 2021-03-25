<?php

include(__DIR__ . "/../../DB.php");

date_default_timezone_set('Asia/Tokyo');

preg_match('|'.dirname($_SERVER["SCRIPT_NAME"]).'/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$paths = explode('/',$matches[1]);
$file = array_shift($paths);

$file_path = './controllers/'.$file.'.php';
if(file_exists($file_path)){
    include($file_path);
    $class_name = ucfirst($file)."Controller";
    $method_name = strtolower($_SERVER["REQUEST_METHOD"]);
    $object = new $class_name();
    $method = $method_name==="head" ? "get": $method_name;
    if(method_exists($object, $method)){
        $response = json_encode($object->$method(...$paths));
        $response_code = $object->code ?? 200;
    }else{
        $response = json_encode(["error"=>["type"=>"method_not_allowed"]]);
        $response_code = 405;
    }
    // header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=utf-8", true, $response_code);
    echo $response;
}else{
    header("HTTP/1.1 404 Not Found");
    exit;
}