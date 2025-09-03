<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permissions
        $this->createRolesAndPermissions();
    }

    /**
     * Test complete user management workflow
     */
    public function test_complete_user_management_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            $browser->loginAs($admin)
                ->visit('/admin/dashboard');

            // Navigate to user management
            $browser->clickLink('Users')
                ->assertPathIs('/admin/users')
                ->click('@add-user-btn')
                ->assertPathIs('/admin/users/create');

            // Create new user
            $browser->type('name', 'Test User')
                ->type('email', 'testuser@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->select('role', 'accountant')
                ->press('Create User')
                ->assertSee('User created successfully')
                ->assertPathIs('/admin/users');

            // Verify user appears in list
            $browser->assertSee('Test User')
                ->assertSee('testuser@example.com')
                ->assertSee('Accountant');

            // Edit user
            $browser->click('@edit-user-btn')
                ->assertPathIs('/admin/users/*/edit')
                ->type('name', 'Updated Test User')
                ->select('role', 'manager')
                ->press('Update User')
                ->assertSee('User updated successfully')
                ->assertSee('Updated Test User')
                ->assertSee('Manager');

            // Test user login with new credentials
            $browser->logout()
                ->visit('/login')
                ->type('email', 'testuser@example.com')
                ->type('password', 'password123')
                ->press('Login')
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Updated Test User');
        });
    }

    /**
     * Test role-based access control
     */
    public function test_role_based_access_control(): void
    {
        $this->browse(function (Browser $browser) {
            // Test admin access
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            $browser->loginAs($admin)
                ->visit('/admin/users')
                ->assertSee('Add User')
                ->assertSee('Edit')
                ->assertSee('Delete');

            // Test accountant access
            $accountant = User::factory()->create();
            $accountant->assignRole('accountant');

            $browser->logout()
                ->loginAs($accountant)
                ->visit('/admin/users')
                ->assertSee('Access Denied')
                ->assertDontSee('Add User');

            // Test manager access
            $manager = User::factory()->create();
            $manager->assignRole('manager');

            $browser->logout()
                ->loginAs($manager)
                ->visit('/admin/users')
                ->assertSee('View Users')
                ->assertDontSee('Add User')
                ->assertDontSee('Delete');
        });
    }

    /**
     * Test permission-based UI elements
     */
    public function test_permission_based_ui_elements(): void
    {
        $this->browse(function (Browser $browser) {
            // Create user with specific permissions
            $user = User::factory()->create();
            $user->givePermissionTo(['product-view', 'product-edit']);

            $browser->loginAs($user)
                ->visit('/admin/products')
                ->assertSee('Products')
                ->assertDontSee('Add Product') // No create permission
                ->assertSee('Edit') // Has edit permission
                ->assertDontSee('Delete'); // No delete permission

            // Test navigation menu based on permissions
            $browser->assertSee('Products')
                ->assertDontSee('Users') // No user management permission
                ->assertDontSee('Roles'); // No role management permission
        });
    }

    /**
     * Test user profile management
     */
    public function test_user_profile_management(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/profile')
                ->assertPathIs('/admin/profile')
                ->assertSee('Profile Information');

            // Update profile
            $browser->type('name', 'Updated Profile Name')
                ->type('email', 'updated@example.com')
                ->press('Update Profile')
                ->assertSee('Profile updated successfully');

            // Change password
            $browser->click('@change-password-tab')
                ->type('current_password', 'password')
                ->type('password', 'newpassword123')
                ->type('password_confirmation', 'newpassword123')
                ->press('Update Password')
                ->assertSee('Password updated successfully');

            // Test login with new password
            $browser->logout()
                ->visit('/login')
                ->type('email', 'updated@example.com')
                ->type('password', 'newpassword123')
                ->press('Login')
                ->assertPathIs('/admin/dashboard')
                ->assertSee('Updated Profile Name');
        });
    }

    /**
     * Test user search and filtering
     */
    public function test_user_search_and_filtering(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            // Create test users
            User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
            User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
            User::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.com']);

            $browser->loginAs($admin)
                ->visit('/admin/users');

            // Test search by name
            $browser->type('@search-input', 'John')
                ->press('@search-btn')
                ->assertSee('John Doe')
                ->assertDontSee('Jane Smith')
                ->assertDontSee('Bob Johnson');

            // Test search by email
            $browser->clear('@search-input')
                ->type('@search-input', 'jane@example.com')
                ->press('@search-btn')
                ->assertSee('Jane Smith')
                ->assertDontSee('John Doe')
                ->assertDontSee('Bob Johnson');

            // Test role filtering
            $browser->select('@role-filter', 'admin')
                ->press('@filter-btn')
                ->assertSee('Filtered results');
        });
    }

    /**
     * Test user activation and deactivation
     */
    public function test_user_activation_deactivation(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            $user = User::factory()->create(['status' => 'active']);

            $browser->loginAs($admin)
                ->visit('/admin/users')
                ->assertSee($user->name)
                ->assertSee('Active');

            // Deactivate user
            $browser->click('@deactivate-user-btn')
                ->press('Confirm Deactivation')
                ->assertSee('User deactivated successfully')
                ->assertSee('Inactive');

            // Test deactivated user cannot login
            $browser->logout()
                ->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertSee('Your account has been deactivated');

            // Reactivate user
            $browser->loginAs($admin)
                ->visit('/admin/users')
                ->click('@activate-user-btn')
                ->press('Confirm Activation')
                ->assertSee('User activated successfully')
                ->assertSee('Active');

            // Test reactivated user can login
            $browser->logout()
                ->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/admin/dashboard');
        });
    }

    /**
     * Test bulk user operations
     */
    public function test_bulk_user_operations(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            // Create multiple users
            $users = User::factory()->count(3)->create();

            $browser->loginAs($admin)
                ->visit('/admin/users');

            // Select multiple users
            $browser->check('@user-checkbox-1')
                ->check('@user-checkbox-2')
                ->check('@user-checkbox-3');

            // Bulk deactivate
            $browser->select('@bulk-action-select', 'deactivate')
                ->press('@bulk-action-btn')
                ->press('Confirm Bulk Action')
                ->assertSee('3 users deactivated successfully');

            // Verify all selected users are deactivated
            $browser->assertSee('Inactive')
                ->assertSee('Inactive')
                ->assertSee('Inactive');
        });
    }

    /**
     * Test user audit trail
     */
    public function test_user_audit_trail(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            $browser->loginAs($admin)
                ->visit('/admin/users')
                ->click('@view-audit-trail-btn')
                ->assertPathIs('/admin/users/*/audit')
                ->assertSee('User Activity Log')
                ->assertSee('Created')
                ->assertSee('Updated')
                ->assertSee('Login')
                ->assertSee('Logout');
        });
    }

    /**
     * Test password reset workflow
     */
    public function test_password_reset_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            // Request password reset
            $browser->visit('/password/reset')
                ->type('email', $user->email)
                ->press('Send Password Reset Link')
                ->assertSee('We have emailed your password reset link');

            // Note: In a real test, you would need to handle email verification
            // For this example, we'll simulate the reset process

            // Test invalid reset token
            $browser->visit('/password/reset/invalid-token')
                ->assertSee('This password reset token is invalid');

            // Test password reset form
            $browser->visit('/password/reset/valid-token')
                ->type('email', $user->email)
                ->type('password', 'newpassword123')
                ->type('password_confirmation', 'newpassword123')
                ->press('Reset Password')
                ->assertSee('Your password has been reset');

            // Test login with new password
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'newpassword123')
                ->press('Login')
                ->assertPathIs('/admin/dashboard');
        });
    }

    /**
     * Test session management
     */
    public function test_session_management(): void
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();

            $browser->loginAs($user)
                ->visit('/admin/dashboard')
                ->assertPathIs('/admin/dashboard');

            // Test session timeout (simulate by clearing session)
            $browser->script('localStorage.clear(); sessionStorage.clear();')
                ->visit('/admin/dashboard')
                ->assertPathIs('/login')
                ->assertSee('Your session has expired');

            // Test concurrent sessions
            $browser->loginAs($user)
                ->visit('/admin/dashboard')
                ->assertPathIs('/admin/dashboard');

            // Simulate login from another device
            $browser->script('window.dispatchEvent(new Event("beforeunload"));')
                ->visit('/admin/dashboard')
                ->assertSee('You have been logged out due to concurrent session');
        });
    }

    /**
     * Helper method to create roles and permissions
     */
    private function createRolesAndPermissions(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $accountantRole = Role::create(['name' => 'accountant']);
        $userRole = Role::create(['name' => 'user']);

        // Create permissions
        $permissions = [
            'user-create', 'user-view', 'user-edit', 'user-delete',
            'product-create', 'product-view', 'product-edit', 'product-delete',
            'journal-create', 'journal-view', 'journal-edit', 'journal-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $managerRole->givePermissionTo(['user-view', 'product-view', 'product-edit', 'journal-view']);
        $accountantRole->givePermissionTo(['product-view', 'journal-create', 'journal-view', 'journal-edit']);
        $userRole->givePermissionTo(['product-view', 'journal-view']);
    }
}
