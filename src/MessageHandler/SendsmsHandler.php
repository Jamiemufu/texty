<?php

namespace App\MessageHandler;

use Twilio\Rest\Client;
use App\Message\Sendsms;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class SendsmsHandler implements MessageHandlerInterface
{      
    private $container;
    /**
     * Get container service
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
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
            'statusCallback' => "http://d9b4a9caf4f5.ngrok.io/message/{$message->getMessageID()}/status",
        ]);
    }

    // run: php bin/console messenger:consume async to consume the queue and start a worker
}
