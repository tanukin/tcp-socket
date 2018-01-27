<?php

namespace Socket\TCPSocket;

use Socket\Exceptions\ContentException;
use Socket\Logger\LoggerInterface;

class CreateToSeveralProcess
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var ConnectTCPSocket
     */
    private $connectTCPSocket;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CreateToSeveralProcess constructor.
     *
     * @param int $countProcess
     * @param ConnectTCPSocket $connectTCPSocket
     * @param LoggerInterface $logger
     */
    public function __construct(int $countProcess = 1, ConnectTCPSocket $connectTCPSocket, LoggerInterface $logger)
    {
        $this->count = $countProcess;
        $this->connectTCPSocket = $connectTCPSocket;
        $this->logger = $logger;

    }

    public function run()
    {
        for ($i = 0; $i < $this->count; $i++) {

            $pid = pcntl_fork();

            if ($pid > 0) {
                $parentPid = posix_getpid();
                $this->logger->log("Parent (pid = $parentPid)");
            }

            if ($pid == 0) {
                $childPid = posix_getpid();
                $this->logger->log("Child (pid = $childPid)");

                try {
                    $this->connectTCPSocket->run();
                } catch (ContentException $e) {
                    $this->logger->log($e->getMessage());
                    exit($e->getCode());
                }

                exit(0);
            }
        }

        pcntl_signal(SIGINT, function () {
            exit;
        });

        while (pcntl_waitpid(0, $status) != -1) {
            $childPid = posix_getpid();
            $status = pcntl_wexitstatus($status);
            $this->logger->log("Child $childPid closed. Child return status code = $status");
        }

        pcntl_signal_dispatch();
    }

}