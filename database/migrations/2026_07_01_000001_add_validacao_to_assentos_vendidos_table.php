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
        Schema::table('assentos_vendidos', function (Blueprint $table) {
            $table->timestamp('validado_em')->nullable()->after('embarque_volta');
            $table->unsignedBigInteger('validado_por')->nullable()->after('validado_em');
            $table->foreign('validado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assentos_vendidos', function (Blueprint $table) {
            $table->dropForeign(['validado_por']);
            $table->dropColumn(['validado_em', 'validado_por']);
        });
    }
};
