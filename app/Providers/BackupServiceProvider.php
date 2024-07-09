<?php

namespace App\Providers;

use App\Admin\Extensions\Backup;
use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views/backup', 'admin');

        Backup::boot();
    }
}
