<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->string('email')->nullable(); $table->string('phone')->nullable(); $table->text('notes')->nullable(); $table->timestamps(); });
        Schema::create('projects', function (Blueprint $table) { $table->id(); $table->foreignId('customer_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->text('description')->nullable(); $table->string('status')->default('active'); $table->date('due_date')->nullable(); $table->decimal('hourly_rate', 10, 2)->nullable(); $table->timestamps(); });
        Schema::create('tickets', function (Blueprint $table) { $table->id(); $table->foreignId('customer_id')->constrained()->cascadeOnDelete(); $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete(); $table->string('title'); $table->text('description')->nullable(); $table->string('status')->default('open'); $table->string('priority')->default('normal'); $table->timestamps(); });
        Schema::create('tasks', function (Blueprint $table) { $table->id(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete(); $table->string('title'); $table->text('description')->nullable(); $table->string('status')->default('todo'); $table->date('due_date')->nullable(); $table->unsignedInteger('estimated_minutes')->nullable(); $table->timestamps(); });
        Schema::create('time_entries', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('project_id')->constrained()->cascadeOnDelete(); $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete(); $table->text('description')->nullable(); $table->dateTime('started_at'); $table->dateTime('ended_at')->nullable(); $table->boolean('billable')->default(true); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('time_entries'); Schema::dropIfExists('tasks'); Schema::dropIfExists('tickets'); Schema::dropIfExists('projects'); Schema::dropIfExists('customers'); }
};
