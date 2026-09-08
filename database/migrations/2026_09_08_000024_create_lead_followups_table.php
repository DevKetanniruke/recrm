<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type', 40)->default('Call'); // Call, Email, Meeting, WhatsApp, Site Visit, Other
            $table->dateTime('followup_at');
            $table->text('notes')->nullable();
            $table->string('outcome')->nullable();
            $table->string('next_action')->nullable();
            $table->string('status', 30)->default('Pending'); // Pending, Completed, Missed, Cancelled
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'lead_id']);
            $table->index(['company_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_followups');
    }
};
