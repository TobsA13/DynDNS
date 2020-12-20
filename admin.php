<?php

require_once("config.php");
require_once("record.class.php");

function checkAuth($authData){
    return hash_equals(ADMIN_KEY, $authData);
}

function do_create($host) {
    $record = new Record(API_KEY, ZONE_ID, $host);
    if($record->exist()){
        http_response_code(400);
        echo "host does already exist";
    }

    $record->create();
    
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
        http_response_code(400);
        echo "host does not exist";
    }

    $record->delete();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!isset($_SERVER['HTTP_AUTH']) || !checkAuth($_SERVER['HTTP_AUTH'])){
        http_response_code(401);
        return;
    }
    if(!isset($_POST["type"]) || !isset($_POST["host"])){
        http_response_code(400);
        return;
    }
    switch($_POST["type"]){
        case "exist": do_exist($_POST["host"]); break;
        case "create": do_create($_POST["host"]); break;
        case "delete": do_delete($_POST["host"]); break;
    }
} else {
    http_response_code(405);
    return;
}