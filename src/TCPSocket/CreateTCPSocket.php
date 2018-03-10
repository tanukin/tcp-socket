<?php

namespace Socket\TCPSocket;

use Socket\Exceptions\ContentException;
use Socket\Exceptions\EmptyContentException;
use Socket\Exceptions\HelpContentException;
use Socket\Exceptions\InvalidArgumentContentException;
use Socket\Interfaces\LoggerInterface;
use Socket\Interfaces\OptionsInterface;

class CreateTCPSocket
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var OptionsInterface
     */
    private $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $port;

    /**
     * @var resource
     */
    private $socket;

    /**
     * CreateTCPSocket constructor.
     *
     * @param string $host
     * @param OptionsInterface $options
     * @param LoggerInterface $logger
     */
    public function __construct(string $host, OptionsInterface $options, LoggerInterface $logger)
    {
        $this->host = $host;
        $this->options = $options;
        $this->logger = $logger;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    public function open()
    {
        $this->port = $this->getNewPort();

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->host, $this->port);
        socket_listen($this->socket);
        $this->logger->log("Waiting to connect (port = $this->port) ...");
    }


    public function getNewPort(): int
    {
        try {
            $port = $this->parseOptions($this->readOptions());
        } catch (ContentException $e) {
            $this->logger->log($e->getMessage());
            die();
        }
        return $port;
    }
    /**
     * @return array
     */
    private function readOptions(): array
    {
        return $this->options->getOptions();
    }

    /**
     * @param array $options
     *
     * @return int
     *
     * @throws EmptyContentException
     * @throws HelpContentException
     * @throws InvalidArgumentContentException
     */
    private function parseOptions(array $options): int
    {
        if (empty($options))
            throw new EmptyContentException("Port number not received. \nFor help, specify the flag: -h, --help");


        switch (array_keys($options)[0]) {
            case "p":
                $flag = "p";
                break;
            case "port":
                $flag = "port";
                break;
            case "h":
            case "help":
                $flag = "help";
                break;
            default:
                throw new InvalidArgumentContentException("Invalid flag.");
        }

        if ($flag == "help")
            throw new HelpContentException(<<<HTML
    Use: bracket.php [key]=[value]
    
After connecting to the server, you must pass a string containing the sequence of parentheses for verification.
After accepting the line, the server check it and send answer, then it is ready to accept a new line for verification.

To close the session, enter command: exit

Flags.                  
    -p=port_number,
    --port=port_number      set the TCP port number on which 
                            the server will start accepting the connection
                            
    -h, --help              show this help

HTML
            );

        $port = (int)$options[$flag];

        if ($port == 0)
            throw new EmptyContentException("Invalid flag or port.");

        return $port;
    }

    public function close()
    {
        if (!empty($this->socket)) {
            socket_close($this->socket);
            $this->logger->log("Connection closed (port = $this->port)");
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}