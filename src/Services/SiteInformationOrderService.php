<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Services;

use Velor\SiteInformation\Repositories\Contracts\SiteInformationRepositoryInterface;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationSubjectRepositoryInterface;

class SiteInformationOrderService
{
    public function __construct(
        protected SiteInformationRepositoryInterface $siteInformationRepository,
        protected SiteInformationSubjectRepositoryInterface $siteInformationSubjectRepository,
    ) {
    }

    /**
     * @param array<int, string> $subjectIds
     */
    public function reorderSubjects(?string $parentId, array $subjectIds): void
    {
        $this->siteInformationSubjectRepository->reorderWithinParent($parentId, $subjectIds);
    }

    /**
     * @param array<int, string> $fieldIds
     */
    public function reorderFields(string $subjectId, array $fieldIds): void
    {
        $this->siteInformationRepository->reorderWithinSubject($subjectId, $fieldIds);
    }
}
