<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('last_output')->nullable();
            $table->text('last_error')->nullable();
            $table->string('status')->default('idle'); // idle, running, completed, failed

        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['last_output', 'last_error', 'status']);
        });
    }
};
