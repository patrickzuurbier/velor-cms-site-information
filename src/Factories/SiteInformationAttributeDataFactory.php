<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Factories;

use App\Data\View\AttributeData;
use App\Enums\AttributeViewTypeEnum;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;

class SiteInformationAttributeDataFactory
{
    public function make(SiteInformation $siteInformation): AttributeData
    {
        return new AttributeData(
            action: 'show',
            label: (string) $siteInformation->getAttribute('label'),
            type: $this->attributeViewType($siteInformation->getAttribute('type'))->value,
            value: $siteInformation->getAttribute('value'),
            url: null,
        );
    }

    protected function attributeViewType(?SiteInformationFieldTypeEnum $type): AttributeViewTypeEnum
    {
        return match ($type) {
            SiteInformationFieldTypeEnum::SVG => AttributeViewTypeEnum::SVG,
            default                           => AttributeViewTypeEnum::STRING,
        };
    }
}
