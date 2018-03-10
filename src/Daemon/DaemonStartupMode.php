<?php

namespace Socket\Daemon;

use Socket\Options\FlagOptions;

class DaemonStartupMode
{
    /**
     * @var array
     */
    private $mode;

    /**
     * DaemonStartupMode constructor.
     *
     * @param FlagOptions $mode
     */
    public function __construct(FlagOptions $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return bool
     */
    public function getMode(): bool
    {
        if (!array_key_exists('g', $this->mode->getOptions()))
            return false;

        switch ($this->mode->getOptions()['g']) {
            case "daemon on":
                $flag = true;
                break;
            case "daemon off":
                $flag = false;
                break;
            default:
                $flag = false;
        }

        return $flag;
    }
}