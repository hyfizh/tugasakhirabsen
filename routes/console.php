<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\AutoAlphaCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('absensi:auto-alpha', function () {
    $this->call(AutoAlphaCommand::class);
})->purpose('Otomatis menandai status Alpa (A) bagi mahasiswa yang tidak absen setelah jam matkul berakhir');
