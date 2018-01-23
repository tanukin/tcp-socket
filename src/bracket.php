#!/usr/bin/env php

<?php
set_time_limit(0);

require __DIR__ . "/../vendor/autoload.php";

use \socket\ValidateOptions;
use \socket\TCP_socket;

declare(ticks=1);

try {

    $shortopts = "";
    $shortopts .= "p:";
    $shortopts .= "h";

    $longopts = array(
        "port:",
        "help",
    );

    $options = getopt($shortopts, $longopts);

    $validate = new ValidateOptions($options);
    $validate->flag();
    $port = $validate->getValueFlag();

    $socket = new TCP_socket("localhost", $port);

    for ($i = 0; $i < 2; $i++) {

        $pid = pcntl_fork();

        if ($pid > 0) {
            $parentPid = posix_getpid();
            echo "Parent с pid = $parentPid \n";
        }


        if ($pid == 0) {

            $childPid = posix_getpid();
            echo "Child с pid = $childPid слушает на 127.0.0.1:$port \n";
            $socket->accept($childPid);
            exit($i);
        }
    }

    pcntl_signal(SIGINT, function () {
        exit;
    });

    while (pcntl_waitpid(0, $status) != -1) {
        $status = pcntl_wexitstatus($status);
        echo "Child $status завершен\n";
    }


} catch (\Exception $e) {
    echo $e->getMessage();
}






