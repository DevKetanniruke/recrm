<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Company $company;
    protected VendorCategory $plumbingCategory;
    protected VendorCategory $steelCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Acme Builders Pvt Ltd',
            'slug' => 'acme-builders',
        ]);

        $this->admin = User::create([
            'company_id' => $this->company->id,
            'name' => 'Vendor Admin',
            'email' => 'admin@acmebuilders.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        $this->plumbingCategory = VendorCategory::create([
            'company_id' => $this->company->id,
            'name' => 'Plumbing Contractors',
        ]);

        $this->steelCategory = VendorCategory::create([
            'company_id' => $this->company->id,
            'name' => 'Steel Suppliers',
        ]);
    }

    public function test_admin_can_view_vendors_directory()
    {
        Vendor::create([
            'company_id' => $this->company->id,
            'category_id' => $this->plumbingCategory->id,
            'vendor_name' => 'Apex Plumbing Works',
            'mobile' => '9876543210',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('vendors.index'));

        $response->assertStatus(200);
        $response->assertSee('Apex Plumbing Works');
        $response->assertSee('Plumbing Contractors');
    }

    public function test_can_filter_vendors_category_wise()
    {
        $plumbingVendor = Vendor::create([
            'company_id' => $this->company->id,
            'category_id' => $this->plumbingCategory->id,
            'vendor_name' => 'Apex Plumbing Works',
            'status' => 'Active',
        ]);

        $steelVendor = Vendor::create([
            'company_id' => $this->company->id,
            'category_id' => $this->steelCategory->id,
            'vendor_name' => 'Jindal Steel Traders',
            'status' => 'Active',
        ]);

        // Filter by Steel Suppliers Category
        $response = $this->actingAs($this->admin)->get(route('vendors.index', ['category_id' => $this->steelCategory->id]));

        $response->assertStatus(200);
        $response->assertSee('Jindal Steel Traders');
        $response->assertDontSee('Apex Plumbing Works');
    }

    public function test_can_search_vendors_by_name_or_mobile()
    {
        Vendor::create([
            'company_id' => $this->company->id,
            'category_id' => $this->plumbingCategory->id,
            'vendor_name' => 'Apex Plumbing Works',
            'mobile' => '9988776655',
            'status' => 'Active',
        ]);

        Vendor::create([
            'company_id' => $this->company->id,
            'category_id' => $this->steelCategory->id,
            'vendor_name' => 'Tata Steel Agency',
            'mobile' => '1122334455',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('vendors.index', ['search' => 'Tata Steel']));

        $response->assertStatus(200);
        $response->assertSee('Tata Steel Agency');
        $response->assertDontSee('Apex Plumbing Works');
    }

    public function test_can_create_new_vendor()
    {
        $response = $this->actingAs($this->admin)->post(route('vendors.store'), [
            'category_id' => $this->plumbingCategory->id,
            'vendor_name' => 'Unique Sanitary Mart',
            'contact_person' => 'Sunil Gupta',
            'mobile' => '9123456789',
            'email' => 'sunil@uniquesanitary.com',
            'gst_number' => '27ABCDE1234F1Z5',
            'status' => 'Active',
        ]);

        $response->assertRedirect(route('vendors.index'));

        $this->assertDatabaseHas('vendors', [
            'company_id' => $this->company->id,
            'vendor_name' => 'Unique Sanitary Mart',
            'gst_number' => '27ABCDE1234F1Z5',
        ]);
    }

    public function test_can_create_new_vendor_category()
    {
        $response = $this->actingAs($this->admin)->post(route('vendor-categories.store'), [
            'name' => 'Painting & Waterproofing Contractors',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vendor_categories', [
            'company_id' => $this->company->id,
            'name' => 'Painting & Waterproofing Contractors',
        ]);
    }

    public function test_can_view_vendor_profile_details()
    {
        $vendor = Vendor::create([
            'company_id' => $this->company->id,
            'category_id' => $this->plumbingCategory->id,
            'vendor_name' => 'Apex Plumbing Works',
            'mobile' => '9876543210',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('vendors.show', $vendor->id));

        $response->assertStatus(200);
        $response->assertSee('Apex Plumbing Works');
        $response->assertSee('Plumbing Contractors');
    }
}
