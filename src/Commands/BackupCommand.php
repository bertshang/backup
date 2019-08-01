<?php

namespace Bertshang\Backup\Commands;

use Exception;
use Bertshang\Backup\Events\BackupHasFailed;
use Bertshang\Backup\Exceptions\InvalidCommand;
use Bertshang\Backup\Tasks\Backup\BackupJobFactory;
use Bertshang\Backup\Helpers\ConsoleOutput;
class BackupCommand extends BaseCommand
{

    /** @var string */
    protected $signature = "backup:run {dbname} {--filename=} {--only-db} {--db-name=*} {--only-files} {--only-to-disk=} {--disable-notifications}";

    /** @var string */
    protected $description = '运行备份命令.';

    public function handle()
    {
        app(ConsoleOutput::class)->comment('Starting backup...');

        $disableNotifications = $this->option('disable-notifications');

        try {
            $this->guardAgainstInvalidOptions();

            $backupJob = BackupJobFactory::createFromArray(config('backup'));

            if ($this->option('only-db')) {
                $backupJob->dontBackupFilesystem();
            }
            if ($this->option('db-name')) {
                $backupJob->onlyDbName($this->option('db-name'));
            }

            if ($this->option('only-files')) {
                $backupJob->dontBackupDatabases();
            }

            if ($this->option('only-to-disk')) {
                $backupJob->onlyBackupTo($this->option('only-to-disk'));
            }

            if ($this->option('filename')) {
                $backupJob->setFilename($this->option('filename'));
            }

            if ($disableNotifications) {
                $backupJob->disableNotifications();
            }

            $db = $this->argument('dbname');

            $backupJob->run($db);

            app(ConsoleOutput::class)->comment('Backup completed!');
        } catch (Exception $exception) {
            app(ConsoleOutput::class)->error("Backup failed because: {$exception->getMessage()}.");

            if (! $disableNotifications) {
                event(new BackupHasFailed($exception));
            }

            return 1;
        }
    }

    protected function guardAgainstInvalidOptions()
    {
        if ($this->option('only-db') && $this->option('only-files')) {
            throw InvalidCommand::create('Cannot use `only-db` and `only-files` together');
        }
    }
}
