<?php

namespace Socket\Daemon;

use Socket\Exceptions\ContentException;
use Socket\Interfaces\DaemonInterface;
use Socket\Interfaces\LoggerInterface;
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
    private $DaemonRun;

    /**
     * @var array
     */
    private $countChildProcesses = [];

    /**
     * @var string
     */
    private $pidFile;

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
        $this->DaemonRun = true;
        $this->pidFile = "/tmp/bracket_daemon_pid.tmp";
    }

    public function run()
    {
        declare(ticks=1);

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

            if ($sid < 0) {
                $this->logger->log("ERROR");
                exit;
            }

            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);

            while ($this->DaemonRun) {
                if ($this->DaemonRun and (count($this->countChildProcesses) < $this->countProcess)) {

                    $pid = pcntl_fork();

                    if ($pid) {

                        $this->countChildProcesses[$pid] = true;
                        file_put_contents($this->pidFile, posix_getpid());
                    }

                    if ($pid == 0) {

                        $pid = posix_getpid();
                        $this->logger->log("Child run. pid = $pid");
                        try {
                            $this->connectTCPSocket->run();
                        } catch (ContentException $e) {
                            $this->logger->log($e->getMessage());
                            exit($e->getCode());
                        }
                        exit;
                    }

                } else {
                    sleep(10);
                }

                while ($signaled_pid = pcntl_waitpid(-1, $status, WNOHANG)) {
                    if ($signaled_pid != -1) {

                        $pid = posix_getpid();
                        $this->logger->log("Child closed. pid = $pid");
                        unset($this->countChildProcesses[$signaled_pid]);
                    } else {
                        $this->countChildProcesses = [];
                    }
                }

                pcntl_signal(SIGTERM, array($this, "signalHandler"));
                pcntl_signal(SIGHUP, array($this, "signalHandler"));

                pcntl_signal_dispatch();

            }
        }
    }

    public function signalHandler($signo)
    {
        switch ($signo) {
            case SIGTERM:
                {
                    $this->logger->log("SIGTERM");
                    $this->DaemonRun = false;

                    break;
                }
            case SIGHUP:
                {
                    $this->logger->log("SIGHUP");
                    break;
                }
        }
    }

    protected function isDaemonRun($pid_file)
    {
        if (is_file($pid_file)) {
            $pid = file_get_contents($pid_file);

            if (posix_kill($pid, 0)) {
                return true;
            } else {
                if (!unlink($pid_file)) {
                    $this->logger->log("Can't delete file $pid_file");
                    exit(-1);
                }
            }

        }

        return false;
    }
}




