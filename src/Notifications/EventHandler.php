<?php

namespace Bertshang\Backup\Notifications;

use Bertshang\Backup\Events\BackupHasFailed;
use Illuminate\Notifications\Notification;
use Bertshang\Backup\Events\CleanupHasFailed;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Bertshang\Backup\Events\BackupWasSuccessful;
use Bertshang\Backup\Events\CleanupWasSuccessful;
use Bertshang\Backup\Events\HealthyBackupWasFound;
use Bertshang\Backup\Events\UnhealthyBackupWasFound;
use Bertshang\Backup\Exceptions\NotificationCouldNotBeSent;

class EventHandler
{
    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen($this->allBackupEventClasses(), function ($event) {
            $notifiable = $this->determineNotifiable();

            $notification = $this->determineNotification($event);

            $notifiable->notify($notification);
        });
    }

    protected function determineNotifiable()
    {
        $notifiableClass = $this->config->get('backup.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification($event): Notification
    {
        $eventName = class_basename($event);

        $notificationClass = collect($this->config->get('backup.notifications.notifications'))
            ->keys()
            ->first(function ($notificationClass) use ($eventName) {
                $notificationName = class_basename($notificationClass);

                return $notificationName === $eventName;
            });

        if (! $notificationClass) {
            throw NotificationCouldNotBeSent::noNotifcationClassForEvent($event);
        }

        return app($notificationClass)->setEvent($event);
    }

    protected function allBackupEventClasses(): array
    {
        return [
            BackupHasFailed::class,
            BackupWasSuccessful::class,
            CleanupHasFailed::class,
            CleanupWasSuccessful::class,
            HealthyBackupWasFound::class,
            UnhealthyBackupWasFound::class,
        ];
    }
}
