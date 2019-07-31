<?php

namespace Bertshang\Backup\DbDumper\Exceptions;

use Exception;

class CannotStartDump extends Exception
{
    /**
     * @param string $name
     *
     * @return \Bertshang\Backup\DbDumper\Exceptions\CannotStartDump
     */
    public static function emptyParameter($name)
    {
        return new static("Parameter `{$name}` cannot be empty.");
    }
}
