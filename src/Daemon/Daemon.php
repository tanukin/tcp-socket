<?php

namespace Socket\Daemon;

use Socket\Exceptions\ContentException;
use Socket\Exceptions\EmptyContentException;
use Socket\Exceptions\HelpContentException;
use Socket\Exceptions\InvalidArgumentContentException;
use Socket\Interfaces\DaemonInterface;
use Socket\Interfaces\LoggerInterface;
use Socket\Options\ConfigFileOptions;
use Socket\TCPSocket\ConnectTCPSocket;

class Daemon implements DaemonInterface
{
    /**
     * @var int
     */
    private $countProcess;

    /**
     * @var ConnectTCPSocket
     */
    private $connectTCPSocket;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $DaemonRun = true;

    /**
     * @var array
     */
    private $childProcesses = [];

    /**
     * @var string
     */
    private $pidFile = "/tmp/bracket_daemon_pid.tmp";

    /**
     * @var int
     */
    private $sleep = 5;

    /**
     * Daemon constructor.
     *
     * @param int $countProcess
     * @param ConnectTCPSocket $connectTCPSocket
     * @param LoggerInterface $logger
     */
    public function __construct(int $countProcess, ConnectTCPSocket $connectTCPSocket, LoggerInterface $logger)
    {
        $this->countProcess = $countProcess;
        $this->connectTCPSocket = $connectTCPSocket;
        $this->logger = $logger;
    }

    public function run()
    {
        if ($this->isDaemonRun($this->pidFile)) {
            $this->logger->log("Daemon already run");
            exit;
        }

        $pid = pcntl_fork();

        if ($pid) {
            exit;
        }

        if ($pid == 0) {

            $sid = posix_setsid();
            file_put_contents($this->pidFile, posix_getpid());
            if ($sid < 0) {
                $this->logger->log("ERROR");
                exit;
            }

            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);

            pcntl_signal(SIGTERM, array($this, "signalHandler"));
            pcntl_signal(SIGHUP, array($this, "signalHandler"));

            $this->connectTCPSocket->createTCPSocket();

            while ($this->DaemonRun) {
                if ($this->DaemonRun && (count($this->childProcesses) < $this->countProcess)) {

                    $pid = pcntl_fork();

                    if ($pid) {

                        $this->childProcesses[$pid] = true;
                        cli_set_process_title("bracket-parent");
                    }

                    if ($pid == 0) {

                        $pid = posix_getpid();
                        cli_set_process_title("bracket-child");
                        $this->logger->log("Child run. pid = $pid");
                        try {
                            $this->connectTCPSocket->communication();
                        } catch (ContentException $e) {
                            $this->logger->log($e->getMessage());
                            exit($e->getCode());
                        }
                        exit;
                    }

                } else {
                    sleep($this->sleep);
                }

                pcntl_signal_dispatch();

                while ($signaled_pid = pcntl_waitpid(-1, $status, WNOHANG)) {
                    if ($signaled_pid != -1) {

                        $pid = posix_getpid();
                        $this->logger->log("Child closed. pid = $pid");
                        unset($this->childProcesses[$signaled_pid]);
                    } else {
                        $this->childProcesses = [];
                    }
                }
            }
        }
    }

    public function signalHandler($signo)
    {
        switch ($signo) {
            case SIGTERM:
                $this->logger->log("SIGTERM");
                $this->DaemonRun = false;
                $this->closeChildProses();
                break;
            case SIGHUP:
                $this->logger->log("SIGHUP");

                if (!$this->hasChangePort())
                    break;

                $this->connectTCPSocket->closeTCPSocket();
                $this->closeChildProses();
                $this->connectTCPSocket->createTCPSocket();
        }
    }

    protected function isDaemonRun($pid_file)
    {
        if (!is_file($pid_file))
            return false;

        $pid = file_get_contents($pid_file);

        if (posix_kill($pid, 0))
            return true;

        if (!unlink($pid_file)) {
            $this->logger->log("Can't delete file $pid_file");
            exit(-1);
        }

        return false;
    }

    /**
     * @return bool
     */
    private function hasChangePort(): bool
    {
        return $this->connectTCPSocket->getSocket()->getPort() != $this->connectTCPSocket->getSocket()->getNewPort();
    }


    private function closeChildProses(): void
    {
        foreach ($this->childProcesses as $process => $val){
            posix_kill($process, SIGKILL);
            unset($this->childProcesses[$process]);
        }
    }
}

