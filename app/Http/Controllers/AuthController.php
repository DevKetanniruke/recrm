<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Seed V0.1 default company, roles, permissions, and super admin if database is empty
        if (Company::count() === 0) {
            $company = Company::create([
                'name' => 'PropFlow Real Estate Corp',
                'legal_name' => 'PropFlow Realty Solutions LLC',
                'slug' => 'propflow-corp',
                'email' => 'contact@propflow.com',
                'phone' => '+1 (555) 019-2831',
                'website' => 'https://propflow.com',
                'address' => '100 Executive Tower Parkway, Suite 500',
                'city' => 'Austin',
                'state' => 'Texas',
                'pincode' => '78701',
                'country' => 'USA',
                'default_timezone' => 'America/Chicago',
                'currency_code' => 'USD',
            ]);

            // Seed Permissions List
            $permissionsList = [
                ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'dashboard'],
                ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users'],
                ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'users'],
                ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'users'],
                ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'users'],
                ['name' => 'View Projects', 'slug' => 'projects.view', 'module' => 'projects'],
                ['name' => 'Create Projects', 'slug' => 'projects.create', 'module' => 'projects'],
                ['name' => 'Edit Projects', 'slug' => 'projects.edit', 'module' => 'projects'],
                ['name' => 'Delete Projects', 'slug' => 'projects.delete', 'module' => 'projects'],
                ['name' => 'View Leads', 'slug' => 'leads.view', 'module' => 'leads'],
                ['name' => 'Create Leads', 'slug' => 'leads.create', 'module' => 'leads'],
                ['name' => 'Edit Leads', 'slug' => 'leads.edit', 'module' => 'leads'],
                ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'module' => 'leads'],
                ['name' => 'View Bookings', 'slug' => 'bookings.view', 'module' => 'bookings'],
                ['name' => 'Create Bookings', 'slug' => 'bookings.create', 'module' => 'bookings'],
                ['name' => 'Edit Bookings', 'slug' => 'bookings.edit', 'module' => 'bookings'],
                ['name' => 'View Payments', 'slug' => 'payments.view', 'module' => 'payments'],
                ['name' => 'Create Payments', 'slug' => 'payments.create', 'module' => 'payments'],
                ['name' => 'Edit Payments', 'slug' => 'payments.edit', 'module' => 'payments'],
                ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports'],
                ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'reports'],
                ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory'],
            ];

            foreach ($permissionsList as $p) {
                Permission::firstOrCreate(['slug' => $p['slug']], $p);
            }

            // Seed Configured V0.1 Roles
            $rolesList = [
                'super_admin' => 'Super Admin',
                'admin' => 'Admin',
                'management' => 'Management',
                'sales_manager' => 'Sales Manager',
                'sales_executive' => 'Sales Executive',
                'telecaller' => 'Telecaller',
                'crm_manager' => 'CRM Manager',
                'crm_executive' => 'CRM Executive',
                'accounts_manager' => 'Accounts Manager',
                'accounts_executive' => 'Accounts Executive',
            ];

            foreach ($rolesList as $slug => $roleName) {
                $role = Role::create([
                    'company_id' => $company->id,
                    'name' => $roleName,
                    'slug' => $slug,
                    'description' => "Default {$roleName} role",
                ]);

                // Attach all permissions to Super Admin & Admin
                if (in_array($slug, ['super_admin', 'admin'])) {
                    $role->permissions()->sync(Permission::all());
                }
            }

            // Seed Super Admin User
            $superAdmin = User::create([
                'company_id' => $company->id,
                'name' => 'System Admin',
                'email' => 'admin@recrm.com',
                'mobile' => '+1 (555) 019-0001',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'Active',
            ]);

            $adminRole = Role::where('slug', 'super_admin')->first();
            if ($adminRole) {
                $superAdmin->roles()->attach($adminRole->id);
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->isActive()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account status is currently ' . $user->status . '. Please contact administrator.']);
            }

            // Update last_login_at timestamp
            $user->update(['last_login_at' => now()]);

            // Log Login Audit Event
            AuditLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event' => 'login',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'new_values' => ['last_login_at' => now()->toDateTimeString()],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            AuditLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'event' => 'logout',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        return back()->with('status', 'If an account exists for this email, password reset instructions have been dispatched.');
    }
}
