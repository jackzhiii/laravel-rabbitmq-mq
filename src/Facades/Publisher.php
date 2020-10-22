<?php

namespace Rabbitmq\Facades;

use Illuminate\Support\Facades\Facade;

class Publisher extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rabbitmq_publisher';
    }
}
