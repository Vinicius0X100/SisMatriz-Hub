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
        if (!Schema::hasTable('catequistas_crisma')) {
            Schema::create('catequistas_crisma', function (Blueprint $table) {
                $table->id();
                $table->foreignId('register_id')->constrained('registers')->onDelete('cascade');
                $table->string('nome');
                $table->unsignedBigInteger('ent_id')->index();
                $table->integer('status')->default(1);
                $table->unsignedBigInteger('paroquia_id')->nullable()->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catequistas_eucaristia')) {
            Schema::create('catequistas_eucaristia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('register_id')->constrained('registers')->onDelete('cascade');
                $table->string('nome');
                $table->unsignedBigInteger('ent_id')->index();
                $table->integer('status')->default(1);
                $table->unsignedBigInteger('paroquia_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catequistas_eucaristia');
        Schema::dropIfExists('catequistas_crisma');
    }
};
