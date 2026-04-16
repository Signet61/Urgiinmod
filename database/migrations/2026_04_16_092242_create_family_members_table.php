<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('rel');                                   // gpl,gml,gpr,gmr,dad,mom,uncle,aunt,sib,me,cousin
            $table->string('emoji')->default('image/jaal_huu.png'); // avatar image path
            $table->string('photo')->nullable();                     // uploaded photo (storage path)
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
