<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->foreignId('related_to_id')->nullable()->after('rel')->constrained('family_members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_to_id');
        });
    }
};
