<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('vicentinos_records')) {
            Schema::table('vicentinos_records', function (Blueprint $table) {
                if (!Schema::hasColumn('vicentinos_records', 'equipe_id')) {
                    $table->unsignedBigInteger('equipe_id')->nullable()->after('paroquia_id')->index();
                }
            });
        }

        if (Schema::hasTable('vin_watcheds')) {
            Schema::table('vin_watcheds', function (Blueprint $table) {
                if (!Schema::hasColumn('vin_watcheds', 'equipe_id')) {
                    $table->unsignedBigInteger('equipe_id')->nullable()->after('paroquia_id')->index();
                }
                if (!Schema::hasColumn('vin_watcheds', 'vicentinos_record_id')) {
                    $table->unsignedBigInteger('vicentinos_record_id')->nullable()->after('w_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vicentinos_records')) {
            Schema::table('vicentinos_records', function (Blueprint $table) {
                if (Schema::hasColumn('vicentinos_records', 'equipe_id')) {
                    $table->dropColumn('equipe_id');
                }
            });
        }

        if (Schema::hasTable('vin_watcheds')) {
            Schema::table('vin_watcheds', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('vin_watcheds', 'equipe_id')) {
                    $columns[] = 'equipe_id';
                }
                if (Schema::hasColumn('vin_watcheds', 'vicentinos_record_id')) {
                    $columns[] = 'vicentinos_record_id';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
