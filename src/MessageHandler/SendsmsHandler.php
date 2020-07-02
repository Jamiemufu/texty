<?php

namespace App\MessageHandler;

use Twilio\Rest\Client;
use App\Message\Sendsms;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class SendsmsHandler implements MessageHandlerInterface

    /*
     * Change the status callback to your reachable domain - using ngrok for testing and local env
     * This can be changed in the .env
     * Run: php bin/console messenger:consume async to consume the queue and start a worker
     */
{      
    private $container;

    private $params;

    /**
     * Get container service
     */
    public function __construct(Container $container, ParameterBagInterface $params)
    {
        $this->container = $container;
        $this->params = $params;
    }

    /**
     * Send a sms to queue
     */
    public function __invoke(Sendsms $message)
    {   
        $twilio = $this->container->get('twilio.client');

        $twilio->messages->create($message->getPhoneNumber(), [
            'from' => $message->getmyNumber(),
            'body' => $message->getBody(),
            'statusCallback' => "{$this->params->get('twilio_callback_uri')}message/status/{$message->getMessageID()}",
        ]);

        /*
         * I would manange exceptions and errors here with a response
         * With a trial account though, I can't access any errors or add more phone numbers
         */
    }
    
}
