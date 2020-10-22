<?php

namespace Rabbitmq\Subscribe;

use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

abstract class BaseSubscribe
{
    protected $message = null;

    protected $eventNmae = '';

    public function handle(AMQPMessage $message)
    {
        $this->message = $message;
        $this->eventNmae = $message->delivery_info['exchange'];
        Log::info(sprintf("%s content:%s", $this->eventNmae, json_encode($message)));

        $result = $this->callback(json_decode($message->body, true));

        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

        return $result;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getEventName()
    {
        return $this->eventNmae;
    }

    /**
     * 用于具体事件实现
     */
    abstract public function callback(Array $data);
}
