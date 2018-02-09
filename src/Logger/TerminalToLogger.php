<?php

namespace Socket\Logger;

use Socket\Interfaces\LoggerInterface;

class TerminalToLogger implements LoggerInterface
{
    public function log($message)
    {
        echo $message . "\n";
    }
}