<?php

namespace Rabbitmq;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;
use Rabbitmq\Console\ConsumerCommand;
use Illuminate\Support\Arr;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMqProvider extends ServiceProvider
{
    public function boot()
    {
        $source = realpath(__DIR__ . '/../config/rabbitmq.php');
        
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('rabbitmq.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure($source, 'rabbitmq');
        }

        $this->mergeConfigFrom($source, 'rabbitmq');

        // 注册命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                ConsumerCommand::class
            ]);
        }
    }

    public function register()
    {
        $this->app->singleton('rabbitmq_connection', function($app) {
            $config = $app->make('config')->get('rabbitmq');
            $hosts = $config['hosts'];
            if (! Arr::has($hosts, ['host', 'port', 'user', 'password', 'vhost'])) {
                throw new \Exception("缺少rabbitmq连接参数");
            }

            $connection = new AMQPStreamConnection(
                $hosts['host'],
                $hosts['port'],
                $hosts['user'],
                $hosts['password'],
                $hosts['vhost']
            );

            return $connection;
        });

        $this->app->bind('rabbitmq_channel', function($app) {
            return $app->get('rabbitmq_connection')->channel();
        });

        $this->app->singleton('rabbitmq_publisher', function($app) {
            return new Publisher();
        });
    }
}
