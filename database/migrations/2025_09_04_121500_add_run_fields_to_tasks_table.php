<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('last_dry_run_summary')->nullable()->after('cmd');
            $table->longText('last_run_log')->nullable()->after('last_dry_run_summary');
            $table->timestamp('last_run_at')->nullable()->after('last_run_log');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['last_dry_run_summary', 'last_run_log', 'last_run_at']);
        });
    }
};
