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
        Schema::table('pinned_modules', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('module_slug');
            $table->string('bg_color')->nullable()->after('order');
            $table->string('text_color')->nullable()->after('bg_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pinned_modules', function (Blueprint $table) {
            $table->dropColumn(['order', 'bg_color', 'text_color']);
        });
    }
};
