<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('data_aset', function(Blueprint $table) {
    if(Schema::hasColumn('data_aset', 'penanggung_jawab_id')) {
        $table->dropColumn('penanggung_jawab_id');
        echo "Column penanggung_jawab_id dropped.\n";
    } else {
        echo "Column penanggung_jawab_id not found.\n";
    }
});
