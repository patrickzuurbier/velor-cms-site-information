<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Services;

use App\Data\Form\FormPanelData;
use App\Data\View\AttributePanelData;
use Velor\SiteInformation\Factories\SiteInformationAttributeDataFactory;
use Velor\SiteInformation\Factories\SiteInformationInputDataFactory;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationSubjectRepositoryInterface;
use Illuminate\Http\Request;

class SiteInformationPanelFactory
{
    public function __construct(
        protected SiteInformationInputDataFactory $inputDataFactory,
        protected SiteInformationAttributeDataFactory $attributeDataFactory,
        protected SiteInformationSubjectRepositoryInterface $siteInformationSubjectRepository,
    ) {
    }

    /**
     * @return array<int, FormPanelData>
     */
    public function formPanels(Request $request): array
    {
        return array_map(
            fn (SiteInformationSubject $subject): FormPanelData => $this->formPanel($subject, $request),
            $this->rootSubjects(),
        );
    }

    /**
     * @return array<int, AttributePanelData>
     */
    public function showPanels(): array
    {
        return array_map(
            fn (SiteInformationSubject $subject): AttributePanelData => $this->showPanel($subject),
            $this->rootSubjects(),
        );
    }

    /**
     * @return array<int, SiteInformationSubject>
     */
    public function subjects(): array
    {
        return $this->rootSubjects();
    }

    protected function formPanel(SiteInformationSubject $subject, Request $request): FormPanelData
    {
        return new FormPanelData(
            title: (string) $subject->getAttribute('name'),
            inputs: array_map(
                fn (SiteInformation $siteInformation) => $this->inputDataFactory->make($siteInformation, $request),
                $subject->siteInformation->all(),
            ),
            expanded: ! $subject->getAttribute('is_collapsed'),
            panels: array_map(
                fn (SiteInformationSubject $child): FormPanelData => $this->formPanel($child, $request),
                $subject->children->all(),
            ),
        );
    }

    protected function showPanel(SiteInformationSubject $subject): AttributePanelData
    {
        return new AttributePanelData(
            title: (string) $subject->getAttribute('name'),
            attributes: array_map(
                fn (SiteInformation $siteInformation) => $this->attributeDataFactory->make($siteInformation),
                $subject->siteInformation->all(),
            ),
            expanded: ! $subject->getAttribute('is_collapsed'),
            panels: array_map(
                fn (SiteInformationSubject $child): AttributePanelData => $this->showPanel($child),
                $subject->children->all(),
            ),
        );
    }

    /**
     * @return array<int, SiteInformationSubject>
     */
    protected function rootSubjects(): array
    {
        return $this->siteInformationSubjectRepository->rootSubjectsForPanels();
    }
}
