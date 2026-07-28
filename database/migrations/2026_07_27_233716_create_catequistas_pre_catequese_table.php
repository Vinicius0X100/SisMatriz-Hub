<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catequistas_pre_catequese', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('register_id')->nullable();
            $table->string('nome', 100);
            $table->unsignedBigInteger('ent_id')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('paroquia_id')->default(0);
            $table->date('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catequistas_pre_catequese');
    }
};
