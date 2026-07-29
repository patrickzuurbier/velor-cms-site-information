<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Requests;

use App\Http\Requests\AbstractFormRequest;
use App\Rules\SvgMarkup;
use Illuminate\Contracts\Validation\ValidationRule;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;

class SiteInformationValueRequest extends AbstractFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $rules = [
            'values' => ['nullable', 'array'],
        ];

        foreach (SiteInformation::query()->get() as $siteInformation) {
            $rules['values.' . $siteInformation->getKey()] = $this->fieldRules($siteInformation);
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (SiteInformation::query()->get() as $siteInformation) {
            $attributes['values.' . $siteInformation->getKey()] = (string) $siteInformation->getAttribute('label');
        }

        return $attributes;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function getRules(): array
    {
        return $this->rules();
    }

    /**
     * @return array<int, ValidationRule|string>
     */
    protected function fieldRules(SiteInformation $siteInformation): array
    {
        return match ($siteInformation->getAttribute('type')) {
            SiteInformationFieldTypeEnum::EMAIL => ['nullable', 'email', 'max:255'],
            SiteInformationFieldTypeEnum::SVG   => ['nullable', 'string', new SvgMarkup()],
            SiteInformationFieldTypeEnum::URL   => ['nullable', 'url', 'max:2048'],
            default                             => ['nullable', 'string', 'max:255'],
        };
    }
}
