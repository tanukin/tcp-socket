<?php

namespace Socket\Logger;

use Socket\Interfaces\LoggerInterface;

class FileToLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * FileToLogger constructor.
     *
     * @param string $fileName
     */
    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        file_put_contents($this->fileName, "[".date("d.m.Y H:i:s")."]: ". $message. "\r\n", FILE_APPEND);
    }

}
