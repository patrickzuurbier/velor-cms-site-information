<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Services;

use App\Data\Form\FormPanelData;
use App\Data\Form\InputData;
use App\Data\View\AttributeData;
use App\Data\View\AttributePanelData;
use App\Enums\AttributeViewTypeEnum;
use App\Enums\InputTypeEnum;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;

class SiteInformationPanelFactory
{
    public function __construct(
        protected SiteInformationSvgStorage $svgStorage,
        protected Translator $translator,
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
                fn (SiteInformation $siteInformation): InputData => $this->input($siteInformation, $request),
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
                fn (SiteInformation $siteInformation): AttributeData => $this->attribute($siteInformation),
                $subject->siteInformation->all(),
            ),
            expanded: ! $subject->getAttribute('is_collapsed'),
            panels: array_map(
                fn (SiteInformationSubject $child): AttributePanelData => $this->showPanel($child),
                $subject->children->all(),
            ),
        );
    }

    protected function input(SiteInformation $siteInformation, Request $request): InputData
    {
        $attribute = 'values.' . $siteInformation->getKey();
        $name = 'values[' . $siteInformation->getKey() . ']';

        return new InputData(
            label: (string) $siteInformation->getAttribute('label'),
            attribute: $attribute,
            name: $name,
            type: $this->inputType($siteInformation->getAttribute('type'))->value,
            value: $request->old($attribute, $siteInformation->getAttribute('value')),
            help: $this->help($siteInformation),
        );
    }

    protected function attribute(SiteInformation $siteInformation): AttributeData
    {
        return new AttributeData(
            action: 'show',
            label: (string) $siteInformation->getAttribute('label'),
            type: $this->attributeViewType($siteInformation->getAttribute('type'))->value,
            value: $siteInformation->getAttribute('value'),
            url: null,
        );
    }

    protected function inputType(?SiteInformationFieldTypeEnum $type): InputTypeEnum
    {
        return match ($type) {
            SiteInformationFieldTypeEnum::EMAIL    => InputTypeEnum::EMAIL,
            SiteInformationFieldTypeEnum::SVG      => InputTypeEnum::SVG,
            SiteInformationFieldTypeEnum::TEXTAREA => InputTypeEnum::TEXTAREA,
            default                                => InputTypeEnum::TEXT,
        };
    }

    protected function attributeViewType(?SiteInformationFieldTypeEnum $type): AttributeViewTypeEnum
    {
        return match ($type) {
            SiteInformationFieldTypeEnum::SVG => AttributeViewTypeEnum::SVG,
            default                           => AttributeViewTypeEnum::STRING,
        };
    }

    protected function help(SiteInformation $siteInformation): ?string
    {
        if ($siteInformation->getAttribute('type') !== SiteInformationFieldTypeEnum::SVG) {
            return null;
        }

        return $this->translator->get('velor-site-information::resources.site-information.help.svg', [
            'filename' => $this->svgStorage->filename($siteInformation),
        ]);
    }

    /**
     * @return array<int, SiteInformationSubject>
     */
    protected function rootSubjects(): array
    {
        return SiteInformationSubject::query()
            ->whereNull('parent_id')
            ->with([
                'siteInformation',
                'children.siteInformation',
                'children.children',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->all();
    }
}
