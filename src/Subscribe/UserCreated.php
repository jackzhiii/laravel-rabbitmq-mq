<?php

namespace Rabbitmq\Subscribe;

use PhpAmqpLib\Message\AMQPMessage;

class UserCreated extends BaseSubscribe
{
    public function callback($data)
    {
        echo "\n---111-----\n";
        echo "exchange:" . $this->getEventName() . "\n";
        // echo $data;
        var_dump($data);
        echo "\n---111-----\n";
    }
}