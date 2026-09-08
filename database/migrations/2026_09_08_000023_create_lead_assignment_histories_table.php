<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_assignment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('previous_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('previous_team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_assignment_histories');
    }
};
