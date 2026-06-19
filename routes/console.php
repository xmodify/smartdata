<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\MophNotify\ServiceController;
use App\Http\Controllers\MophNotify\ReplicationController;
use App\Http\Controllers\MophNotify\BackupController;

// Default inspiring quote command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Laravel Task Scheduler
|--------------------------------------------------------------------------
|
| Define your scheduled tasks directly in the code.
| To activate these tasks, you only need to configure EXACTLY ONE
| Windows Task Scheduler job to run:
|
| powershell -Command "php d:\Project Laravel\smartdata\artisan schedule:run"
|
| running every 1 minute.
|
*/

// 1. ส่งสถิติเวรดึก (ตรวจงาน 00.00-08.00 น.) ส่งเวลา 08:00 น.
Schedule::call(function () {
    app(ServiceController::class)->service_night();
})->dailyAt('08:00');

// 2. ส่งสถิติเวรเช้า (ตรวจงาน 08.00-16.00 น.) ส่งเวลา 16:00 น.
Schedule::call(function () {
    app(ServiceController::class)->service_morning();
})->dailyAt('16:00');

// 3. ส่งสถิติเวรบ่าย (ตรวจงาน 16.00-24.00 น.) ส่งเวลา 00:01 น. (เที่ยงคืน 1 นาที ของวันถัดไป)
Schedule::call(function () {
    app(ServiceController::class)->service_afternoon();
})->dailyAt('00:01');

// 4. ตรวจสอบ MySQL Replication (Master-Slaves) ทุกๆ 10 นาที
Schedule::call(function () {
    app(ReplicationController::class)->check(request());
})->everyTenMinutes();

// 5. ตรวจสอบสถานะการสำรองข้อมูล (Backup HOSxP) ทุกๆ 10 นาที
Schedule::call(function () {
    app(BackupController::class)->check(request());
})->everyTenMinutes();
