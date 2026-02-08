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
        if (!Schema::hasTable('protocols_superadmin')) {
            Schema::create('protocols_superadmin', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->unsignedBigInteger('user_id');
                $table->text('description');
                $table->unsignedBigInteger('paroquia_id');
                $table->integer('status')->default(0); // 0=pending, 1=concluded, 2=rejected, 3=cancelled
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('protocols_files')) {
            Schema::create('protocols_files', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('protocol_id');
                $table->string('file_name');
                $table->timestamp('uploaded_at')->useCurrent();
                $table->timestamps();

                $table->foreign('protocol_id')->references('id')->on('protocols_superadmin')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocols_files');
        Schema::dropIfExists('protocols_superadmin');
    }
};
