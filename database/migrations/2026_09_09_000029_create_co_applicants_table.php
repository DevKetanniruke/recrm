<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('co_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('customer_name', 150);
            $table->string('relationship', 50); // Spouse, Parent, Child, Sibling, Business Partner, Co-Applicant
            $table->string('mobile', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->decimal('ownership_percentage', 5, 2)->default(0.00);
            $table->string('applicant_type', 30)->default('Co-Applicant'); // Primary, Co-Applicant
            $table->string('pan_number', 20)->nullable();
            $table->string('aadhaar_number', 20)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('co_applicants');
    }
};
