<?php

namespace Bertshang\Backup;

use Illuminate\Support\ServiceProvider;
use Bertshang\Backup\Commands\ListCommand;
use Bertshang\Backup\Helpers\ConsoleOutput;
use Bertshang\Backup\Commands\BackupCommand;
use Bertshang\Backup\Commands\CleanupCommand;
use Bertshang\Backup\Commands\MonitorCommand;
use Bertshang\Backup\Notifications\EventHandler;
use Bertshang\Backup\Tasks\Cleanup\CleanupStrategy;

class BackupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/backup.php' => config_path('backup.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/backup'),
        ]);

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'backup');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/backup.php', 'backup');

        $this->app['events']->subscribe(EventHandler::class);

        $this->app->bind('command.backup:run', BackupCommand::class);
        $this->app->bind('command.backup:clean', CleanupCommand::class);
        $this->app->bind('command.backup:list', ListCommand::class);
        $this->app->bind('command.backup:monitor', MonitorCommand::class);

        $this->app->bind(CleanupStrategy::class, config('backup.cleanup.strategy'));

        $this->commands([
            'command.backup:run',
            'command.backup:clean',
            'command.backup:list',
            'command.backup:monitor',
        ]);

        $this->app->singleton(ConsoleOutput::class);
    }
}
