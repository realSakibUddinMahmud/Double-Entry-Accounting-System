<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolePermissionSeeder;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Test permission matrix for different roles
     */
    public function test_permission_matrix_for_different_roles(): void
    {
        // Define permission matrix based on PERMISSION_PROGRESS.md
        $permissionMatrix = [
            'admin' => [
                'brand-create', 'brand-view', 'brand-edit', 'brand-delete',
                'category-create', 'category-view', 'category-edit', 'category-delete',
                'product-create', 'product-view', 'product-edit', 'product-delete',
                'sale-create', 'sale-show', 'sale-edit', 'sale-payment-view', 'sale-delete',
                'purchase-create', 'purchase-show', 'purchase-edit', 'purchase-payment-view', 'purchase-delete',
            ],
            'manager' => [
                'brand-view', 'brand-edit',
                'category-view', 'category-edit',
                'product-view', 'product-edit',
                'sale-view', 'sale-edit',
                'purchase-view', 'purchase-edit',
            ],
            'user' => [
                'brand-view',
                'category-view',
                'product-view',
                'sale-view',
                'purchase-view',
            ],
        ];

        foreach ($permissionMatrix as $roleName => $permissions) {
            $this->testRolePermissions($roleName, $permissions);
        }
    }

    /**
     * Test specific role permissions
     */
    private function testRolePermissions(string $roleName, array $allowedPermissions): void
    {
        // Create user with role
        $user = User::factory()->create();
        $role = Role::findByName($roleName) ?? Role::create(['name' => $roleName]);
        $user->assignRole($role);

        // Test allowed permissions
        foreach ($allowedPermissions as $permission) {
            $this->assertTrue($user->can($permission),
                "User with role '{$roleName}' should have permission '{$permission}'");
        }

        // Test denied permissions (permissions not in allowed list)
        $allPermissions = [
            'brand-create', 'brand-view', 'brand-edit', 'brand-delete',
            'category-create', 'category-view', 'category-edit', 'category-delete',
            'product-create', 'product-view', 'product-edit', 'product-delete',
            'sale-create', 'sale-show', 'sale-edit', 'sale-payment-view', 'sale-delete',
            'purchase-create', 'purchase-show', 'purchase-edit', 'purchase-payment-view', 'purchase-delete',
        ];

        $deniedPermissions = array_diff($allPermissions, $allowedPermissions);

        foreach ($deniedPermissions as $permission) {
            $this->assertFalse($user->can($permission),
                "User with role '{$roleName}' should NOT have permission '{$permission}'");
        }
    }

    /**
     * Test brand module permissions
     */
    public function test_brand_module_permissions(): void
    {
        $permissions = ['brand-create', 'brand-view', 'brand-edit', 'brand-delete'];

        foreach ($permissions as $permission) {
            $this->testPermissionAccess($permission, '/admin/brands');
        }
    }

    /**
     * Test category module permissions
     */
    public function test_category_module_permissions(): void
    {
        $permissions = ['category-create', 'category-view', 'category-edit', 'category-delete'];

        foreach ($permissions as $permission) {
            $this->testPermissionAccess($permission, '/admin/categories');
        }
    }

    /**
     * Test product module permissions
     */
    public function test_product_module_permissions(): void
    {
        $permissions = ['product-create', 'product-view', 'product-edit', 'product-delete'];

        foreach ($permissions as $permission) {
            $this->testPermissionAccess($permission, '/admin/products');
        }
    }

    /**
     * Test sale module permissions
     */
    public function test_sale_module_permissions(): void
    {
        $permissions = ['sale-create', 'sale-show', 'sale-edit', 'sale-payment-view', 'sale-delete'];

        foreach ($permissions as $permission) {
            $this->testPermissionAccess($permission, '/admin/sales');
        }
    }

    /**
     * Test purchase module permissions
     */
    public function test_purchase_module_permissions(): void
    {
        $permissions = ['purchase-create', 'purchase-show', 'purchase-edit', 'purchase-payment-view', 'purchase-delete'];

        foreach ($permissions as $permission) {
            $this->testPermissionAccess($permission, '/admin/purchases');
        }
    }

    /**
     * Test permission access for specific route
     */
    private function testPermissionAccess(string $permission, string $route): void
    {
        // Create user with permission
        $userWithPermission = User::factory()->create();
        $permissionModel = Permission::findByName($permission) ?? Permission::create(['name' => $permission]);
        $userWithPermission->givePermissionTo($permissionModel);

        // Create user without permission
        $userWithoutPermission = User::factory()->create();

        // Test access with permission
        $response = $this->actingAs($userWithPermission)->get($route);
        $this->assertNotEquals(403, $response->getStatusCode(),
            "User with permission '{$permission}' should access '{$route}'");

        // Test access without permission
        $response = $this->actingAs($userWithoutPermission)->get($route);
        $this->assertEquals(403, $response->getStatusCode(),
            "User without permission '{$permission}' should be denied access to '{$route}'");
    }

    /**
     * Test role-based access control
     */
    public function test_role_based_access_control(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $userRole = Role::create(['name' => 'user']);

        // Create permissions
        $createPermission = Permission::create(['name' => 'brand-create']);
        $viewPermission = Permission::create(['name' => 'brand-view']);

        // Assign permissions to roles
        $adminRole->givePermissionTo([$createPermission, $viewPermission]);
        $managerRole->givePermissionTo($viewPermission);
        $userRole->givePermissionTo($viewPermission);

        // Create users with different roles
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $manager = User::factory()->create();
        $manager->assignRole($managerRole);

        $user = User::factory()->create();
        $user->assignRole($userRole);

        // Test admin access
        $this->assertTrue($admin->can('brand-create'));
        $this->assertTrue($admin->can('brand-view'));

        // Test manager access
        $this->assertFalse($manager->can('brand-create'));
        $this->assertTrue($manager->can('brand-view'));

        // Test user access
        $this->assertFalse($user->can('brand-create'));
        $this->assertTrue($user->can('brand-view'));
    }

    /**
     * Test permission inheritance
     */
    public function test_permission_inheritance(): void
    {
        // Create parent role
        $parentRole = Role::create(['name' => 'parent']);
        $childRole = Role::create(['name' => 'child']);

        // Create permission
        $permission = Permission::create(['name' => 'test-permission']);

        // Assign permission to parent role
        $parentRole->givePermissionTo($permission);

        // Create user with child role
        $user = User::factory()->create();
        $user->assignRole($childRole);

        // Test that child role doesn't inherit parent permissions
        $this->assertFalse($user->can('test-permission'));

        // Assign parent role to user
        $user->assignRole($parentRole);

        // Test that user now has permission
        $this->assertTrue($user->can('test-permission'));
    }

    /**
     * Test permission caching
     */
    public function test_permission_caching(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'test-permission']);

        // Initially user should not have permission
        $this->assertFalse($user->can('test-permission'));

        // Give permission to user
        $user->givePermissionTo($permission);

        // Clear cache and test
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $user->refresh();

        // User should now have permission
        $this->assertTrue($user->can('test-permission'));
    }

    /**
     * Test multiple permissions
     */
    public function test_multiple_permissions(): void
    {
        $user = User::factory()->create();

        $permissions = [
            Permission::create(['name' => 'permission-1']),
            Permission::create(['name' => 'permission-2']),
            Permission::create(['name' => 'permission-3']),
        ];

        // Give multiple permissions
        $user->givePermissionTo($permissions);

        // Test all permissions
        $this->assertTrue($user->can('permission-1'));
        $this->assertTrue($user->can('permission-2'));
        $this->assertTrue($user->can('permission-3'));

        // Test hasAnyPermission
        $this->assertTrue($user->hasAnyPermission(['permission-1', 'permission-4']));
        $this->assertFalse($user->hasAnyPermission(['permission-4', 'permission-5']));

        // Test hasAllPermissions
        $this->assertTrue($user->hasAllPermissions(['permission-1', 'permission-2']));
        $this->assertFalse($user->hasAllPermissions(['permission-1', 'permission-4']));
    }

    /**
     * Test permission revocation
     */
    public function test_permission_revocation(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'test-permission']);

        // Give permission
        $user->givePermissionTo($permission);
        $this->assertTrue($user->can('test-permission'));

        // Revoke permission
        $user->revokePermissionTo($permission);
        $this->assertFalse($user->can('test-permission'));
    }
}
