<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:project', function (): void {
    $this->info('Smart Timetable API');
})->purpose('Display basic project information');
