#!/usr/bin/env php

<?php
set_time_limit(0);

require __DIR__ . "/../vendor/autoload.php";

use Socket\Daemon\Daemon;
use Socket\Daemon\DaemonStartupMode;
use Socket\Logger\FileToLogger;
use Socket\Options\FlagOptions;
use Socket\TCPSocket\CreateTCPSocket;
use Socket\BracketFactory;
use Socket\TCPSocket\ConnectTCPSocket;
use Socket\Options\ConfigFileOptions;

try {
    $mode = new FlagOptions();
    $logger = new FileToLogger(__DIR__ . "/../bracket-daemon.log");
    $options = new ConfigFileOptions(__DIR__ . "/../src/configFile.yaml");
    $createTCPSocket = new CreateTCPSocket("localhost", $options, $logger);
    $bracketFactory = new BracketFactory();
    $connectTCPSocket = new ConnectTCPSocket($createTCPSocket, $bracketFactory, $logger);

    $daemonStartupMode = new DaemonStartupMode($mode);
    $createToSeveralProcess = new Daemon(2, $daemonStartupMode->getMode(), $connectTCPSocket, $logger);
    $createToSeveralProcess->run();

} catch (\Exception $e) {
    $logger->log($e->getMessage());
}