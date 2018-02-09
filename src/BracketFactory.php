<?php

namespace Socket;

use Library\Services\BracketService;

class BracketFactory
{
    /**
     * @param $input
     *
     * @return BracketService
     *
     * @throws \Library\Exceptions\InvalidArgumentException
     */
    public function getBracketService($input): BracketService
    {
        return new BracketService($input);
    }
}