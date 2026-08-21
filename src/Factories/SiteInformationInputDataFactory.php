<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Factories;

use App\Data\Form\InputData;
use App\Enums\InputTypeEnum;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Services\SiteInformationSvgStorage;

class SiteInformationInputDataFactory
{
    public function __construct(
        protected SiteInformationSvgStorage $svgStorage,
        protected Translator $translator,
    ) {
    }

    public function make(SiteInformation $siteInformation, Request $request): InputData
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

    protected function inputType(?SiteInformationFieldTypeEnum $type): InputTypeEnum
    {
        return match ($type) {
            SiteInformationFieldTypeEnum::EMAIL    => InputTypeEnum::EMAIL,
            SiteInformationFieldTypeEnum::SVG      => InputTypeEnum::SVG,
            SiteInformationFieldTypeEnum::TEXTAREA => InputTypeEnum::TEXTAREA,
            default                                => InputTypeEnum::TEXT,
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
}
