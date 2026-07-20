<?php

use App\Console\Commands\RetryDebtNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(RetryDebtNotification::class)->hourly();
