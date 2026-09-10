<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Repositories\Contracts;

use Velor\SiteInformation\Models\SiteInformationSubject;

interface SiteInformationSubjectRepositoryInterface
{
    /**
     * @return array<int, SiteInformationSubject>
     */
    public function rootSubjectsForPanels(): array;

    /**
     * @return array<string, string>
     */
    public function subjectOptions(): array;

    /**
     * @return array<string, string>
     */
    public function parentOptions(): array;

    public function findWithParent(string $id): ?SiteInformationSubject;

    public function hasChildren(SiteInformationSubject $subject): bool;

    public function loadForSvgDeletion(SiteInformationSubject $subject): SiteInformationSubject;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SiteInformationSubject;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SiteInformationSubject $subject, array $attributes): SiteInformationSubject;

    public function delete(SiteInformationSubject $subject): void;

    /**
     * @param  array<int, string>  $ids
     */
    public function reorderWithinParent(?string $parentId, array $ids): void;
}
