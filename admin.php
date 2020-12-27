<?php

require_once("config.php");
require_once("record.class.php");

function result($code, $status){
    http_response_code($code);
    exit($status);
}
function checkAuth($authData){
    return hash_equals(ADMIN_KEY, $authData);
}

function do_create($host) {
    $record = new Record(API_KEY, ZONE_ID, $host);
    if(!$record->exist()){
        $record->create();
        http_response_code(201);
    } else {
        http_response_code(200);
    }

    $key = hash_hmac("sha256", $host . ZONE_ID, HMAC_KEY);
    echo $key;
}

function do_exist($host){
    $record = new Record(API_KEY, ZONE_ID, $host);
    echo $record->exist() ? "true" : "false";
}

function do_delete($host){
    $record = new Record(API_KEY, ZONE_ID, $host);
    if(!$record->exist()){
        result(404, "host does not exist");
    }

    $record->delete();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!isset($_SERVER['HTTP_AUTH']) || !checkAuth($_SERVER['HTTP_AUTH'])){
        result(401, "failed auth");
    }
    if(!isset($_POST["type"]) || !isset($_POST["host"])){
        result(400, "missing parameters");
    }
    switch($_POST["type"]){
        case "exist": do_exist($_POST["host"]); break;
        case "create": do_create($_POST["host"]); break;
        case "delete": do_delete($_POST["host"]); break;
    }
} else {
    result(406, "wrong request method");
}