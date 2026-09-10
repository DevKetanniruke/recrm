<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Helper to safely add an index if all required columns exist on table.
     */
    private function addIndexIfColumnsExist(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Throwable $e) {
            // Index may already exist
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexIfColumnsExist('users', ['company_id', 'status'], 'idx_users_company_status');
        $this->addIndexIfColumnsExist('users', ['company_id', 'role'], 'idx_users_company_role');

        $this->addIndexIfColumnsExist('projects', ['company_id', 'status'], 'idx_projects_company_status');

        $this->addIndexIfColumnsExist('buildings', ['project_id'], 'idx_buildings_project');
        $this->addIndexIfColumnsExist('wings', ['building_id'], 'idx_wings_building');
        $this->addIndexIfColumnsExist('floors', ['wing_id'], 'idx_floors_wing');

        $this->addIndexIfColumnsExist('units', ['company_id', 'project_id', 'status'], 'idx_units_company_project_status');
        $this->addIndexIfColumnsExist('units', ['company_id', 'building_id'], 'idx_units_company_building');
        $this->addIndexIfColumnsExist('units', ['company_id', 'wing_id'], 'idx_units_company_wing');
        $this->addIndexIfColumnsExist('units', ['company_id', 'unit_type_id'], 'idx_units_company_unittype');
        $this->addIndexIfColumnsExist('units', ['company_id', 'unit_number'], 'idx_units_company_number');

        $this->addIndexIfColumnsExist('leads', ['company_id', 'status'], 'idx_leads_company_status');
        $this->addIndexIfColumnsExist('leads', ['company_id', 'assigned_to'], 'idx_leads_company_assigned');
        $this->addIndexIfColumnsExist('leads', ['company_id', 'project_id'], 'idx_leads_company_project');
        $this->addIndexIfColumnsExist('leads', ['company_id', 'source'], 'idx_leads_company_source');
        $this->addIndexIfColumnsExist('leads', ['company_id', 'mobile'], 'idx_leads_company_mobile');
        $this->addIndexIfColumnsExist('leads', ['company_id', 'email'], 'idx_leads_company_email');
        $this->addIndexIfColumnsExist('leads', ['company_id', 'lead_number'], 'idx_leads_company_number');
        $this->addIndexIfColumnsExist('leads', ['company_id', 'created_at'], 'idx_leads_company_created');

        $this->addIndexIfColumnsExist('lead_activities', ['company_id', 'lead_id'], 'idx_lead_activities_company_lead');
        $this->addIndexIfColumnsExist('lead_activities', ['company_id', 'user_id'], 'idx_lead_activities_company_user');

        $this->addIndexIfColumnsExist('site_visits', ['company_id', 'project_id', 'status'], 'idx_site_visits_company_proj_status');
        $this->addIndexIfColumnsExist('site_visits', ['company_id', 'lead_id'], 'idx_site_visits_company_lead');
        $this->addIndexIfColumnsExist('site_visits', ['company_id', 'assigned_to'], 'idx_site_visits_company_assigned');
        $this->addIndexIfColumnsExist('site_visits', ['company_id', 'visit_date'], 'idx_site_visits_company_date');

        $this->addIndexIfColumnsExist('customers', ['company_id', 'lead_id'], 'idx_customers_company_lead');
        $this->addIndexIfColumnsExist('customers', ['company_id', 'phone'], 'idx_customers_company_phone');
        $this->addIndexIfColumnsExist('customers', ['company_id', 'email'], 'idx_customers_company_email');

        $this->addIndexIfColumnsExist('bookings', ['company_id', 'status'], 'idx_bookings_company_status');
        $this->addIndexIfColumnsExist('bookings', ['company_id', 'unit_id'], 'idx_bookings_company_unit');
        $this->addIndexIfColumnsExist('bookings', ['company_id', 'customer_id'], 'idx_bookings_company_customer');
        $this->addIndexIfColumnsExist('bookings', ['company_id', 'sales_agent_id'], 'idx_bookings_company_agent');
        $this->addIndexIfColumnsExist('bookings', ['company_id', 'booking_number'], 'idx_bookings_company_number');
        $this->addIndexIfColumnsExist('bookings', ['company_id', 'created_at'], 'idx_bookings_company_created');

        $this->addIndexIfColumnsExist('payment_schedules', ['company_id', 'booking_id', 'status'], 'idx_payment_schedules_comp_bkg_stat');
        $this->addIndexIfColumnsExist('payment_schedules', ['company_id', 'due_date'], 'idx_payment_schedules_comp_due');

        $this->addIndexIfColumnsExist('payments', ['company_id', 'booking_id', 'status'], 'idx_payments_company_booking_status');
        $this->addIndexIfColumnsExist('payments', ['company_id', 'receipt_number'], 'idx_payments_company_receipt');
        $this->addIndexIfColumnsExist('payments', ['company_id', 'payment_method'], 'idx_payments_company_method');
        $this->addIndexIfColumnsExist('payments', ['company_id', 'payment_date'], 'idx_payments_company_date');

        $this->addIndexIfColumnsExist('offers', ['company_id', 'status'], 'idx_offers_company_status');
        $this->addIndexIfColumnsExist('offers', ['company_id', 'lead_id'], 'idx_offers_company_lead');
        $this->addIndexIfColumnsExist('offers', ['company_id', 'unit_id'], 'idx_offers_company_unit');

        $this->addIndexIfColumnsExist('channel_partners', ['company_id', 'status'], 'idx_channel_partners_company_status');
        $this->addIndexIfColumnsExist('channel_partners', ['company_id', 'partner_code'], 'idx_channel_partners_company_code');

        $this->addIndexIfColumnsExist('commissions', ['company_id', 'channel_partner_id', 'status'], 'idx_commissions_comp_partner_stat');
        $this->addIndexIfColumnsExist('commissions', ['company_id', 'booking_id'], 'idx_commissions_company_booking');

        $this->addIndexIfColumnsExist('communication_logs', ['company_id', 'channel', 'status'], 'idx_comm_logs_company_chan_stat');
        $this->addIndexIfColumnsExist('communication_logs', ['company_id', 'lead_id'], 'idx_comm_logs_company_lead');
        $this->addIndexIfColumnsExist('communication_logs', ['company_id', 'customer_id'], 'idx_comm_logs_company_customer');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback gracefully
    }
};

