#!/usr/bin/env php

<?php
set_time_limit(0);

require __DIR__ . "/../vendor/autoload.php";

use Socket\Daemon\Daemon;
use Socket\Logger\FileToLogger;
use Socket\TCPSocket\CreateTCPSocket;
use Socket\BracketFactory;
use Socket\TCPSocket\ConnectTCPSocket;
use Socket\Options\ConfigFileOptions;

try {
    $logger = new FileToLogger("./bracket-daemon.log");

    $options = new ConfigFileOptions("./configFile.yaml");
    $options = $options->getOptions();

    $createTCPSocket = new CreateTCPSocket("localhost", $options, $logger);
    $createTCPSocket->open();

    $bracketFactory = new BracketFactory();
    $connectTCPSocket = new ConnectTCPSocket($createTCPSocket, $bracketFactory, $logger);

    $createToSeveralProcess = new Daemon(5, $connectTCPSocket, $logger);
    $createToSeveralProcess->run();

} catch (\Exception $e) {
    $logger->log($e->getMessage());
}