#!/usr/bin/env php

<?php
set_time_limit(0);

require __DIR__ . "/../vendor/autoload.php";

use \socket\ValidateOptions;
use \socket\TCP_socket;

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
    $socket->accept();

} catch (\Exception $e) {
    echo $e->getMessage();
}






