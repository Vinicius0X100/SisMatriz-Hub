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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('paroquia_id')->nullable()->index();
            $table->integer('user_id')->nullable()->index();
            $table->string('user_name', 100)->nullable();
            $table->string('category', 30)->index(); // auth, navigation, model, action
            $table->string('action', 50)->index();   // login, logout, page_view, create, update, delete, etc.
            $table->string('module', 60)->index();
            $table->string('description', 255);
            $table->string('auditable_type', 100)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('url', 255)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('status', 20)->default('success')->index(); // success, failed, blocked
            $table->string('source', 20)->default('web');              // web, api, system, frontend
            $table->string('request_id', 36)->nullable()->index();
            $table->timestamp('created_at')->nullable()->index();

            // Índices compostos de alta performance para auditoria e filtros
            $table->index(['paroquia_id', 'created_at'], 'idx_act_parish_created');
            $table->index(['user_id', 'created_at'], 'idx_act_user_created');
            $table->index(['paroquia_id', 'module', 'created_at'], 'idx_act_module_created');
            $table->index(['paroquia_id', 'category', 'created_at'], 'idx_act_category_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
