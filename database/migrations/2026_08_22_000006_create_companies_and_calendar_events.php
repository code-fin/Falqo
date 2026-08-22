<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('users', fn (Blueprint $table) => $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete());

        DB::table('users')->orderBy('id')->eachById(function (object $user): void {
            $companyId = DB::table('companies')->insertGetId(['name' => $user->name.' Workspace', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('users')->where('id', $user->id)->update(['company_id' => $companyId]);
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('calendar_event_user', function (Blueprint $table) {
            $table->foreignId('calendar_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['calendar_event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_user');
        Schema::dropIfExists('calendar_events');
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('company_id'));
        Schema::dropIfExists('companies');
    }
};
