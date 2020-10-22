<?php

namespace Rabbitmq;

use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class Publisher
{
    public function pushlish(string $eventName, string $message)
    {
        $channel = $this->getChannel();
        $channel->exchange_declare($eventName, AMQPExchangeType::FANOUT, false, false, true);
        $message = new AMQPMessage(json_encode($message), array('content_type' => 'text/plain'));

        $channel->basic_publish($message, $eventName);
    }

    public function getChannel()
    {
        return app()->get('rabbitmq_channel');
    }
}
