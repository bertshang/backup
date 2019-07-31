<?php

namespace Bertshang\Backup\Events;

use Exception;
use Bertshang\Backup\BackupDestination\BackupDestination;

class BackupHasFailed
{
    /** @var \Exception */
    public $exception;

    /** @var \Bertshang\Backup\BackupDestination\BackupDestination|null */
    public $backupDestination;

    public function __construct(Exception $exception, BackupDestination $backupDestination = null)
    {
        $this->exception = $exception;

        $this->backupDestination = $backupDestination;
    }
}
