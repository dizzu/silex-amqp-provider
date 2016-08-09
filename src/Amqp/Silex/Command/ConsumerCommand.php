<?php

namespace Amqp\Silex\Command;

use Knp\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumerCommand extends BaseCommand
{
    public $timeout = 600;
    public $refresh = 10;

    protected function configure()
    {
        $this
            ->setName('rabbitmq:consumer')
            ->setDescription('Execute a rabbitmq consumer.')
            ->addArgument('name', InputArgument::REQUIRED, 'Consumer Name')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Enable Debugging')
            ->addOption('connection', 'c', InputOption::VALUE_OPTIONAL, 'Specify queue', 'default')
        ;
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     *
     * @throws \InvalidArgumentException When the number of messages to consume is less than 0
     * @throws \BadFunctionCallException When the pcntl is not installed and option -s is true
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (defined('AMQP_DEBUG') === false) {
            define('AMQP_DEBUG', (bool) $input->getOption('debug'));
        }

        $this->consumer = $this->getConsumerInstance($input);
        $this->connection = $this->consumer[$input->getOption('connection')];
        $this->endTime = $endTime = time() + $this->timeout;
        $this->channel = $this->connection->channel();

        $callback = function ($msg) {
            $this->refreshConsumer();
            echo " [x] Received ", $msg->body, "\n";
        };

        $this->channel->basic_consume($input->getArgument('name'), '', false, true, false, false, $callback);

        while (count($this->channel->callbacks)) {
            $this->refreshConsumer();
            $this->wait($this->channel, null, true, $this->refresh);
        }
    }

    private function refreshConsumer()
    {
        if (time()>$this->endTime) {
            $this->channel->close();
            $this->connection->close();
            die();
        }
    }

    private function wait($channel, $allowed_methods = null, $non_blocking = false, $timeout = 0)
    {
        try {
           $this->refreshConsumer();
            $channel->wait($allowed_methods, $non_blocking, $timeout);
        }
        catch (\Exception $e) {
            $this->wait($channel, $allowed_methods, $non_blocking, $timeout);
        }
    }

    protected function getConsumerInstance($input)
    {
        $app = $this->getSilexApplication();
        return $app->getHelperSet()->get('cp')->getConnectionProvider();
    }
}
