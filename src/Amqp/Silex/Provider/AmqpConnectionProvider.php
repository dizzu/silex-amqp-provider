<?php

namespace Amqp\Silex\Provider;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class AmqpConnectionProvider extends \Pimple\Container
{
    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $provider = $this;
        foreach ($options as $key => $connection) {
            $this['default'] = function () use ($connection, $provider) {
                return $provider->createConnection($connection['host'], $connection['port'], $connection['username'], $connection['password'], $connection['vhost']);
            };
        }
    }

    /**
     * @param  string          $host
     * @param  integer         $port
     * @param  string          $username
     * @param  string          $password
     * @param  string          $vhost
     * @return \AMQPConnection
     */
    public function createConnection($host = 'localhost', $port = 5672, $username = 'guest', $password = 'guest', $vhost = '/')
    {
        return new AMQPStreamConnection($host, $port, $username, $password, $vhost);
    }
}
