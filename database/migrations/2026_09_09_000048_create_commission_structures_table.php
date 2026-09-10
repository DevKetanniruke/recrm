<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->foreignId('unit_type_id')->nullable()->constrained('unit_types')->onDelete('set null');
            $table->string('name', 150);
            $table->string('calculation_type', 50)->default('percentage'); // percentage, fixed_amount, slab_based, milestone_based
            $table->decimal('rate', 5, 2)->default(0.00); // e.g. 2.50%
            $table->decimal('fixed_amount', 15, 2)->default(0.00);
            $table->json('slabs_json')->nullable(); // array of [{min_bookings, max_bookings, percentage_rate, bonus_amount}]
            $table->json('milestone_triggers_json')->nullable(); // e.g. [{milestone_code: 'BOOKING', release_percentage: 50}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_structures');
    }
};
