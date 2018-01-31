<?php

namespace Socket\Options;

use Socket\Exceptions\EmptyContentException;
use Socket\Interfaces\OptionsInterface;
use Symfony\Component\Yaml\Yaml;


class ConfigFileOptions implements OptionsInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * configFile constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     *
     * @throws EmptyContentException
     */
    public function getOptions(): array
    {
        if (!file_exists($this->path))
            throw new EmptyContentException("File not found");

        $content = Yaml::parseFile($this->path);

        if (empty($content))
            throw new EmptyContentException("Config file is empty");

        return $content;
    }

}