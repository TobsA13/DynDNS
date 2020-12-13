<?php

require_once "config.php";

function result($code, $status, $ip = ""){
    http_response_code($code);
    if($ip != ""){
        $status = $status ." ". $ip;
    }
    exit($status);
}

if(!isset($_GET["id"]) || !is_numeric($_GET['id']) || !isset($_GET["token"])){
    result(400, "badsys");
}

$id = $_GET["id"];
$token = addslashes($_GET["token"]);
$ip = $_SERVER['REMOTE_ADDR'];
if(!array_key_exists($id, $CONFIG)){
    result(400, "nohost");
}
$conf = $CONFIG[$id];
if($conf["token"] !== $token) {
    result(401, "badauth");
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://dns.hetzner.com/api/v1/records/' . $conf["record"]);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Auth-API-Token: ' . $API_KEY,
]);
$response = curl_exec($ch);
if (!$response) {
    result(500, "dnserr" . ' Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}
$current_record = json_decode($response);
curl_close($ch);
if($current_record->record->value == $ip){
    result(200, "nochg", $ip);
}



$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://dns.hetzner.com/api/v1/records/' . $conf["record"]);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Auth-API-Token: ' . $API_KEY,
]);

$json_array = [
    'value' => $ip,
    'ttl' => $TTL,
    'type' => 'A',
    'name' => $conf["name"],
    'zone_id' => $current_record->record->zone_id
];
$body = json_encode($json_array);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
$response = curl_exec($ch);

// stop if fails
if (!$response) {
    result(500, "dnserr" . ' Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}
curl_close($ch);
result(200, "good", $ip);