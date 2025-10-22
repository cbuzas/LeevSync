<?php

use App\Models\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Task::class);
            $table->string('alias');
            $table->dateTime('executed_at');
            $table->text('log_file')->nullable();
            $table->string('method');
            $table->enum('status', ['running', 'failed', 'canceled', 'completed'])->default('running');
            $table->text('cmd')->nullable();
            $table->json('logs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syncs');
    }
};
