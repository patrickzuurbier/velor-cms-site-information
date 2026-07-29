<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Policies;

use Velor\SiteInformation\Models\SiteInformationSubject;
use App\Models\User;
use App\Policies\Concerns\UsesRolePermissions;

class SiteInformationSubjectPolicy
{
    use UsesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }

    public function view(User $user, SiteInformationSubject $siteInformationSubject): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }

    public function update(User $user, SiteInformationSubject $siteInformationSubject): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }

    public function delete(User $user, SiteInformationSubject $siteInformationSubject): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }

    public function restore(User $user, SiteInformationSubject $siteInformationSubject): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }

    public function forceDelete(User $user, SiteInformationSubject $siteInformationSubject): bool
    {
        return $this->allows($user, SiteInformationSubject::class, __FUNCTION__);
    }
}
