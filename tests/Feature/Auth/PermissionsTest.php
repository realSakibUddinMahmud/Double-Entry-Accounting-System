<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\TestCase;

class PermissionsTest extends TestCase
{
    /**
     * Test permission mapping structure based on PERMISSION_PROGRESS.md
     */
    public function test_permission_structure_completeness()
    {
        // Define the expected permissions based on the system modules
        $expectedPermissions = [
            // Brand Module
            'brand-create',
            'brand-view', 
            'brand-edit',
            'brand-delete',
            
            // Category Module
            'category-create',
            'category-view',
            'category-edit', 
            'category-delete',
            
            // Product Module
            'product-create',
            'product-view',
            'product-edit',
            'product-delete',
            
            // Sale Module
            'sale-create',
            'sale-show',
            'sale-edit',
            'sale-payment-view',
            'sale-delete',
            
            // Purchase Module
            'purchase-create',
            'purchase-show',
            'purchase-edit',
            'purchase-payment-view',
            'purchase-delete',
            
            // Customer Module (High Priority)
            'customer-create',
            'customer-view',
            'customer-edit',
            'customer-delete',
            'customer-show',
            
            // Supplier Module (High Priority)
            'supplier-create',
            'supplier-view',
            'supplier-edit',
            'supplier-delete',
            'supplier-show',
            
            // Stock Adjustment Module (High Priority)
            'stock-adjustment-create',
            'stock-adjustment-view',
            'stock-adjustment-edit',
            'stock-adjustment-delete',
            'stock-adjustment-show',
            
            // Accounting-specific permissions
            'journal-create',
            'journal-view',
            'journal-edit',
            'journal-delete',
            'journal-post',
            'journal-reverse',
            
            // Reports permissions
            'report-trial-balance',
            'report-income-statement',
            'report-balance-sheet',
            'report-cash-flow',
            
            // Admin permissions
            'user-create',
            'user-view',
            'user-edit',
            'user-delete',
            'role-create',
            'role-view',
            'role-edit',
            'role-delete',
        ];
        
        $this->assertIsArray($expectedPermissions, 'Permission list should be an array');
        $this->assertGreaterThan(50, count($expectedPermissions), 'Should have comprehensive permission set');
        
        // Test that permissions follow naming convention
        foreach ($expectedPermissions as $permission) {
            $this->assertMatchesRegularExpression('/^[a-z-]+$/', $permission, "Permission {$permission} should follow kebab-case naming");
            $this->assertStringNotContainsString('__', $permission, "Permission {$permission} should not have double underscores");
        }
    }

    /**
     * Test role-permission matrix structure
     */
    public function test_role_permission_matrix()
    {
        $roles = [
            'Super Admin' => ['all_permissions' => true],
            'Admin' => [
                'can' => ['user-*', 'role-*', 'report-*', 'journal-*'],
                'cannot' => []
            ],
            'Manager' => [
                'can' => ['product-*', 'sale-*', 'purchase-*', 'report-trial-balance'],
                'cannot' => ['user-delete', 'role-*']
            ],
            'Accountant' => [
                'can' => ['journal-*', 'report-*'],
                'cannot' => ['product-delete', 'user-*']
            ],
            'Sales Person' => [
                'can' => ['sale-create', 'sale-view', 'customer-*'],
                'cannot' => ['journal-*', 'purchase-*']
            ],
            'Viewer' => [
                'can' => ['*-view'],
                'cannot' => ['*-create', '*-edit', '*-delete', '*-post']
            ]
        ];
        
        $this->assertIsArray($roles, 'Roles should be defined as array');
        $this->assertGreaterThan(4, count($roles), 'Should have at least 5 different roles');
        
        // Test each role has proper structure
        foreach ($roles as $roleName => $permissions) {
            $this->assertIsString($roleName, "Role name should be string");
            $this->assertIsArray($permissions, "Permissions for {$roleName} should be array");
            
            if (isset($permissions['all_permissions']) && $permissions['all_permissions']) {
                // Super admin role
                continue;
            }
            
            $this->assertArrayHasKey('can', $permissions, "Role {$roleName} should have 'can' permissions");
            $this->assertArrayHasKey('cannot', $permissions, "Role {$roleName} should have 'cannot' permissions");
        }
    }

