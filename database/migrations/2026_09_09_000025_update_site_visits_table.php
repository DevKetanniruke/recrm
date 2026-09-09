<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->string('visit_number', 50)->nullable()->after('id');
            $table->string('transportation_type', 30)->default('Self')->after('assigned_to'); // Self, Company Cab, Uber
            $table->string('driver_name', 100)->nullable()->after('transportation_type');
            $table->string('driver_phone', 20)->nullable()->after('driver_name');
            $table->string('pickup_location', 255)->nullable()->after('driver_phone');
            $table->dateTime('pickup_time')->nullable()->after('pickup_location');
            $table->dateTime('check_in_at')->nullable()->after('pickup_time');
            $table->dateTime('check_out_at')->nullable()->after('check_in_at');
            $table->decimal('check_in_lat', 10, 8)->nullable()->after('check_out_at');
            $table->decimal('check_in_lng', 11, 8)->nullable()->after('check_in_lat');
            $table->decimal('check_out_lat', 10, 8)->nullable()->after('check_in_lng');
            $table->decimal('check_out_lng', 11, 8)->nullable()->after('check_out_lat');
            $table->json('units_viewed')->nullable()->after('check_out_lng');
            $table->text('executive_notes')->nullable()->after('units_viewed');
        });
    }

    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn([
                'visit_number',
                'transportation_type',
                'driver_name',
                'driver_phone',
                'pickup_location',
                'pickup_time',
                'check_in_at',
                'check_out_at',
                'check_in_lat',
                'check_in_lng',
                'check_out_lat',
                'check_out_lng',
                'units_viewed',
                'executive_notes',
            ]);
        });
    }
};
