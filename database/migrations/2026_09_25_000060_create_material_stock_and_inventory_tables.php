<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Material Stocks Inventory Table
        Schema::create('material_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('material_categories')->onDelete('cascade');
            $table->string('material_name', 150);
            $table->string('unit_of_measure', 30)->default('Units');
            $table->decimal('current_stock_qty', 12, 3)->default(0);
            $table->decimal('min_threshold_qty', 12, 3)->default(10.000);
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->timestamp('last_replenished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id', 'category_id']);
        });

        // 2. Material Inward Replenishments Table
        Schema::create('material_inwards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('stock_id')->nullable()->constrained('material_stocks')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('material_categories')->onDelete('cascade');
            $table->string('material_name', 150);
            $table->decimal('qty_received', 12, 3)->default(0);
            $table->string('unit_of_measure', 30)->default('Units');
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('total_cost', 15, 2)->default(0.00);
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->string('gate_pass_number', 100)->nullable();
            $table->date('received_date');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id', 'received_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_inwards');
        Schema::dropIfExists('material_stocks');
    }
};
