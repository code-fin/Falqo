<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
            $table->boolean('time_bookmarked')->default(false)->after('priority');
        });
        Schema::table('tasks', fn (Blueprint $table) => $table->foreignId('assigned_user_id')->nullable()->after('ticket_id')->constrained('users')->nullOnDelete());
        Schema::table('time_entries', fn (Blueprint $table) => $table->timestamp('booked_at')->nullable()->after('ended_at'));
        DB::table('tickets')->orderBy('id')->eachById(function (object $ticket): void {
            $userId = DB::table('customers')->where('id', $ticket->customer_id)->value('user_id');
            DB::table('tickets')->where('id', $ticket->id)->update(['assigned_user_id' => $userId, 'time_bookmarked' => true]);
        });
        DB::table('tasks')->orderBy('id')->eachById(function (object $task): void {
            $userId = DB::table('projects')->join('customers', 'customers.id', '=', 'projects.customer_id')->where('projects.id', $task->project_id)->value('customers.user_id');
            DB::table('tasks')->where('id', $task->id)->update(['assigned_user_id' => $userId]);
        });
        DB::table('time_entries')->whereNotNull('ended_at')->update(['booked_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('time_entries', fn (Blueprint $table) => $table->dropColumn('booked_at'));
        Schema::table('tasks', fn (Blueprint $table) => $table->dropConstrainedForeignId('assigned_user_id'));
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropColumn('time_bookmarked');
        });
    }
};
