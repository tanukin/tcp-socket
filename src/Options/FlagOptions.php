<?php

namespace Socket\Options;

use Socket\Interfaces\OptionsInterface;

class FlagOptions implements OptionsInterface
{
    public function getOptions(): array
    {
        $shortOpts = "";
        $shortOpts .= "p:";
        $shortOpts .= "h";
        $shortOpts .= "g:";

        $longOpts = array(
            "port:",
            "help",
        );

        return getopt($shortOpts, $longOpts);
    }
}