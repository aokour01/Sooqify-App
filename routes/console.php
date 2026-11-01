<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('ads:expire')->hourly();
Schedule::command('ads:unprocessed')->everyFiveSeconds();
