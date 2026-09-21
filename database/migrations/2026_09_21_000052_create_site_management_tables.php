<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Material Categories
        Schema::create('material_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'name']);
        });

        // 2. Material Entries
        Schema::create('material_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('set null');
            $table->foreignId('category_id')->constrained('material_categories')->onDelete('cascade');
            $table->string('material_name', 150);
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit_of_measure', 30)->default('Units'); // Bags, Brass, Tons, Sq.Ft, Pieces
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->date('entry_date');
            $table->unsignedBigInteger('supplier_vendor_id')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id', 'entry_date']);
            $table->index(['company_id', 'category_id']);
        });

        // 3. Labour Entries
        Schema::create('labour_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('buildings')->onDelete('set null');
            $table->string('labour_identifier', 150); // Name or Labour ID
            $table->string('work_category', 100)->default('General'); // Masonry, Shuttering, Carpenter, Helper, Electrician
            $table->decimal('days_worked', 5, 2)->default(1.00);
            $table->decimal('daily_wage_rate', 10, 2)->default(0);
            $table->decimal('total_wages', 12, 2)->default(0);
            $table->date('work_date');
            $table->foreignId('supervisor_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('payment_status', 30)->default('Pending'); // Pending, Paid, Partial
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id', 'work_date']);
        });

        // 4. Vendor Categories
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 100);
            $table->timestamps();
        });

        // 5. Vendors Master
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('vendor_categories')->onDelete('cascade');
            $table->string('vendor_name', 200);
            $table->string('contact_person', 150)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('gst_number', 50)->nullable();
            $table->string('pan_number', 50)->nullable();
            $table->string('status', 30)->default('Active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'category_id']);
        });

        // 6. Vendor Payments
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->enum('payment_mode', ['Cash', 'Cheque', 'NEFT/RTGS', 'UPI'])->default('Cash');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date');
            $table->string('cheque_number', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('transaction_reference', 100)->nullable();
            $table->decimal('invoice_bill_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'project_id', 'vendor_id']);
            $table->index(['company_id', 'payment_mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('vendor_categories');
        Schema::dropIfExists('labour_entries');
        Schema::dropIfExists('material_entries');
        Schema::dropIfExists('material_categories');
    }
};
