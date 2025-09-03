<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant;

class CustomTenant extends Tenant
{
    public function makeCurrent(): void
    {
        parent::makeCurrent();
        
        // Ensure the tenant connection is properly configured
        $this->configureTenantConnection();
    }
    
    protected function configureTenantConnection(): void
    {
        $databaseConfig = json_decode($this->database, true);
        
        if ($databaseConfig) {
            // Set the tenant connection configuration
            config(['database.connections.tenant' => $databaseConfig]);
            
            // Clear the connection to force reconnection
            app('db')->purge('tenant');
        }
    }
}