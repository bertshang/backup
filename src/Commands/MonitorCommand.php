<?php

namespace Bertshang\Backup\Commands;

use Bertshang\Backup\Events\HealthyBackupWasFound;
use Bertshang\Backup\Events\UnhealthyBackupWasFound;
use Bertshang\Backup\Tasks\Monitor\BackupDestinationStatus;
use Bertshang\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class MonitorCommand extends BaseCommand
{
    /** @var string */
    protected $signature = 'backup:monitor';

    /** @var string */
    protected $description = 'Monitor the health of all backups.';

    public function handle()
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitorBackups'));

        $statuses->each(function (BackupDestinationStatus $backupDestinationStatus) {
            if ($backupDestinationStatus->isHealthy()) {
                $this->info("The backups on {$backupDestinationStatus->diskName()} are considered healthy.");
                event(new HealthyBackupWasFound($backupDestinationStatus));

                return;
            }

            $this->error("The backups on {$backupDestinationStatus->diskName()} are considered unhealthy!");
            event(new UnHealthyBackupWasFound($backupDestinationStatus));
        });
    }
}
