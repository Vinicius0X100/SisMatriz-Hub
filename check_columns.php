<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['vicentinos_records', 'vicentinos_families'];

foreach ($tables as $table) {
    echo "Table: $table\n";
    $columns = Schema::getColumnListing($table);
    foreach ($columns as $column) {
        echo " - $column\n";
    }
    echo "\n";
}
