# Lravel Rabbit fanout MQ

一个基于rabbitmq的fanout exchange实现的laravel的消息队列(pub/sub)

## 发布配置
```php
php artisan vendor:publish --provider="Rabbitmq\RabbitMqProvider"
```

## 消费实现
[参考实践 Exchange Per Event](https://derickbailey.com/2015/09/02/rabbitmq-best-practices-for-designing-exchanges-queues-and-bindings/) 

配置：config/rabbitmq.php
```php
// eventName => subscribe
'subscribe_exchange' => [
    'user.created' => \Rabbitmq\Subscribe\UserCreated::class,
]
```

eventName本质就是一个fanout的exchange

## 监听事件
```php
php artisan rabbitmq:consume 
```

## 发布消息
```php
$created_user = [
    'id' => 100,
    'name' => 'zhangsan'
];

\Rabbitmq\Facades\Publisher::pushlish('user.created', json_encode($created_user))

```





