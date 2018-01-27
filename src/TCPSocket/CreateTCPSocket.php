<?php

namespace Socket\TCPSocket;

use Socket\Exceptions\ContentException;
use Socket\Exceptions\EmptyContentException;
use Socket\Exceptions\HelpContentException;
use Socket\Exceptions\InvalidArgumentContentException;
use Socket\Logger\LoggerInterface;

class CreateTCPSocket
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var array
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
    private $socket;

    /**
     * CreateTCPSocket constructor.
     *
     * @param string $host
     * @param array $options
     * @param LoggerInterface $logger
     */
    public function __construct(string $host, array $options, LoggerInterface $logger)
    {
        $this->host = $host;
        $this->options = $options;
        $this->logger = $logger;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function open()
    {
        try {
            $this->parseOptions();
        } catch (ContentException $e) {
            $this->logger->log($e->getMessage());
            die();
        }

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->host, $this->port);
        socket_listen($this->socket, 3);
        $this->logger->log("Waiting to connect (port = $this->port) ...");
    }

    public function close()
    {
        if (!empty($this->socket)) {
            socket_close($this->socket);
            $this->logger->log("Connection closed (port = $this->port)");
        }
    }

    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @throws EmptyContentException
     * @throws HelpContentException
     * @throws InvalidArgumentContentException
     */
    protected function parseOptions()
    {
        if (empty($this->options))
            throw new EmptyContentException("Port number not received. \nFor help, specify the flag: -h, --help");


        switch (array_keys($this->options)[0]) {
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

        $port = (int)$this->options[$flag];

        if ($port == 0)
            throw new EmptyContentException("Invalid flag or port.");

        $this->port = $port;
    }
}