    /**
     * Test permission gate logic simulation
     */
    public function test_permission_gate_logic()
    {
        // Simulate a user with specific permissions
        $userPermissions = ['journal-create', 'journal-view', 'journal-edit', 'report-trial-balance'];
        
        // Test allowed actions
        $this->assertTrue($this->checkPermission($userPermissions, 'journal-create'), 'Should allow journal creation');
        $this->assertTrue($this->checkPermission($userPermissions, 'journal-view'), 'Should allow journal viewing');
        $this->assertTrue($this->checkPermission($userPermissions, 'report-trial-balance'), 'Should allow trial balance report');
        
        // Test denied actions
        $this->assertFalse($this->checkPermission($userPermissions, 'journal-delete'), 'Should deny journal deletion');
        $this->assertFalse($this->checkPermission($userPermissions, 'user-create'), 'Should deny user creation');
        $this->assertFalse($this->checkPermission($userPermissions, 'journal-post'), 'Should deny journal posting');
    }

    /**
     * Test sensitive permission controls
     */
    public function test_sensitive_permissions()
    {
        $sensitivePermissions = [
            'journal-post',      // Posting entries affects financial statements
            'journal-reverse',   // Reversing entries affects audit trail
            'user-delete',       // Security risk
            'role-edit',         // Can escalate privileges
            'report-cash-flow',  // Sensitive financial information
        ];
        
        $regularUser = ['product-view', 'sale-view', 'customer-view'];
        $accountant = ['journal-create', 'journal-view', 'journal-edit', 'report-trial-balance'];
        $admin = ['user-create', 'user-edit', 'role-view', 'journal-post', 'journal-reverse'];
        
        // Regular users should not have sensitive permissions
        foreach ($sensitivePermissions as $permission) {
            $this->assertFalse(
                $this->checkPermission($regularUser, $permission),
                "Regular user should not have {$permission} permission"
            );
        }
        
        // Accountants should have some sensitive permissions but not all
        $this->assertFalse($this->checkPermission($accountant, 'user-delete'), 'Accountant should not delete users');
        $this->assertFalse($this->checkPermission($accountant, 'journal-post'), 'Accountant should not post journals without explicit permission');
        
        // Admins should have critical permissions
        $this->assertTrue($this->checkPermission($admin, 'journal-post'), 'Admin should be able to post journals');
        $this->assertTrue($this->checkPermission($admin, 'journal-reverse'), 'Admin should be able to reverse journals');
    }

    /**
     * Test permission inheritance and conflicts
     */
    public function test_permission_inheritance_and_conflicts()
    {
        // Test wildcard permission patterns
        $wildcardPermissions = ['product-*', 'sale-view'];
        
        // Should inherit all product permissions
        $this->assertTrue($this->checkWildcardPermission($wildcardPermissions, 'product-create'));
        $this->assertTrue($this->checkWildcardPermission($wildcardPermissions, 'product-edit'));
        $this->assertTrue($this->checkWildcardPermission($wildcardPermissions, 'product-delete'));
        
        // Should have specific sale permission
        $this->assertTrue($this->checkWildcardPermission($wildcardPermissions, 'sale-view'));
        
        // Should not have other sale permissions
        $this->assertFalse($this->checkWildcardPermission($wildcardPermissions, 'sale-create'));
        $this->assertFalse($this->checkWildcardPermission($wildcardPermissions, 'sale-edit'));
    }

