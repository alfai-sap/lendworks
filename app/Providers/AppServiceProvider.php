<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    // Toggle this to enable/disable the time shift
    protected static $timeShiftEnabled = true; // Enable (true) , Disable (False)
    protected static $daysToAdd = 5; // Advance number of days

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (self::$timeShiftEnabled) {
            Carbon::setTestNow(Carbon::now()->addDays(self::$daysToAdd));
        }
        //
    }
}
