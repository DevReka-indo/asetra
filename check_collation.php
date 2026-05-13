<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLE STATUS');
foreach($tables as $table) {
    echo $table->Name . ": " . $table->Collation . "\n";
}