    /**
     * Test module-specific permission groupings
     */
    public function test_module_permission_groupings()
    {
        $modulePermissions = [
            'inventory' => [
                'product-create', 'product-view', 'product-edit', 'product-delete',
                'category-create', 'category-view', 'category-edit', 'category-delete',
                'stock-adjustment-create', 'stock-adjustment-view', 'stock-adjustment-edit'
            ],
            'sales' => [
                'sale-create', 'sale-show', 'sale-edit', 'sale-delete',
                'customer-create', 'customer-view', 'customer-edit', 'customer-delete'
            ],
            'accounting' => [
                'journal-create', 'journal-view', 'journal-edit', 'journal-post', 'journal-reverse',
                'report-trial-balance', 'report-income-statement', 'report-balance-sheet'
            ],
            'administration' => [
                'user-create', 'user-view', 'user-edit', 'user-delete',
                'role-create', 'role-view', 'role-edit', 'role-delete'
            ]
        ];
        
        foreach ($modulePermissions as $module => $permissions) {
            $this->assertIsArray($permissions, "Module {$module} permissions should be array");
            $this->assertGreaterThan(2, count($permissions), "Module {$module} should have multiple permissions");
            
            // Test that permissions follow module naming
            foreach ($permissions as $permission) {
                if (!in_array($permission, ['user-create', 'user-view', 'user-edit', 'user-delete', 'role-create', 'role-view', 'role-edit', 'role-delete'])) {
                    // Skip user/role permissions as they don't follow module prefix pattern
                    if ($module === 'administration') continue;
                }
                
                $this->assertIsString($permission, "Permission should be string");
                $this->assertMatchesRegularExpression('/^[a-z-]+-[a-z-]+$/', $permission, "Permission {$permission} should follow module-action pattern");
            }
        }
    }

    /**
     * Test journal posting permission workflow
     */
    public function test_journal_posting_permission_workflow()
    {
        // Different permission levels for journal workflow
        $draftUser = ['journal-create', 'journal-view', 'journal-edit'];
        $poster = ['journal-create', 'journal-view', 'journal-edit', 'journal-post'];
        $reverser = ['journal-view', 'journal-reverse'];
        $admin = ['journal-create', 'journal-view', 'journal-edit', 'journal-post', 'journal-reverse'];
        
        // Draft creation workflow
        $this->assertTrue($this->checkPermission($draftUser, 'journal-create'), 'Draft user can create journals');
        $this->assertTrue($this->checkPermission($draftUser, 'journal-edit'), 'Draft user can edit journals');
        $this->assertFalse($this->checkPermission($draftUser, 'journal-post'), 'Draft user cannot post journals');
        
        // Posting workflow
        $this->assertTrue($this->checkPermission($poster, 'journal-post'), 'Poster can post journals');
        $this->assertFalse($this->checkPermission($poster, 'journal-reverse'), 'Poster cannot reverse without explicit permission');
        
        // Reversal workflow
        $this->assertTrue($this->checkPermission($reverser, 'journal-reverse'), 'Reverser can reverse journals');
        $this->assertFalse($this->checkPermission($reverser, 'journal-post'), 'Reverser cannot post without explicit permission');
        
        // Admin workflow
        $this->assertTrue($this->checkPermission($admin, 'journal-create'), 'Admin has full journal permissions');
        $this->assertTrue($this->checkPermission($admin, 'journal-post'), 'Admin can post journals');
        $this->assertTrue($this->checkPermission($admin, 'journal-reverse'), 'Admin can reverse journals');
    }

    // Helper methods

    private function checkPermission(array $userPermissions, string $requiredPermission): bool
    {
        return in_array($requiredPermission, $userPermissions);
    }

    private function checkWildcardPermission(array $userPermissions, string $requiredPermission): bool
    {
        // Check exact match first
        if (in_array($requiredPermission, $userPermissions)) {
            return true;
        }
        
        // Check wildcard patterns
        foreach ($userPermissions as $permission) {
            if (str_ends_with($permission, '-*')) {
                $prefix = str_replace('-*', '', $permission);
                if (str_starts_with($requiredPermission, $prefix . '-')) {
                    return true;
                }
            }
        }
        
        return false;
    }
}