<?php

require __DIR__ . '/vendor/autoload.php';

defined('TTL') or define('TTL', 60);

use MehrIt\HetznerDnsApi\HetznerDnsClient;

class Record {
    var $zone;

    var $client;
    var $zoneId;
    var $recordId;

    function __construct($apiKey, $zoneId, $name){
        $this->name = $name;
        $this->zoneId = $zoneId;

        $this->client = new HetznerDnsClient($apiKey);

        $records = $this->client->getAllRecords($this->zoneId);

        $this->recordId = false;
        foreach ($records->getRecords() as $record){
            if($record->getName() == $this->name){
                $this->recordId = $record->getId();
                break;
            }
        }
    }     

    function exist(){
        return $this->recordId !== false;
    } 

    function create(){
        if($this->exist()){
            throw new RuntimeException("Record does already exist.");
        }
        $response = $this->client->createRecord(
            (new MehrIt\HetznerDnsApi\Models\Record())
                ->zoneId($this->zoneId)
                ->ttl(TTL)
                ->type(MehrIt\HetznerDnsApi\Models\Record::TYPE_A)
                ->value('127.0.0.1')
                ->name($this->name)
        );
        $this->recordId = $response->getRecord()->getId();
    }

    function delete(){
        if(!$this->exist()){;
            throw new RuntimeException("Record does not exist.");
        }
        $this->client->deleteRecord($this->recordId);
        $this->recordId = false;
    }

    function getIp(){
        if(!$this->exist()){
            throw new RuntimeException("Record does not exist.");
        }
        
        return $this->client->getRecord($this->recordId)->getRecord()->getValue();
    }

    function setIp($ip){
        if(!$this->exist()){
            throw new RuntimeException("Record does not exist.");
        }
        
        $record = (new MehrIt\HetznerDnsApi\Models\Record())
                ->zoneId($this->zoneId)
                ->ttl(TTL)
                ->type(MehrIt\HetznerDnsApi\Models\Record::TYPE_A)
                ->value($ip)
                ->name($this->name);
        $this->client->updateRecord($this->recordId, $record);
    }
}
