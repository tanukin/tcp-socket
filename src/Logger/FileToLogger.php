<?php

namespace Socket\Logger;

class FileToLogger implements LoggerInterface
{
    /**
     * @var string
     */
    private $fileName;
    private $fp;

    /**
     * FileToLogger constructor.
     *
     * @param string $fileName
     */
    public function __construct(string $fileName = "correct-brackets-socket.log")
    {
        $this->fileName = $fileName;
        $this->open();
    }

    public function open()
    {
        $this->fp = fopen($this->fileName , 'a+');
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        fwrite($this->fp, $message."\r\n");
    }

    public function __destruct()
    {
        fclose($this->fp);
    }
}