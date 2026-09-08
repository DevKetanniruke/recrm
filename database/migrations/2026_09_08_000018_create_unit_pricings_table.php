<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('rate_per_sqft', 12, 2)->default(0);
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('floor_rise_rate', 10, 2)->default(0);
            $table->decimal('facing_premium', 12, 2)->default(0);
            $table->decimal('plc_amount', 12, 2)->default(0); // Preferential Location Charges
            $table->decimal('parking_charges', 12, 2)->default(0);
            $table->decimal('clubhouse_charges', 12, 2)->default(0);
            $table->decimal('infrastructure_charges', 12, 2)->default(0);
            $table->decimal('maintenance_deposit', 12, 2)->default(0);
            $table->decimal('legal_charges', 12, 2)->default(0);
            $table->decimal('gst_percent', 5, 2)->default(5.00); // e.g. 5% GST
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('calculated_total_price', 15, 2)->default(0);
            $table->timestamps();

            $table->unique('unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_pricings');
    }
};
