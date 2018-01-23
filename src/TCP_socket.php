<?php

namespace socket;

use Library\Services\BracketService;

class TCP_socket
{
    private $socket;
    private $spawn;
    private $childPid;

    public function __construct($host = "localhost", $port)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $host, $port);
        socket_listen($this->socket, 3);
        echo "Ожидаются соединения на порту $port ...\n";
    }

    public function __destruct()
    {
        socket_close($this->socket);
        echo "Завершение работы\n";
    }

    /*
     * @param $childPid
     */
    public function accept(int $childPid)
    {
        $this->childPid = $childPid;
        do {
            $this->spawn = socket_accept($this->socket);
            echo "Получен запрос на соединение (pid = $this->childPid) \n";

            $welcome = "\nСоединение установлено. \nНеобходимо отправить последовательность скобок для валидации.\n\nДля завершения сеанса введите команду: exit\n\n";
            socket_write($this->spawn, $welcome, strlen($welcome));

            $this->communication();

            socket_close($this->spawn);
            echo "Соединение завершено (pid = $this->childPid).\n";
        } while (true);

    }


    protected function communication()
    {
        do {
            $input = socket_read($this->spawn, 2048, PHP_BINARY_READ);
            $input = trim($input);

            if ($input != "") {
                echo "Получена строка от пользователя (pid = $this->childPid): $input" . "\n";

                if ($input == "exit") {
                    break;
                }

                try {
                    $bkt = new BracketService($input);
                    $output = $bkt->check() ? "Скобки расставлены верно." : "Скобки расставленны не верно.";
                } catch (\Exception $e) {
                    $output = "ERROR! " . $e->getMessage();
                }

                socket_write($this->spawn, $output . "\n", strlen($output) + 2);
                echo "Ответ сервера (pid = $this->childPid): " . trim($output) . "\n";
            }

        } while (true);
    }


}