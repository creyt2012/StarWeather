<?php

namespace App\Services;

use App\Models\Tenant;

class TenantManager
{
    protected ?Tenant $currentTenant = null;

    /**
     * Set the current tenant.
     */
    public function setTenant(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;
    }

    /**
     * Get the current tenant.
     */
    public function getTenant(): ?Tenant
    {
        return $this->currentTenant;
    }

    /**
     * Get current tenant ID.
     */
    public function getTenantId(): ?int
    {
        return $this->currentTenant?->id;
    }

    /**
     * Check if a tenant is set.
     */
    public function hasTenant(): bool
    {
        return !is_null($this->currentTenant);
    }
}
