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
        // Table 1: vicentinos_records (Main Form)
        Schema::create('vicentinos_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('paroquia_id')->index();
            $table->date('data_ficha')->nullable();
            
            // Header Info
            $table->string('conferencia')->nullable();
            $table->string('conselho_particular')->nullable();
            
            // Responsável Info
            $table->string('responsavel_nome');
            $table->integer('idade')->nullable();
            $table->string('rg', 20)->nullable();
            $table->string('cpf', 14)->nullable();
            
            // Endereço
            $table->string('endereco')->nullable();
            $table->string('endereco_numero', 20)->nullable();
            $table->string('bairro')->nullable(); // Maps to home_situation in registers
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 9)->nullable();
            
            // Contato
            $table->string('contato_principal', 20)->nullable();
            $table->string('contato_recado', 20)->nullable();
            $table->string('falar_com')->nullable();
            
            // Benefícios
            $table->boolean('recebe_bolsa_familia')->default(false);
            $table->decimal('valor_bolsa_familia', 10, 2)->nullable();
            $table->string('outro_beneficio_nome')->nullable();
            $table->decimal('outro_beneficio_valor', 10, 2)->nullable();
            
            // Situação Habitacional
            $table->string('tipo_residencia')->nullable(); // Propria, Alugada, Financiada, Cedida, etc.
            $table->decimal('valor_aluguel_prestacao', 10, 2)->nullable();
            
            // Religião
            $table->string('religiao')->nullable();
            $table->boolean('catolico_tem_sacramentos')->default(false);
            $table->string('sacramento_faltando')->nullable();
            
            // Trabalho
            $table->string('quem_trabalha')->nullable();
            $table->string('local_trabalho')->nullable();
            
            // Outros
            $table->text('observacoes')->nullable();
            $table->string('responsaveis_sindicancia')->nullable();
            
            // Dispensa
            $table->text('motivo_dispensa')->nullable();
            $table->date('data_dispensa')->nullable();
            
            $table->timestamps();
        });

        // Table 2: vicentinos_families (Family Members)
        Schema::create('vicentinos_families', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vicentinos_record_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->integer('paroquia_id')->index();
            
            $table->string('nome');
            $table->string('parentesco')->nullable();
            $table->date('nascimento')->nullable();
            $table->string('profissao')->nullable();
            $table->string('escolaridade')->nullable();
            $table->decimal('renda', 10, 2)->nullable();
            
            $table->timestamps();
            
            // Foreign key constraint removed to avoid permission issues
            // $table->foreign('vicentinos_record_id')->references('id')->on('vicentinos_records')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vicentinos_families');
        Schema::dropIfExists('vicentinos_records');
    }
};
