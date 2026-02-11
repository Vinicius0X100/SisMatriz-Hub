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
        // Drop table if exists to ensure clean slate with new structure
        Schema::dropIfExists('batismos');

        Schema::create('batismos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('register_id');
            // $table->foreign('register_id')->references('id')->on('registers')->onDelete('cascade'); // Permission denied workaround
            
            $table->integer('paroquia_id')->index();
            $table->boolean('is_batizado')->default(false);
            $table->date('data_batismo')->nullable();
            $table->string('local_batismo')->nullable(); // Ex: Paróquia São José
            $table->string('celebrante')->nullable();
            $table->string('padrinho_nome')->nullable();
            $table->string('madrinha_nome')->nullable();
            $table->string('livro')->nullable();
            $table->string('folha')->nullable();
            $table->string('registro')->nullable(); // Número do registro
            $table->text('obs')->nullable();
            $table->timestamps();

            $table->index('register_id'); // Add index for performance
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batismos');
    }
};
