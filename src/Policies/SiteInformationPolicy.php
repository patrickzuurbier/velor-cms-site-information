<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Policies;

use Velor\SiteInformation\Models\SiteInformation;
use App\Models\User;
use App\Policies\Concerns\UsesRolePermissions;

class SiteInformationPolicy
{
    use UsesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }

    public function view(User $user, SiteInformation $siteInformation): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }

    public function update(User $user, SiteInformation $siteInformation): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }

    public function delete(User $user, SiteInformation $siteInformation): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }

    public function restore(User $user, SiteInformation $siteInformation): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }

    public function forceDelete(User $user, SiteInformation $siteInformation): bool
    {
        return $this->allows($user, SiteInformation::class, __FUNCTION__);
    }
}
