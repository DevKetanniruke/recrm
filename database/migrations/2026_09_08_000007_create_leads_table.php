<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('lead_number')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('alternate_mobile', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('location', 255)->nullable();
            
            $table->string('source', 50)->default('Website');
            $table->foreignId('source_id')->nullable()->constrained('lead_sources')->onDelete('set null');
            $table->string('campaign', 100)->nullable();
            
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('unit_type', 50)->nullable();
            $table->decimal('minimum_budget', 15, 2)->nullable();
            $table->decimal('maximum_budget', 15, 2)->nullable();
            $table->string('preferred_floor', 50)->nullable();
            $table->string('preferred_facing', 50)->nullable();
            $table->string('purchase_timeline', 50)->nullable();
            
            $table->string('priority', 20)->default('Medium');
            $table->string('status', 50)->default('New');
            $table->foreignId('status_id')->nullable()->constrained('lead_statuses')->onDelete('set null');
            
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_team_id')->nullable()->constrained('teams')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->foreignId('merged_into_lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'assigned_to']);
            $table->index(['company_id', 'assigned_team_id']);
            $table->index(['company_id', 'mobile']);
            $table->index(['company_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
