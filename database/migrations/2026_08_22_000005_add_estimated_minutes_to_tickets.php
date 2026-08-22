<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', fn (Blueprint $table) => $table->unsignedInteger('estimated_minutes')->default(0)->after('priority'));
    }

    public function down(): void
    {
        Schema::table('tickets', fn (Blueprint $table) => $table->dropColumn('estimated_minutes'));
    }
};
