<?php

namespace Socket\Logger;

class TerminalToLogger implements LoggerInterface
{
    public function log($message)
    {
        echo $message . "\n";
    }
}