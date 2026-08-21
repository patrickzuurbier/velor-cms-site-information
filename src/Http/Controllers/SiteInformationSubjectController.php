<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Controllers;

use App\Contracts\Factories\View\ShowFactoryInterface;
use App\Data\View\ButtonData;
use App\Data\View\ModalData;
use App\Data\View\TabData;
use App\Data\View\TabsData;
use App\Enums\ButtonTypeEnum;
use App\Http\Controllers\Controller;
use Velor\SiteInformation\Http\Requests\SiteInformationSubjectRequest;
use Velor\SiteInformation\Models\SiteInformationSubject;
use App\Services\Resources\Contracts\ResourceIndexQueryInterface;
use Velor\SiteInformation\Services\SiteInformationSvgStorage;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteInformationSubjectController extends Controller
{
    public function __construct(
        protected ResourceIndexQueryInterface $resourceIndexQuery,
        protected SiteInformationSvgStorage $svgStorage,
        protected Translator $translator,
        protected ShowFactoryInterface $showFactory,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SiteInformationSubject::class);

        return view('cms.layouts.index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                model: SiteInformationSubject::class,
                search: $request->string('search')->toString(),
            ),
            'model' => new SiteInformationSubject(),
        ]);
    }

    public function children(Request $request, SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('viewAny', SiteInformationSubject::class);

        return view('velor-site-information::cms.layouts.site-information.subject-children-index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                model: SiteInformationSubject::class,
                parent: $siteInformationSubject,
                relationship: 'children',
                search: $request->string('search')->toString(),
            ),
            'subject' => $siteInformationSubject,
            'tabs'    => $this->subjectTabs($siteInformationSubject, 'children'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SiteInformationSubject::class);

        $parentId = $this->parentId($request);

        return view('cms.layouts.form', [
            'model' => new SiteInformationSubject([
                'parent_id'  => $parentId,
                'sort_order' => $this->nextSortOrder($parentId),
            ]),
        ]);
    }

    public function store(SiteInformationSubjectRequest $request): RedirectResponse
    {
        $this->authorize('create', SiteInformationSubject::class);

        $siteInformationSubject = SiteInformationSubject::query()->create($request->validated());

        return $this->redirectAfterSave($siteInformationSubject)
            ->with('status', $this->translator->get('velor-site-information::cms.subjects.created'));
    }

    public function show(SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('view', $siteInformationSubject);

        return view('velor-site-information::cms.layouts.site-information.subject-show', [
            'model'        => $siteInformationSubject,
            'show'         => $this->showFactory->make($siteInformationSubject),
            'deleteButton' => $this->deleteButton($siteInformationSubject),
        ]);
    }

    public function edit(SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('update', $siteInformationSubject);

        return view('cms.layouts.form', [
            'model' => $siteInformationSubject,
        ]);
    }

    public function update(
        SiteInformationSubjectRequest $request,
        SiteInformationSubject $siteInformationSubject,
    ): RedirectResponse {
        $this->authorize('update', $siteInformationSubject);

        $siteInformationSubject->update($request->validated());

        return $this->redirectAfterSave($siteInformationSubject)
            ->with('status', $this->translator->get('velor-site-information::cms.subjects.updated'));
    }

    public function destroy(SiteInformationSubject $siteInformationSubject): RedirectResponse
    {
        $this->authorize('delete', $siteInformationSubject);

        $this->deleteSvgFiles($siteInformationSubject);
        $siteInformationSubject->delete();

        return redirect()
            ->route('site-information.manage')
            ->with('status', $this->translator->get('velor-site-information::cms.subjects.deleted'));
    }

    protected function deleteSvgFiles(SiteInformationSubject $siteInformationSubject): void
    {
        $siteInformationSubject->loadMissing([
            'siteInformation',
            'children.siteInformation',
        ]);

        foreach ($siteInformationSubject->siteInformation as $siteInformation) {
            $this->svgStorage->delete($siteInformation);
        }

        foreach ($siteInformationSubject->children as $child) {
            foreach ($child->siteInformation as $siteInformation) {
                $this->svgStorage->delete($siteInformation);
            }
        }
    }

    protected function redirectAfterSave(SiteInformationSubject $siteInformationSubject): RedirectResponse
    {
        $parentId = $siteInformationSubject->getAttribute('parent_id');

        if (is_string($parentId) && $parentId !== '') {
            return redirect()->route('site-information-subjects.children.index', [
                'site_information_subject' => $parentId,
            ]);
        }

        return redirect()->route('site-information.manage');
    }

    protected function parentId(Request $request): ?string
    {
        if (! $request->filled('parent_id')) {
            return null;
        }

        $parentId = $request->string('parent_id')->toString();

        return $parentId !== '' ? $parentId : null;
    }

    protected function nextSortOrder(?string $parentId): int
    {
        return (new SiteInformationSubject([
            'parent_id' => $parentId,
        ]))->nextRowOrderPosition();
    }

    protected function deleteButton(SiteInformationSubject $siteInformationSubject): ButtonData
    {
        $name = (string) $siteInformationSubject->getAttribute('name');

        return new ButtonData(
            ButtonTypeEnum::DELETE,
            route('site-information-subjects.destroy', ['site_information_subject' => $siteInformationSubject->getKey()]),
            __('velor-site-information::resources.site-information-subjects.singular'),
            $name,
            __('cms.button_titles.delete', ['resource' => __('velor-site-information::resources.site-information-subjects.singular')]),
            new ModalData(
                'delete',
                __('cms.delete_modal.title', ['resource' => $name]),
                __('velor-site-information::cms.manage.delete_subject_warning', ['subject' => $name]),
                __('cms.actions.close'),
                __('cms.actions.delete'),
            ),
        );
    }

    protected function subjectTabs(SiteInformationSubject $siteInformationSubject, string $activeTab): TabsData
    {
        return new TabsData([
            new TabData(
                route('site-information-subjects.children.index', ['site_information_subject' => $siteInformationSubject->getKey()]),
                $activeTab === 'children',
                __('velor-site-information::resources.site-information-subjects.tabs.children'),
            ),
            new TabData(
                route('site-information-subjects.site-information.index', ['site_information_subject' => $siteInformationSubject->getKey()]),
                $activeTab === 'fields',
                __('velor-site-information::resources.site-information-subjects.tabs.fields'),
            ),
        ]);
    }
}
