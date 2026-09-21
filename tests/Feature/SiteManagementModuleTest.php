<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MaterialCategory;
use App\Models\MaterialEntry;
use App\Models\LabourEntry;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\VendorPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Company $company;
    protected Project $project;
    protected MaterialCategory $materialCategory;
    protected VendorCategory $vendorCategory;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Apex Infra Corp',
            'slug' => 'apex-infra-corp',
        ]);

        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name' => 'Site Manager Admin',
            'email' => 'sitemanager@apexinfra.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        $this->project = Project::create([
            'company_id' => $this->company->id,
            'project_name' => 'Apex Crest Horizon',
            'project_status' => 'Under Construction',
        ]);

        $this->materialCategory = MaterialCategory::create([
            'company_id' => $this->company->id,
            'name' => 'Bandhkam (Cement & Crusher)',
        ]);

        $this->vendorCategory = VendorCategory::create([
            'company_id' => $this->company->id,
            'name' => 'Plumbing Contractors',
        ]);

        $this->vendor = Vendor::create([
            'company_id' => $this->company->id,
            'category_id' => $this->vendorCategory->id,
            'vendor_name' => 'Super Plumbing Solutions',
            'mobile' => '9876543210',
        ]);
    }

    public function test_admin_can_view_site_management_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('projects.site-management', $this->project->id));

        $response->assertStatus(200);
        $response->assertSee('Apex Crest Horizon');
        $response->assertSee('Site Materials, Labour');
    }

    public function test_admin_can_record_daily_material_entry_and_calculate_total_cost()
    {
        $response = $this->actingAs($this->admin)->post(route('projects.materials.store', $this->project->id), [
            'category_id' => $this->materialCategory->id,
            'material_name' => 'Ultratech PPC Cement',
            'quantity' => 200,
            'unit_of_measure' => 'Bags',
            'unit_cost' => 350.00,
            'entry_date' => date('Y-m-d'),
        ]);

        $response->assertRedirect(route('projects.site-management', [$this->project->id, 'tab' => 'materials']));

        $this->assertDatabaseHas('material_entries', [
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'material_name' => 'Ultratech PPC Cement',
            'quantity' => 200,
            'unit_cost' => 350.00,
            'total_cost' => 70000.00,
        ]);
    }

    public function test_admin_can_record_labour_entry_and_calculate_total_wages()
    {
        $response = $this->actingAs($this->admin)->post(route('projects.labour.store', $this->project->id), [
            'labour_identifier' => 'Shuttering Team A',
            'work_category' => 'Shuttering',
            'days_worked' => 1.5,
            'daily_wage_rate' => 900.00,
            'work_date' => date('Y-m-d'),
            'payment_status' => 'Pending',
        ]);

        $response->assertRedirect(route('projects.site-management', [$this->project->id, 'tab' => 'labour']));

        $this->assertDatabaseHas('labour_entries', [
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'labour_identifier' => 'Shuttering Team A',
            'days_worked' => 1.5,
            'daily_wage_rate' => 900.00,
            'total_wages' => 1350.00,
        ]);
    }

    public function test_admin_can_record_vendor_cash_and_cheque_payments()
    {
        // Cash Payment
        $this->actingAs($this->admin)->post(route('projects.vendor-payments.store', $this->project->id), [
            'vendor_id' => $this->vendor->id,
            'payment_mode' => 'Cash',
            'amount' => 15000.00,
            'invoice_bill_amount' => 50000.00,
            'payment_date' => date('Y-m-d'),
        ]);

        // Cheque Payment
        $this->actingAs($this->admin)->post(route('projects.vendor-payments.store', $this->project->id), [
            'vendor_id' => $this->vendor->id,
            'payment_mode' => 'Cheque',
            'amount' => 25000.00,
            'cheque_number' => 'CHQ-99120',
            'invoice_bill_amount' => 0,
            'payment_date' => date('Y-m-d'),
        ]);

        $this->assertDatabaseHas('vendor_payments', [
            'vendor_id' => $this->vendor->id,
            'payment_mode' => 'Cash',
            'amount' => 15000.00,
        ]);

        $this->assertDatabaseHas('vendor_payments', [
            'vendor_id' => $this->vendor->id,
            'payment_mode' => 'Cheque',
            'amount' => 25000.00,
            'cheque_number' => 'CHQ-99120',
        ]);

        $this->assertEquals(15000.00, $this->vendor->getCashPaidForProject($this->project->id));
        $this->assertEquals(25000.00, $this->vendor->getChequePaidForProject($this->project->id));
        $this->assertEquals(40000.00, $this->vendor->getTotalPaidForProject($this->project->id));
        $this->assertEquals(10000.00, $this->vendor->getRemainingDueForProject($this->project->id));
    }

    public function test_exports_and_printable_pdf_work()
    {
        // Excel CSV stream export
        $excelResponse = $this->actingAs($this->admin)->get(route('projects.site-management.export-excel', [$this->project->id, 'export_type' => 'materials']));
        $excelResponse->assertStatus(200);

        // Printable PDF view
        $pdfResponse = $this->actingAs($this->admin)->get(route('projects.site-management.export-pdf', [$this->project->id, 'vendor_id' => $this->vendor->id]));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('Super Plumbing Solutions');
    }
}
