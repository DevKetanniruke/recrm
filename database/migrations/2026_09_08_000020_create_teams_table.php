<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['team_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
    }
};
