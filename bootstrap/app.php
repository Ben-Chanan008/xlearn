<?php

use App\Jobs\CheckInactiveMessages;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'role-redirect' => \App\Http\Middleware\DashboardRedirect::class
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        //
        $schedule->job(new CheckInactiveMessages())
        ->weeklyOn(5, '22:20')
        ->when(fn () => date('W') % 3 === 1); // Run every 3 weeks
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();