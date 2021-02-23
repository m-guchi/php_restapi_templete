<?php

include(__DIR__ . "/DB.php");

function htmlescape($val){
    return htmlspecialchars($val);
}

preg_match('|'.dirname($_SERVER["SCRIPT_NAME"]).'/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$paths = explode('/',$matches[1]);
$file = array_shift($paths);
$params = array_map("htmlescape",$paths);

$file_path = './controllers/'.$file.'.php';
if(file_exists($file_path)){
    include($file_path);
    $class_name = ucfirst($file)."Controller";
    $method_name = strtolower($_SERVER["REQUEST_METHOD"]);
    $object = new $class_name();
    $response = json_encode($object->$method_name(...$params));
    $response_code = $object->code ?? 200;

    header("Content-Type: application/json; charset=utf-8", true, $response_code);
    echo $response;
}else{
    header("HTTP/1.1 404 Not Found");
    exit;
}