<?php

declare(strict_types=1);

namespace JoshBenham\LocalPackageSync;

use Illuminate\Support\ServiceProvider;
use JoshBenham\LocalPackageSync\Console\Commands\AddLocalRepositories;

/**
 * @internal
 */
final class LocalPackageSyncServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                AddLocalRepositories::class,
            ]);
        }
    }
}
