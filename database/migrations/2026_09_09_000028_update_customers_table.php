<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
            if (!Schema::hasColumn('customers', 'customer_number')) {
                $table->string('customer_number', 50)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('customers', 'middle_name')) {
                $table->string('middle_name', 100)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('customers', 'mobile')) {
                $table->string('mobile', 20)->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('customers', 'alternate_mobile')) {
                $table->string('alternate_mobile', 20)->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('customers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('email');
            }
            if (!Schema::hasColumn('customers', 'occupation')) {
                $table->string('occupation', 100)->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('customers', 'company_or_employer')) {
                $table->string('company_or_employer', 150)->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('customers', 'nationality')) {
                $table->string('nationality', 50)->default('Indian')->after('company_or_employer');
            }
            if (!Schema::hasColumn('customers', 'pincode')) {
                $table->string('pincode', 20)->nullable()->after('city');
            }
            if (!Schema::hasColumn('customers', 'country')) {
                $table->string('country', 50)->default('India')->after('pincode');
            }
            if (!Schema::hasColumn('customers', 'PAN')) {
                $table->string('PAN', 20)->nullable()->after('country');
            }
            if (!Schema::hasColumn('customers', 'reference')) {
                $table->string('reference', 100)->nullable()->after('PAN');
            }
            if (!Schema::hasColumn('customers', 'communication_preference')) {
                $table->string('communication_preference', 30)->default('Email')->after('reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'customer_number',
                'middle_name',
                'mobile',
                'alternate_mobile',
                'date_of_birth',
                'occupation',
                'company_or_employer',
                'nationality',
                'pincode',
                'country',
                'PAN',
                'reference',
                'communication_preference',
            ]);
        });
    }
};
