<?php

declare(strict_types=1);

namespace BuzzingPixel\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class EntryNotFound extends Exception implements NotFoundExceptionInterface
{
}
