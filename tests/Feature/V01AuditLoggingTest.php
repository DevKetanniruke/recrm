<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V01AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_changes_create_audit_logs_with_old_and_new_values(): void
    {
        $company = Company::create(['name' => 'Original Name', 'slug' => 'orig-slug']);
        $user = User::create([
            'company_id' => $company->id,
            'name' => 'Auditor',
            'email' => 'auditor@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'Active',
        ]);

        $this->actingAs($user);

        // Update company name
        $company->update(['name' => 'Updated Legal Corp']);

        $auditLog = AuditLog::where('auditable_type', Company::class)
            ->where('auditable_id', $company->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('Original Name', $auditLog->old_values['name']);
        $this->assertEquals('Updated Legal Corp', $auditLog->new_values['name']);
    }
}
