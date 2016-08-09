<?php

namespace Amqp\Silex\Provider;

use Silex\Application;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class AmqpServiceProvider implements ServiceProviderInterface
{
    const AMQP = 'amqp';
    const AMQP_CONNECTIONS = 'amqp.connections';
    const AMQP_FACTORY = 'amqp.factory';

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Container $pimple)
    {
        $pimple[self::AMQP_CONNECTIONS] = array(
            'default' => array(
                'host' => 'localhost',
                'port' => 5672,
                'username' => 'guest',
                'password' => 'guest',
                'vhost' => '/'
            )
        );

        $pimple[self::AMQP_FACTORY] = function ($host = 'localhost', $port = 5672, $username = 'guest', $password = 'guest', $vhost = '/') use ($pimple) {
            return $pimple[self::AMQP]->createConnection($host, $port, $username, $password, $vhost);
        };

        $pimple[self::AMQP] = function () use ($pimple) {
            return new AmqpConnectionProvider($pimple[self::AMQP_CONNECTIONS]);
        };
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registers
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {}
}
