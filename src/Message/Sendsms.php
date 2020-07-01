<?php

namespace App\Message;

class Sendsms
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */
    
    private $phoneNumber;

    private $myNumber;

    private $body;

    private $messageID;

    public function __construct(int $messageID, string $body, string $myNumber, string $phoneNumber)
    {
        $this->messageID = $messageID;
        $this->body = $body;
        $this->myNumber = $myNumber;
        $this->phoneNumber = $phoneNumber;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber()
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getmyNumber()
    {
        return $this->myNumber;
    }

    public function setmyNumber()
    {
        $this->myNumber = $myNumber;
    }

    public function getBody()
    {
        return $this->body;;
    }

    public function setBody()
    {
        $this->body = $body;
    }

    public function getMessageID()
    {
        return $this->messageID;
    }

    public function setMessageID(int $messageID): self
    {
        $this->messageID = $messageID;
    }

}
