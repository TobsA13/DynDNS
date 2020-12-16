<?php

const TTL = 60;

class Record {
    var $zone;

    var $client;
    var $zoneId;
    var $recordId;

    function __construct($apiKey, $zone, $name){
        $this->zone = $zone;
        $this->name = $name

        $this->client = new HetznerDnsClient($apiKey);
        $this->zoneId = $this->client->getZoneByName($this->zone)->getZones()[0]->getId();

        $records = $this->client->getAllRecords($this->zoneId);

        $this->recordId = false;
        foreach ($records as $record){
            if($record->getId() == $this->name){
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
            throw new RuntimeException("Record does already exist.")
        }
        $response = $client->createRecord(
            (new Record())
                ->zoneId($this->zoneId)
                ->ttl(TTL)
                ->type(Record::TYPE_A)
                ->value('127.0.0.1')
                ->name($name . $posfix)
        );
        $this->recordId = $response->getRecord()->getId();
    }

    function deleteRecord(){
        if(!$this->exist()){
            throw new RuntimeException("Record does not exist.")
        }
        $this->client->deleteRecord($this->recordId);
        $this->recordId = false;
    }

    function getIp(){
        if(!$this->exist()){
            throw new RuntimeException("Record does not exist.")
        }
        
        return $this->client->getRecord($this->recordId)->getValue();
    }

    function setIp($ip){
        if(!$this->exist()){
            throw new RuntimeException("Record does not exist.")
        }
        
        $record =  $this->client->getRecord($this->recordId);
        $record->setValue($ip)
        $this->client->updateRecord($this->recordId, $record);
    }
}
