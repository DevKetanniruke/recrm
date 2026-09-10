<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->foreignId('wing_id')->constrained('wings')->onDelete('cascade');
            $table->foreignId('floor_id')->constrained('floors')->onDelete('cascade');
            $table->unsignedBigInteger('unit_type_id')->nullable()->index();
            
            $table->string('unit_number');
            $table->string('unit_code')->nullable();
            
            $table->decimal('carpet_area', 10, 2)->default(0);
            $table->decimal('built_up_area', 10, 2)->default(0);
            $table->decimal('super_built_up_area', 10, 2)->default(0);
            $table->decimal('balcony_area', 10, 2)->default(0);
            $table->decimal('terrace_area', 10, 2)->default(0);
            
            $table->integer('bedrooms')->default(2);
            $table->integer('bathrooms')->default(2);
            $table->string('facing', 50)->default('East'); // North, East, South, West, North-East, etc.
            $table->string('parking', 50)->default('Covered'); // Covered, Open, None, Double Covered
            
            $table->string('RERA_unit_number', 100)->nullable();
            $table->string('possession_status', 50)->default('Under Construction'); // Ready to Move, Under Construction, Immediate
            $table->string('inventory_status', 30)->default('Available'); // Available, Hold, Booked, Sold, Cancelled, Blocked

            // Backward compatibility helper column
            $table->string('unit_type', 50)->nullable();
            $table->decimal('carpet_area_sqft', 10, 2)->nullable();
            $table->decimal('super_builtup_area_sqft', 10, 2)->nullable();
            $table->decimal('base_rate_per_sqft', 12, 2)->nullable();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('status', 30)->default('Available');
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'inventory_status']);
            $table->index(['project_id', 'building_id', 'floor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
