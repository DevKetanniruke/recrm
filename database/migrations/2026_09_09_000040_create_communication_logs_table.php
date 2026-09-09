<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null');
            $table->foreignId('communication_template_id')->nullable()->constrained('communication_templates')->onDelete('set null');
            $table->string('channel', 30); // email, sms, whatsapp, in_app
            $table->string('recipient', 191);
            $table->string('subject')->nullable();
            $table->text('message_body');
            $table->string('status', 30)->default('Queued'); // Queued, Sent, Delivered, Failed, OptedOut
            $table->boolean('is_transactional')->default(false);
            $table->string('provider_name', 50)->nullable();
            $table->string('provider_reference', 100)->nullable();
            $table->text('failure_reason')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'channel', 'status']);
            $table->index(['company_id', 'recipient']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};
