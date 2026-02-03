<?php

namespace App\Providers;

use App\Listeners\LogFailedLogin;
use App\Listeners\LogLastLoginListener;
use App\Listeners\LogLogout;
use App\Listeners\LogSuccessfulLogin;
use App\Models\Cover;
use App\Observers\CoverObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogLastLoginListener::class,
            LogSuccessfulLogin::class,
        ],

        Failed::class => [
            LogFailedLogin::class,
        ],

        Logout::class => [
            LogLogout::class,
        ],
    ];

    public function boot(): void
    {
        Cover::observe(CoverObserver::class);
    }
}
