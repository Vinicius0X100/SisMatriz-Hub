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
        Schema::table('registers', function (Blueprint $table) {
            // Modifying home_situation to be string (Bairro)
            // Using raw SQL for safety if doctrine/dbal is missing, or try standard Laravel way
            // But to be safe and avoid dependency issues, I'll rely on Laravel's native change if supported, 
            // or simply adding if missing.
        });

        // Split operations to handle existence checks
        if (Schema::hasColumn('registers', 'home_situation')) {
            Schema::table('registers', function (Blueprint $table) {
                $table->string('home_situation', 255)->nullable()->change();
            });
        } else {
            Schema::table('registers', function (Blueprint $table) {
                $table->string('home_situation', 255)->nullable();
            });
        }

        if (!Schema::hasColumn('registers', 'country')) {
            Schema::table('registers', function (Blueprint $table) {
                $table->string('country', 100)->nullable();
            });
        }

        if (!Schema::hasColumn('registers', 'rg')) {
            Schema::table('registers', function (Blueprint $table) {
                $table->string('rg', 20)->nullable();
            });
        }

        if (!Schema::hasColumn('registers', 'work_state')) {
            Schema::table('registers', function (Blueprint $table) {
                $table->integer('work_state')->nullable()->default(4);
            });
        }

        if (!Schema::hasColumn('registers', 'race')) {
            Schema::table('registers', function (Blueprint $table) {
                $table->integer('race')->nullable()->default(5);
            });
        }

        if (!Schema::hasColumn('registers', 'familly_qntd')) {
            Schema::table('registers', function (Blueprint $table) {
                $table->integer('familly_qntd')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting implies knowing previous state, which is hard. 
        // We'll leave it as is or revert only added columns.
        Schema::table('registers', function (Blueprint $table) {
            //
        });
    }
};
