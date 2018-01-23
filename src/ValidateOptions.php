<?php

namespace socket;


use socket\Exceptions\EmptyContentException;
use socket\Exceptions\HelpContentException;

class ValidateOptions
{
    private $options;
    private $flag;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @throws EmptyContentException
     */
    public function flag()
    {
        if (empty($this->options))
            throw new EmptyContentException("Не указан номер порта. \nДля получение справки укажите флаг -h, --help \n");

        switch (array_keys($this->options)[0]) {
            case "p":
                $this->flag = "p";
                break;
            case "port":
                $this->flag = "port";
                break;
            case "h":
            case "help":
                $this->flag = "help";
                break;
        }

    }


    /**
     * @return int
     * @throws EmptyContentException
     * @throws HelpContentException
     */
    public function getValueFlag(): int
    {
        if ($this->flag == "help")
            throw new HelpContentException(<<<HTML
    Использование: bracket.php [КЛЮЧ]=[ЗНАЧЕНИЕ]
    
После соединения с сервером, необходимо передать строку содержащую последовательность скобок для валидации.
Приняв строку, сервер валидирует ее и выдает ответ, после чего готов принять новую строку для валидации.

Для завершения соединения необходимо отправить: exit

Аргументы.                  
    -p=номер_порта,
    --port=номер_порта      установить номер TCP-порта, по которому 
                            сервер начнет принимать соединение
                            
    -h, --help              показать эту справку и выйти

HTML
            );

        $port = (int)$this->options[$this->flag];

        if ($port == 0)
            throw new EmptyContentException("Неверно указан флаг или номер порта. \n");

        return $port;
    }

}