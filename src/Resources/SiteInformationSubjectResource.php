<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Resources;

use App\Resources\AbstractResource;
use App\Enums\ButtonTypeEnum;
use App\Enums\ResourceViewEnum;
use App\Resources\Fields\Checkbox;
use App\Resources\Fields\Field;
use App\Resources\Fields\Number;
use App\Resources\Fields\Select;
use App\Resources\Fields\Text;
use App\Resources\Tabs\ResourceTab;
use App\Resources\Validation\Unique;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Model;
use Velor\SiteInformation\Models\SiteInformationSubject;

class SiteInformationSubjectResource extends AbstractResource
{
    public static string $model = SiteInformationSubject::class;

    public function __construct(
        protected UrlGenerator $urlGenerator,
    ) {
    }

    public function titleAttribute(): string
    {
        return 'name';
    }

    /**
     * @return array<int, Field>
     */
    public function fields(): array
    {
        return [
            Text::make('parent_id')
                ->label(__('velor-site-information::resources.site-information-subjects.fields.parent'))
                ->resolveValueUsing(
                    function (Model $model): ?string {
                        if (! $model instanceof SiteInformationSubject) {
                            return null;
                        }

                        return $model->parent?->getAttribute('name');
                    }
                )
                ->exceptOnForms(),
            Select::make('parent_id')
                ->label(__('velor-site-information::resources.site-information-subjects.fields.parent'))
                ->options(fn (): array => $this->parentOptions())
                ->defaultFromQuery('parent_id')
                ->onlyOnForms()
                ->rules([
                    'nullable',
                    'uuid',
                    'exists:site_information_subjects,id',
                ]),
            Text::make('name')
                ->label(__('velor-site-information::resources.site-information-subjects.fields.name'))
                ->sortable()
                ->searchable()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),
            Text::make('key')
                ->label(__('velor-site-information::resources.site-information-subjects.fields.key'))
                ->hideFromIndex()
                ->hideFromShow()
                ->hideFromForms()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    Unique::make('site_information_subjects'),
                ]),
            Checkbox::make('is_collapsed')
                ->label(__('velor-site-information::resources.site-information-subjects.fields.collapsed'))
                ->sortable()
                ->rules([
                    'boolean',
                ]),
            Number::make('sort_order')
                ->label(__('velor-site-information::resources.site-information-subjects.fields.order'))
                ->sortable()
                ->rules([
                    'nullable',
                    'integer',
                    'min:0',
                ]),
        ];
    }

    /**
     * @return array<int, ResourceTab>
     */
    public function tabs(): array
    {
        return [
            ResourceTab::make(__('velor-site-information::resources.site-information-subjects.tabs.children'))
                ->url(fn (SiteInformationSubject $subject): string => $this->urlGenerator->route(
                    'site-information-subjects.children.index',
                    ['site_information_subject' => $subject->getRouteKey()]
                ))
                ->onlyOnShow(),
            ResourceTab::make(__('velor-site-information::resources.site-information-subjects.tabs.fields'))
                ->url(fn (SiteInformationSubject $subject): string => $this->urlGenerator->route(
                    'site-information-subjects.site-information.index',
                    ['site_information_subject' => $subject->getRouteKey()]
                ))
                ->onlyOnShow(),
        ];
    }

    /**
     * @return array<int, ButtonTypeEnum>
     */
    public function buttonsFor(ResourceViewEnum $view): array
    {
        if ($view === ResourceViewEnum::SHOW) {
            return [
                ButtonTypeEnum::LIST,
                ButtonTypeEnum::EDIT,
            ];
        }

        return parent::buttonsFor($view);
    }

    /**
     * @return array<string, string>
     */
    protected function parentOptions(): array
    {
        return SiteInformationSubject::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('id', 'name')
            ->all();
    }
}
