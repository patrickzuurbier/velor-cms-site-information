<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Resources;

use App\Resources\AbstractResource;
use App\Resources\Fields\Field;
use App\Resources\Fields\Order;
use App\Resources\Fields\Select;
use App\Resources\Fields\Textarea;
use App\Resources\Fields\Text;
use App\Resources\Tabs\ResourceTab;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Model;
use Velor\SiteInformation\Enums\SiteInformationFieldTypeEnum;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;

class SiteInformationResource extends AbstractResource
{
    public static string $model = SiteInformation::class;

    public function __construct(
        protected UrlGenerator $urlGenerator,
    ) {
    }

    public function titleAttribute(): string
    {
        return 'label';
    }

    /**
     * @return array<int, Field>
     */
    public function fields(): array
    {
        return [
            Text::make('site_information_subject_id')
                ->label(__('velor-site-information::resources.site-information.fields.subject'))
                ->resolveValueUsing(
                    function (Model $model): ?string {
                        if (! $model instanceof SiteInformation) {
                            return null;
                        }

                        return $model->subject?->getAttribute('name');
                    }
                )
                ->exceptOnForms(),
            Select::make('site_information_subject_id')
                ->label(__('velor-site-information::resources.site-information.fields.subject'))
                ->options(fn (): array => $this->subjectOptions())
                ->defaultFromQuery('site_information_subject_id')
                ->onlyOnUpdate()
                ->rules([
                    'required',
                    'uuid',
                    'exists:site_information_subjects,id',
                ]),
            Text::make('label')
                ->label(__('velor-site-information::resources.site-information.fields.label'))
                ->sortable()
                ->searchable()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),
            Text::make('key')
                ->label(__('velor-site-information::resources.site-information.fields.key'))
                ->hideFromIndex()
                ->hideFromShow()
                ->hideFromForms()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),
            Select::make('type')
                ->label(__('velor-site-information::resources.site-information.fields.type'))
                ->options(SiteInformationFieldTypeEnum::class)
                ->sortable()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),
            Textarea::make('value')
                ->label(__('velor-site-information::resources.site-information.fields.value'))
                ->hideFromIndex()
                ->hideFromForms()
                ->rules([
                    'nullable',
                    'string',
                ]),
            Order::make('sort_order')
                ->label(__('velor-site-information::resources.site-information.fields.order')),
        ];
    }

    /**
     * @return array<int, ResourceTab>
     */
    public function tabs(): array
    {
        return [
            ResourceTab::make(__('velor-site-information::resources.site-information-subjects.tabs.children'))
                ->url(fn (SiteInformation $siteInformation): string => $this->urlGenerator->route(
                    'site-information-subjects.children.index',
                    ['site_information_subject' => $siteInformation->getAttribute('site_information_subject_id')]
                ))
                ->onlyOnIndex(),
            ResourceTab::make(__('velor-site-information::resources.site-information-subjects.tabs.fields'))
                ->url(fn (SiteInformation $siteInformation): string => $this->urlGenerator->route(
                    'site-information-subjects.site-information.index',
                    ['site_information_subject' => $siteInformation->getAttribute('site_information_subject_id')]
                ))
                ->onlyOnIndex(),
            ResourceTab::make(__('velor-site-information::resources.site-information.tabs.subject'))
                ->url(fn (SiteInformation $siteInformation): string => $this->urlGenerator->route(
                    'site-information-subjects.show',
                    ['site_information_subject' => $siteInformation->getAttribute('site_information_subject_id')]
                ))
                ->onlyOnShow(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function subjectOptions(): array
    {
        return SiteInformationSubject::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('id', 'name')
            ->all();
    }
}
