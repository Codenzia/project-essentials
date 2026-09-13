<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_setting_presets') || Schema::hasColumn('page_setting_presets', 'scope')) {
            return;
        }

        Schema::table('page_setting_presets', function (Blueprint $table) {
            $table->string('scope')->default('')->after('page');
            $table->index(['page', 'scope']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_setting_presets') || ! Schema::hasColumn('page_setting_presets', 'scope')) {
            return;
        }

        Schema::table('page_setting_presets', function (Blueprint $table) {
            $table->dropIndex(['page', 'scope']);
            $table->dropColumn('scope');
        });
    }
};
