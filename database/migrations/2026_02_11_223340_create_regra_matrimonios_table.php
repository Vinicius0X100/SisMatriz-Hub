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
        if (!Schema::hasTable('paroquia_rules_matrimonio')) {
            Schema::create('paroquia_rules_matrimonio', function (Blueprint $table) {
                $table->id();
                $table->integer('paroquia_id')->index();
                $table->integer('comunidade_id');
                $table->integer('max_casamentos_por_dia')->default(0);
                $table->string('dias_permitidos')->nullable(); // Ex: "5,6"
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paroquia_rules_matrimonio');
    }
};
