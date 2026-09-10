<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'idx_users_company_status');
            $table->index(['company_id', 'role'], 'idx_users_company_role');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'idx_projects_company_status');
        });

        Schema::table('buildings', function (Blueprint $table) {
            $table->index(['company_id', 'project_id'], 'idx_buildings_company_project');
        });

        Schema::table('wings', function (Blueprint $table) {
            $table->index(['company_id', 'building_id'], 'idx_wings_company_building');
        });

        Schema::table('floors', function (Blueprint $table) {
            $table->index(['company_id', 'wing_id'], 'idx_floors_company_wing');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->index(['company_id', 'project_id', 'status'], 'idx_units_company_project_status');
            $table->index(['company_id', 'building_id'], 'idx_units_company_building');
            $table->index(['company_id', 'wing_id'], 'idx_units_company_wing');
            $table->index(['company_id', 'unit_type_id'], 'idx_units_company_unittype');
            $table->index(['company_id', 'unit_number'], 'idx_units_company_number');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'idx_leads_company_status');
            $table->index(['company_id', 'assigned_to'], 'idx_leads_company_assigned');
            $table->index(['company_id', 'project_id'], 'idx_leads_company_project');
            $table->index(['company_id', 'source'], 'idx_leads_company_source');
            $table->index(['company_id', 'mobile'], 'idx_leads_company_mobile');
            $table->index(['company_id', 'email'], 'idx_leads_company_email');
            $table->index(['company_id', 'lead_number'], 'idx_leads_company_number');
            $table->index(['company_id', 'created_at'], 'idx_leads_company_created');
        });

        Schema::table('lead_activities', function (Blueprint $table) {
            $table->index(['company_id', 'lead_id'], 'idx_lead_activities_company_lead');
            $table->index(['company_id', 'user_id'], 'idx_lead_activities_company_user');
        });

        Schema::table('site_visits', function (Blueprint $table) {
            $table->index(['company_id', 'project_id', 'status'], 'idx_site_visits_company_proj_status');
            $table->index(['company_id', 'lead_id'], 'idx_site_visits_company_lead');
            $table->index(['company_id', 'assigned_to'], 'idx_site_visits_company_assigned');
            $table->index(['company_id', 'visit_date'], 'idx_site_visits_company_date');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index(['company_id', 'lead_id'], 'idx_customers_company_lead');
            $table->index(['company_id', 'customer_number'], 'idx_customers_company_number');
            $table->index(['company_id', 'mobile'], 'idx_customers_company_mobile');
            $table->index(['company_id', 'email'], 'idx_customers_company_email');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'idx_bookings_company_status');
            $table->index(['company_id', 'project_id'], 'idx_bookings_company_project');
            $table->index(['company_id', 'unit_id'], 'idx_bookings_company_unit');
            $table->index(['company_id', 'customer_id'], 'idx_bookings_company_customer');
            $table->index(['company_id', 'sales_agent_id'], 'idx_bookings_company_agent');
            $table->index(['company_id', 'channel_partner_id'], 'idx_bookings_company_partner');
            $table->index(['company_id', 'booking_number'], 'idx_bookings_company_number');
            $table->index(['company_id', 'created_at'], 'idx_bookings_company_created');
        });

        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->index(['company_id', 'booking_id', 'status'], 'idx_payment_schedules_comp_bkg_stat');
            $table->index(['company_id', 'due_date'], 'idx_payment_schedules_comp_due');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['company_id', 'booking_id', 'status'], 'idx_payments_company_booking_status');
            $table->index(['company_id', 'receipt_number'], 'idx_payments_company_receipt');
            $table->index(['company_id', 'payment_mode'], 'idx_payments_company_mode');
            $table->index(['company_id', 'payment_date'], 'idx_payments_company_date');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'idx_offers_company_status');
            $table->index(['company_id', 'lead_id'], 'idx_offers_company_lead');
            $table->index(['company_id', 'project_id'], 'idx_offers_company_project');
            $table->index(['company_id', 'unit_id'], 'idx_offers_company_unit');
        });

        Schema::table('channel_partners', function (Blueprint $table) {
            $table->index(['company_id', 'status'], 'idx_channel_partners_company_status');
            $table->index(['company_id', 'partner_code'], 'idx_channel_partners_company_code');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->index(['company_id', 'channel_partner_id', 'status'], 'idx_commissions_comp_partner_stat');
            $table->index(['company_id', 'booking_id'], 'idx_commissions_company_booking');
        });

        Schema::table('communication_logs', function (Blueprint $table) {
            $table->index(['company_id', 'channel', 'status'], 'idx_comm_logs_company_chan_stat');
            $table->index(['company_id', 'lead_id'], 'idx_comm_logs_company_lead');
            $table->index(['company_id', 'customer_id'], 'idx_comm_logs_company_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback dropped indexes
        Schema::table('users', fn (Blueprint $table) => $table->dropIndex('idx_users_company_status'));
        Schema::table('projects', fn (Blueprint $table) => $table->dropIndex('idx_projects_company_status'));
        Schema::table('units', fn (Blueprint $table) => $table->dropIndex('idx_units_company_project_status'));
        Schema::table('leads', fn (Blueprint $table) => $table->dropIndex('idx_leads_company_status'));
        Schema::table('bookings', fn (Blueprint $table) => $table->dropIndex('idx_bookings_company_status'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropIndex('idx_payments_company_booking_status'));
    }
};
