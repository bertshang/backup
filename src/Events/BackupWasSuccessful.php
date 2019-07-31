<?php

namespace Bertshang\Backup\Events;

use Bertshang\Backup\BackupDestination\BackupDestination;

class BackupWasSuccessful
{
    /** @var \Bertshang\Backup\BackupDestination\BackupDestination */
    public $backupDestination;

    public function __construct(BackupDestination $backupDestination)
    {
        $this->backupDestination = $backupDestination;
    }
}
