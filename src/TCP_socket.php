<?php

namespace socket;

use Library\Services\BracketService;

class TCP_socket
{
    private $socket;
    private $spawn;

    public function __construct($host = "localhost", $port = 10001)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($this->socket, $host, $port);
        socket_listen($this->socket, 3);
        echo "Ожидание соединений на порту $port ...\n";
    }

    public function __destruct()
    {
        socket_close($this->socket);
        echo "Завершение работы\n";
    }

    public function accept()
    {
        do {
            $this->spawn = socket_accept($this->socket);
            echo "Получен запрос на соединение\n";

            $welcome = "\nСоединение установлено. \nНеобходимо отправить последовательность скобок для валидации.\n\nДля завершения сеанса введите команду: exit\n\n";
            socket_write($this->spawn, $welcome, strlen($welcome));

            $this->communication();

            socket_close($this->spawn);
            echo "Соединение завершено.\n";
            echo "Ожидание соединений ...\n";

        } while (true);

    }


    protected function communication()
    {
        do {
            $input = socket_read($this->spawn, 2048, PHP_BINARY_READ);
            $input = trim($input);

            if ($input != "") {
                echo "Получена строка от пользователя: $input" . "\n";

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
                echo "Ответ сервера: " . trim($output) . "\n";
            }

        } while (true);
    }


}