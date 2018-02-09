<?php

namespace Socket\TCPSocket;

use Socket\BracketFactory;
use Socket\Exceptions\EmptyContentException;
use Socket\Interfaces\LoggerInterface;

class ConnectTCPSocket
{
    /**
     * @var CreateTCPSocket
     */
    private $socket;

    /**
     * @var BracketFactory
     */
    private $bracketFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $spawn;

    /**
     * ConnectTCPSocket constructor.
     *
     * @param CreateTCPSocket $socket
     * @param BracketFactory $bracketFactory
     * @param LoggerInterface $logger
     */
    public function __construct(CreateTCPSocket $socket, BracketFactory $bracketFactory, LoggerInterface $logger)
    {
        $this->socket = $socket;
        $this->bracketFactory = $bracketFactory;
        $this->logger = $logger;
    }

    /**
     * @throws EmptyContentException
     */
    public function run()
    {
        if (empty($this->socket))
            throw new EmptyContentException("Don't create socket");

        $this->spawn = socket_accept($this->socket->getSocket());
        $this->logger->log("Connection request received");

        $welcome = "\nConnection open. \n";
        $welcome .= "You can send sequence of brackets for validation.\n\n";
        $welcome .= "To close the session, enter command: exit\n\n";
        socket_write($this->spawn, $welcome, strlen($welcome));

        $this->communication();

        socket_close($this->spawn);
        $this->logger->log("Connection closed");
    }

    protected function communication()
    {
        do {
            $input = socket_read($this->spawn, 2048, PHP_BINARY_READ);
            $input = trim($input);

            if ($input != "") {
                $this->logger->log("User input string: $input");

                if ($input == "exit") {
                    break;
                }

                try {
                    $bkt = $this->bracketFactory->getBracketService($input);
                    $output = $bkt->check() ? "OK" : "Mistake.";
                } catch (\Exception $e) {
                    $output = "ERROR! " . $e->getMessage();
                }

                socket_write($this->spawn, $output . "\n", strlen($output) + 2);
                $this->logger->log("Server response: $output");
            }

        } while (true);
    }

}