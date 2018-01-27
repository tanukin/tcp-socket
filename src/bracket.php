#!/usr/bin/env php

<?php
set_time_limit(0);

require __DIR__ . "/../vendor/autoload.php";

use Socket\Logger\TerminalToLogger;
use Socket\TCPSocket\CreateTCPSocket;
use Socket\BracketFactory;
use Socket\TCPSocket\ConnectTCPSocket;
use Socket\TCPSocket\CreateToSeveralProcess;

try {
    $shortOpts = "";
    $shortOpts .= "p:";
    $shortOpts .= "h";

    $longOpts = array(
        "port:",
        "help",
    );

    $options = getopt($shortOpts, $longOpts);

    $logger = new TerminalToLogger();

    $createTCPSocket = new CreateTCPSocket("localhost", $options, $logger);
    $createTCPSocket->open();

    $bracketFactory = new BracketFactory();

    $connectTCPSocket = new ConnectTCPSocket($createTCPSocket, $bracketFactory, $logger);

    $createToSeveralProcess = new CreateToSeveralProcess(2, $connectTCPSocket, $logger);
    $createToSeveralProcess->run();

} catch (\Exception $e) {
    $logger->log($e->getMessage());
}