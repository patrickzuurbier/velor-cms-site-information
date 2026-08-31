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
use App\Models\User;
use Velor\SiteInformation\Http\Requests\SiteInformationSubjectRequest;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Resources\SiteInformationSubjectResource;
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
        protected SiteInformationSubjectResource $siteInformationSubjectResource,
        protected Request $request,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SiteInformationSubject::class);

        return view('cms.layouts.index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                resource: $this->siteInformationSubjectResource,
                search: $request->string('search')->toString(),
            ),
            'resource' => $this->siteInformationSubjectResource,
        ]);
    }

    public function children(Request $request, SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('viewAny', SiteInformationSubject::class);

        return view('velor-site-information::cms.layouts.site-information.subject-children-index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                resource: $this->siteInformationSubjectResource,
                parent: $siteInformationSubject,
                relationship: 'children',
                search: $request->string('search')->toString(),
            ),
            'resource' => $this->siteInformationSubjectResource,
            'subject'  => $siteInformationSubject,
            'tabs'     => $this->subjectTabs($siteInformationSubject, 'children'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SiteInformationSubject::class);

        return view('cms.layouts.form', [
            'resource' => $this->siteInformationSubjectResource,
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
            'resource'     => $this->siteInformationSubjectResource,
            'show'         => $this->showFactory->make($this->siteInformationSubjectResource, $siteInformationSubject),
            'deleteButton' => $this->deleteButton($siteInformationSubject),
        ]);
    }

    public function edit(SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('update', $siteInformationSubject);

        return view('cms.layouts.form', [
            'resource' => $this->siteInformationSubjectResource,
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

    protected function deleteButton(SiteInformationSubject $siteInformationSubject): ?ButtonData
    {
        $user = $this->request->user();

        if (! $user instanceof User || ! $user->can('delete', $siteInformationSubject)) {
            return null;
        }

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
