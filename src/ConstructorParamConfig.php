<?php

declare(strict_types=1);

namespace BuzzingPixel\Container;

class ConstructorParamConfig
{
    private string $id;

    private string $param;

    /** @var mixed */
    private $give;

    /**
     * @param mixed $give
     */
    public function __construct(
        string $id,
        string $param,
        $give
    ) {
        $this->id    = $id;
        $this->param = $param;
        $this->give  = $give;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function param(): string
    {
        return $this->param;
    }

    /**
     * @return mixed
     */
    public function give()
    {
        return $this->give;
    }
}
