<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_negotiation_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
            $table->integer('round_number')->default(1);
            $table->string('offered_by', 20); // Buyer, Builder
            $table->foreignId('proposed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('proposed_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('requested_token_amount', 15, 2)->default(0);
            $table->text('payment_terms')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'offer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_negotiation_rounds');
    }
};
