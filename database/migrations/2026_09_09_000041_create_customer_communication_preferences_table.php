<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_communication_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('cascade');
            $table->string('recipient', 191);
            $table->string('channel', 30)->default('all'); // email, sms, whatsapp, all
            $table->boolean('opt_in_marketing')->default(true); // false = opted out of marketing
            $table->text('opt_out_reason')->nullable();
            $table->dateTime('opted_out_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'recipient', 'channel'], 'comp_recip_chan_unique');
            $table->index(['company_id', 'recipient']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_communication_preferences');
    }
};
