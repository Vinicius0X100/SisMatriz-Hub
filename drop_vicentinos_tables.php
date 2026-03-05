<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Schema::disableForeignKeyConstraints();
Schema::dropIfExists('vicentinos_families');
Schema::dropIfExists('vicentinos_records');
Schema::enableForeignKeyConstraints();

echo "Tables dropped successfully.\n";
