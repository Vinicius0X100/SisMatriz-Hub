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
            if (!Schema::hasColumn('registers', 'neighborhood')) {
                $table->string('neighborhood', 255)->nullable();
            }
            if (!Schema::hasColumn('registers', 'mother_name')) {
                $table->string('mother_name', 255)->nullable();
            }
            if (!Schema::hasColumn('registers', 'father_name')) {
                $table->string('father_name', 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registers', function (Blueprint $table) {
            if (Schema::hasColumn('registers', 'neighborhood')) {
                $table->dropColumn('neighborhood');
            }
            if (Schema::hasColumn('registers', 'mother_name')) {
                $table->dropColumn('mother_name');
            }
            if (Schema::hasColumn('registers', 'father_name')) {
                $table->dropColumn('father_name');
            }
        });
    }
};
