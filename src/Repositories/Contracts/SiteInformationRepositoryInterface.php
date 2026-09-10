<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;

interface SiteInformationRepositoryInterface
{
    /**
     * @return EloquentCollection<int, SiteInformation>
     */
    public function all(): EloquentCollection;

    /**
     * @param  array<int, string>  $ids
     * @return EloquentCollection<int, SiteInformation>
     */
    public function forIds(array $ids): EloquentCollection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForSubject(SiteInformationSubject $subject, array $attributes): SiteInformation;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SiteInformation $siteInformation, array $attributes): SiteInformation;

    public function updateValue(SiteInformation $siteInformation, ?string $value): SiteInformation;

    public function delete(SiteInformation $siteInformation): void;

    /**
     * @param  array<int, string>  $ids
     */
    public function reorderWithinSubject(string $subjectId, array $ids): void;
}
