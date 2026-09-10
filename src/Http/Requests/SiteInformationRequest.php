<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Requests;

use App\Contracts\Factories\Validation\ResourceValidationAttributesFactoryInterface;
use App\Contracts\Factories\Validation\ResourceValidationRulesFactoryInterface;
use App\Http\Requests\AbstractFormRequest;
use App\Rules\SvgMarkup;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationSubjectRepositoryInterface;
use Velor\SiteInformation\Resources\SiteInformationResource;

class SiteInformationRequest extends AbstractFormRequest
{
    public function __construct(
        protected ResourceValidationRulesFactoryInterface $rulesFactory,
        protected ResourceValidationAttributesFactoryInterface $attributesFactory,
        protected SiteInformationResource $siteInformationResource,
        protected SiteInformationSubjectRepositoryInterface $siteInformationSubjectRepository,
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
        $rules = $this->rulesFactory->make($this->siteInformationResource);
        $rules['key'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('site_information', 'key')
                ->ignore($this->currentSiteInformation()),
        ];
        $rules['type'] = ['required', Rule::enum(SiteInformationFieldTypeEnum::class)];
        $rules['value'] = $this->valueRules();

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->attributesFactory->make($this->siteInformationResource);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function getRules(): array
    {
        return $this->rules();
    }

    protected function prepareForValidation(): void
    {
        if ($this->currentSiteInformation() instanceof SiteInformation) {
            $this->merge([
                'key'                         => $this->currentSiteInformation()->getAttribute('key'),
                'site_information_subject_id' => $this->subjectId(),
                'sort_order'                  => $this->sortOrder(),
            ]);

            return;
        }

        $this->merge([
            'key'                         => $this->generatedKey(),
            'site_information_subject_id' => $this->subjectId(),
            'sort_order'                  => $this->sortOrder(),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected function valueRules(): array
    {
        return match (SiteInformationFieldTypeEnum::tryFrom($this->string('type')->toString())) {
            SiteInformationFieldTypeEnum::EMAIL => ['nullable', 'email', 'max:255'],
            SiteInformationFieldTypeEnum::SVG   => ['nullable', 'string', new SvgMarkup()],
            SiteInformationFieldTypeEnum::URL   => ['nullable', 'url', 'max:2048'],
            default                             => ['nullable', 'string', 'max:255'],
        };
    }

    protected function subjectId(): ?string
    {
        if ($this->filled('site_information_subject_id')) {
            return $this->string('site_information_subject_id')->toString();
        }

        $subject = $this->route('site_information_subject');

        if ($subject instanceof SiteInformationSubject) {
            return $subject->getKey();
        }

        return $this->string('site_information_subject_id')->toString() ?: null;
    }

    protected function currentSiteInformation(): ?SiteInformation
    {
        $siteInformation = $this->route('site_information');

        return $siteInformation instanceof SiteInformation ? $siteInformation : null;
    }

    protected function generatedKey(): string
    {
        $parts = [
            ...$this->subjectKeyPath(),
            Str::snake($this->string('label')->toString()),
        ];

        return implode('.', array_filter($parts));
    }

    /**
     * @return array<int, string>
     */
    protected function subjectKeyPath(): array
    {
        $subject = $this->subject();

        if (! $subject instanceof SiteInformationSubject) {
            return [];
        }

        $keys = [];

        if ($subject->parent instanceof SiteInformationSubject) {
            $keys[] = (string) $subject->parent->getAttribute('key');
        }

        $keys[] = (string) $subject->getAttribute('key');

        return $keys;
    }

    protected function subject(): ?SiteInformationSubject
    {
        $subject = $this->route('site_information_subject');

        if ($subject instanceof SiteInformationSubject) {
            return $subject->loadMissing('parent');
        }

        $subjectId = $this->subjectId();

        if ($subjectId === null || $subjectId === '') {
            return null;
        }

        return $this->siteInformationSubjectRepository->findWithParent($subjectId);
    }

    protected function sortOrder(): int|string
    {
        if ($this->filled('sort_order')) {
            return $this->string('sort_order')->toString();
        }

        $current = $this->currentSiteInformation();

        if ($current instanceof SiteInformation) {
            return (int) $current->getAttribute('sort_order');
        }

        $subject = $this->subject();

        if (! $subject instanceof SiteInformationSubject) {
            return 1;
        }

        return (new SiteInformation([
            'site_information_subject_id' => $subject->getKey(),
        ]))->nextRowOrderPosition();
    }
}
