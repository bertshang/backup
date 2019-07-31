<?php

namespace Bertshang\Backup\Events;

use Bertshang\Backup\Tasks\Backup\Manifest;

class BackupManifestWasCreated
{
    /** @var \Bertshang\Backup\Tasks\Backup\Manifest */
    public $manifest;

    public function __construct(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }
}
