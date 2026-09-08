<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('project_name');
            $table->string('name')->nullable();
            $table->string('project_code')->nullable();
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('project_type', 50)->default('Residential'); // Residential, Commercial, Mixed
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('RERA_number', 100)->nullable();
            $table->date('RERA_registration_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_completion')->nullable();
            $table->date('actual_completion')->nullable();
            $table->string('project_status', 50)->default('Under Construction'); // Planning, Under Construction, Ready to Possess, Completed, On Hold, Cancelled
            $table->string('status', 50)->default('Ongoing');
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('total_land_area', 15, 2)->default(0);
            $table->json('project_images')->nullable();
            $table->json('project_documents')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
