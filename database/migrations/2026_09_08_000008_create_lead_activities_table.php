<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('activity_type', 40)->default('Call'); // Call, WhatsApp, Email, SMS, Meeting, Note, Follow-up, Site Visit, Status Change, Assignment, Other
            $table->string('subject')->nullable();
            $table->text('summary')->nullable();
            $table->string('outcome')->nullable();
            $table->string('next_action')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('next_followup_at')->nullable();
            $table->string('status', 30)->default('Completed'); // Pending, Completed, Cancelled
            $table->timestamps();

            $table->index(['company_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
