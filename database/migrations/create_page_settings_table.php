<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('page');
            $table->string('scope')->default('');
            $table->json('settings')->default('{}');
            $table->json('order')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'page', 'scope']);
            $table->index('page');
        });

        Schema::create('page_setting_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('page');
            $table->json('settings')->default('{}');
            $table->json('order')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('page');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_setting_presets');
        Schema::dropIfExists('page_settings');
    }
};
