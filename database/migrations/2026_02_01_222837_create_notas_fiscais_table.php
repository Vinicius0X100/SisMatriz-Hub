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
        Schema::dropIfExists('notas_fiscais');
        
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos e Tenant
            $table->unsignedBigInteger('paroquia_id')->index();
            $table->unsignedBigInteger('entidade_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index(); // Quem cadastrou

            // Identificação da Nota
            $table->string('numero', 50); // Número da nota
            $table->string('serie', 10)->nullable(); // Série
            $table->string('chave_acesso', 44)->nullable()->unique(); // Chave da NFe/NFCe
            $table->enum('tipo', ['NFe', 'NFCe', 'NFSe', 'Cupom', 'Recibo', 'Boleto', 'Outro'])->default('NFe');
            
            // Datas
            $table->date('data_emissao');
            $table->date('data_entrada')->nullable(); // Data de competência/entrada
            
            // Partes
            $table->string('emitente_nome'); // Razão Social / Nome
            $table->string('emitente_documento', 20)->nullable(); // CNPJ/CPF
            
            // Valores
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_acrescimo', 15, 2)->default(0);
            
            // Detalhes
            $table->text('descricao')->nullable(); // Descrição geral dos itens/serviço
            $table->string('caminho_arquivo')->nullable(); // Path do arquivo (PDF/XML)
            
            // Controle
            $table->enum('status', ['pendente', 'aprovada', 'cancelada', 'em_analise'])->default('pendente');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
