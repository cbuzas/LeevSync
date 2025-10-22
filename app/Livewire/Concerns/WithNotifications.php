<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithNotifications
{
    /**
     * Display a success notification.
     */
    protected function notifySuccess(string $message, int $duration = 5000): void
    {
        $this->dispatch('notify',
            message: $message,
            type: 'success',
            duration: $duration
        );
    }

    /**
     * Display an error notification.
     */
    protected function notifyError(string $message, int $duration = 5000): void
    {
        $this->dispatch('notify',
            message: $message,
            type: 'error',
            duration: $duration
        );
    }

    /**
     * Display a warning notification.
     */
    protected function notifyWarning(string $message, int $duration = 5000): void
    {
        $this->dispatch('notify',
            message: $message,
            type: 'warning',
            duration: $duration
        );
    }

    /**
     * Display an info notification.
     */
    protected function notifyInfo(string $message, int $duration = 5000): void
    {
        $this->dispatch('notify',
            message: $message,
            type: 'info',
            duration: $duration
        );
    }

    /**
     * Display a custom notification.
     */
    protected function notify(string $message, string $type = 'info', int $duration = 5000): void
    {
        $this->dispatch('notify',
            message: $message,
            type: $type,
            duration: $duration
        );
    }
}
