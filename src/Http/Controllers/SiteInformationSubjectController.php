<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Controllers;

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

    public function create(): View
    {
        $this->authorize('create', SiteInformationSubject::class);

        return view('cms.layouts.form', [
            'model' => new SiteInformationSubject(),
        ]);
    }

    public function store(SiteInformationSubjectRequest $request): RedirectResponse
    {
        $this->authorize('create', SiteInformationSubject::class);

        SiteInformationSubject::query()->create($request->validated());

        return redirect()
            ->route('site-information.manage')
            ->with('status', $this->translator->get('velor-site-information::cms.subjects.created'));
    }

    public function show(SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('view', $siteInformationSubject);

        return view('cms.layouts.show', [
            'model' => $siteInformationSubject,
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

        return redirect()
            ->route('site-information.manage')
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
}
