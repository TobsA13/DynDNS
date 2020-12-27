<?php

require_once("config.php");
require_once("record.class.php");

function result($code, $status, $ip = ""){
    http_response_code($code);
    if($ip != ""){
        $status = $status ." ". $ip;
    }
    exit($status);
}

function checkAuth($host, $auth){
    $key = hash_hmac("sha256", $host . ZONE_ID, HMAC_KEY);
    return hash_equals($key, $auth);
}

function do_getIp($host){
    $record = new Record(API_KEY, ZONE_ID, $host);
    if(!$record->exist()){
        result(400, "nohost");
    }
    echo $record->getIp();
}

function do_setIp($host, $ip){
    $record = new Record(API_KEY, ZONE_ID, $host);
    if(!$record->exist()){
        result(400, "nohost");
    }
    if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
        result(400, "badsys not an IPv4", $ip);
    }

    if($record->getIp() == $ip){
        result(200, "nochg", $ip);
    }
    $record->setIp($ip);
    result(200, "good", $ip);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(!isset($_GET["host"])){
        result(400, "badsys");
    }
    $host = $_GET["host"];

    if(!isset($_GET['auth']) || !checkAuth($host, $_GET['auth'])){
        result(401, "badauth");
    }

    if(isset($_GET["type"]) && $_GET["type"] === "get"){
        do_getIp($host);
    } else {
        if(isset($_GET["ip"])){
            $ip = $_GET["ip"];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        do_setIp($host, $ip);
    }


} else {
    http_response_code(405);
    return;
}