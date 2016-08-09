<?php

namespace Amqp\Silex\Helper;

use Amqp\Silex\Provider\AmqpConnectionProvider;
use Symfony\Component\Console\Helper\Helper;

class ConnectionHelper extends Helper
{
    protected $_cp;

    /**
     * Constructor.
     *
     * @param AmqpConnectionProvider $cp
     */
    public function __construct(AmqpConnectionProvider $cp)
    {
        $this->_cp = $cp;
    }

    public function getConnectionProvider()
    {
        return $this->_cp;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'connectionProvider';
    }
}
