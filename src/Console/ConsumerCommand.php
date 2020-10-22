<?php

namespace Rabbitmq\Console;

use Illuminate\Console\Command;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumerCommand extends Command
{
    protected $signature = "rabbitmq:consume";

    protected $description = "启动RabbitMq监听服务作为消费者";

    protected $comsumer_collect = [];

    public function handle()
    {
        $channel = $this->laravel->get('rabbitmq_channel');
        $subscribes = $this->getSubscribes();

        if (empty($subscribes)) {
            $this->error("不存在需要监听的消费者");
            exit(0);
        }

        // 定义一个随机queue名，避免queue相同（否则变成work queue， 将不是pub/sub）
        list($queue, ) = $channel->queue_declare("", false, false, true, false);
        $consumer_tag = 'consumer' . getmypid();

        $this->comment("开始监听等待消息...\n");
        foreach (array_keys($subscribes) as $exchange) {
            $channel->exchange_declare($exchange, AMQPExchangeType::FANOUT, false, false, true);
            $channel->queue_bind($queue, $exchange);
        }

        $channel->basic_consume($queue, $consumer_tag, false, false, false, false, [$this, 'processMessage']);

        register_shutdown_function(function() use ($channel){
            $channel->close();
            app('rabbitmq_connection')->close();
        });

        while ($channel->is_consuming()) {
            $channel->wait();
        }
    }

    public function processMessage(AMQPMessage $message)
    {
        $subscribes = $this->getSubscribes();

        $target_exchange = $message->delivery_info['exchange'] ?? '';
        if (empty($target_exchange)) {
            return ;
        }

        if (! isset($subscribes[$target_exchange])) {
            return ;
        }

        try {
            $this->getConsumer($target_exchange)->handle($message);
        } catch(\Exception $e) {
            $message = sprintf("rabbitMq处理消息出错: exchange: %s, exception_mesage: %s", $target_exchange, $e->getMessage());
            app('log')->info($message);
        }
    }

    protected function getConsumer($exchange)
    {        
        if (! isset($this->comsumer_collect[$exchange])) {
            $subscribes = $this->getSubscribes();
            $subscribe = $subscribes[$exchange];

            $consumer = new $subscribe();
            $this->consumer_collect[$exchange] = $consumer;
        }

        return $this->consumer_collect[$exchange]; 
    }

    protected function getSubscribes()
    {
        return $this->laravel['config']->get('rabbitmq.subscribes');
    }
}