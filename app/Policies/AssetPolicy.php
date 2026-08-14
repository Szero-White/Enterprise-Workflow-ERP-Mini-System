<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['asset_manager', 'procurement', 'admin']);
    }

    public function view(User $user, Asset $asset): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasRole(['asset_manager', 'admin']);
    }

    public function assign(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }

    public function receiveReturn(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }

    public function completeMaintenance(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }
}
