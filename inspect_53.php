<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Report;
use App\Models\List_Report;

// Cari 53 reports yang unapproved
$reports = Report::all()->filter(function($r) {
    return List_Report::where('Id_Report', $r->Id_Report)
        ->whereNull('Time_List_Report')
        ->whereNull('Time_Approved_Leader')
        ->whereNull('Time_Approved_Auditor')
        ->exists();
});

echo "Total Reports with unapproved items: " . $reports->count() . PHP_EOL;

// Cek per bulan
$byMonth = [];
foreach ($reports as $r) {
    $m = \Carbon\Carbon::parse($r->Start_Report)->format('Y-m');
    $byMonth[$m] = ($byMonth[$m] ?? 0) + 1;
}
print_r($byMonth);
