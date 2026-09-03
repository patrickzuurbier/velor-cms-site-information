<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Requests;

use App\Contracts\Factories\Validation\ResourceValidationAttributesFactoryInterface;
use App\Contracts\Factories\Validation\ResourceValidationRulesFactoryInterface;
use App\Http\Requests\AbstractFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Resources\SiteInformationSubjectResource;

class SiteInformationSubjectRequest extends AbstractFormRequest
{
    public function __construct(
        protected ResourceValidationRulesFactoryInterface $rulesFactory,
        protected ResourceValidationAttributesFactoryInterface $attributesFactory,
        protected SiteInformationSubjectResource $siteInformationSubjectResource,
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->rulesFactory->make($this->siteInformationSubjectResource);
        $rules['key'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('site_information_subjects', 'key')->ignore($this->currentSubject()),
        ];
        $rules['parent_id'] = [
            'nullable',
            'uuid',
            Rule::exists('site_information_subjects', 'id')->whereNull('parent_id'),
            Rule::notIn([$this->currentSubject()?->getKey()]),
        ];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->currentSubject() instanceof SiteInformationSubject) {
            $this->merge([
                'key' => $this->currentSubject()->getAttribute('key'),
                'sort_order' => $this->sortOrder(),
            ]);

            return;
        }

        $this->merge([
            'key' => Str::snake($this->string('name')->toString()),
            'sort_order' => $this->sortOrder(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->attributesFactory->make($this->siteInformationSubjectResource);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function getRules(): array
    {
        return $this->rules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $subject = $this->currentSubject();

            if ($this->filled('parent_id') && $subject instanceof SiteInformationSubject && $subject->children()->exists()) {
                $validator->errors()->add('parent_id', __('velor-site-information::validation.site_information_parent_has_children'));
            }
        });
    }

    protected function currentSubject(): ?SiteInformationSubject
    {
        $subject = $this->route('site_information_subject');

        return $subject instanceof SiteInformationSubject ? $subject : null;
    }

    protected function sortOrder(): int|string
    {
        if ($this->filled('sort_order')) {
            return $this->string('sort_order')->toString();
        }

        $subject = $this->currentSubject();

        if ($subject instanceof SiteInformationSubject) {
            return (int) $subject->getAttribute('sort_order');
        }

        $parentId = $this->parentId();

        return (new SiteInformationSubject([
            'parent_id' => $parentId,
        ]))->nextRowOrderPosition();
    }

    protected function parentId(): ?string
    {
        if (! $this->filled('parent_id')) {
            return null;
        }

        $parentId = $this->string('parent_id')->toString();

        return $parentId !== '' ? $parentId : null;
    }
}
