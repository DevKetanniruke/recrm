<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'channel_partner_id')) {
                $table->foreignId('channel_partner_id')->nullable()->after('source')->constrained('channel_partners')->onDelete('set null');
            }
            if (!Schema::hasColumn('leads', 'channel_partner_contact_id')) {
                $table->foreignId('channel_partner_contact_id')->nullable()->after('channel_partner_id')->constrained('channel_partner_contacts')->onDelete('set null');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'channel_partner_id')) {
                $table->foreignId('channel_partner_id')->nullable()->after('customer_id')->constrained('channel_partners')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['channel_partner_id']);
            $table->dropForeign(['channel_partner_contact_id']);
            $table->dropColumn(['channel_partner_id', 'channel_partner_contact_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['channel_partner_id']);
            $table->dropColumn(['channel_partner_id']);
        });
    }
};
