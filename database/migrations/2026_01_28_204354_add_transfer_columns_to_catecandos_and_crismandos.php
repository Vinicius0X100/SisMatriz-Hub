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
        Schema::table('catecandos', function (Blueprint $table) {
            if (!Schema::hasColumn('catecandos', 'transfer_date')) {
                $table->date('transfer_date')->nullable();
            }
        });

        Schema::table('crismandos', function (Blueprint $table) {
            if (!Schema::hasColumn('crismandos', 'is_transfered')) {
                $table->boolean('is_transfered')->default(0);
            }
            if (!Schema::hasColumn('crismandos', 'transfer_date')) {
                $table->date('transfer_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catecandos', function (Blueprint $table) {
            $table->dropColumn('transfer_date');
        });

        Schema::table('crismandos', function (Blueprint $table) {
            $table->dropColumn(['is_transfered', 'transfer_date']);
        });
    }
};
