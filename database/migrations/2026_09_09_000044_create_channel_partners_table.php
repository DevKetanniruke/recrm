<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('partner_code', 50)->unique();
            $table->string('company_name', 150);
            $table->string('contact_person', 100);
            $table->string('mobile', 20);
            $table->string('email', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('gst_number', 50)->nullable();
            $table->string('pan_number', 50)->nullable();
            $table->string('rera_registration_number', 100)->nullable();
            $table->json('bank_account_details_json')->nullable();
            $table->string('status', 30)->default('Active'); // Active, Inactive, Suspended, Blacklisted
            $table->date('onboarding_date')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'partner_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_partners');
    }
};
