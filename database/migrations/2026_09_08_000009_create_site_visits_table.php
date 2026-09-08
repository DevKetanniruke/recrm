<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('visit_date');
            $table->string('status', 30)->default('Scheduled'); // Scheduled, Completed, Cancelled, No-Show
            $table->text('feedback')->nullable();
            $table->string('rating', 20)->nullable(); // Interested, High Potential, Not Interested
            $table->timestamps();

            $table->index(['company_id', 'visit_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
