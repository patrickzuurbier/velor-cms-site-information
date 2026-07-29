<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Controllers;

use App\Http\Controllers\Controller;
use Velor\SiteInformation\Http\Requests\SiteInformationRequest;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use App\Services\Resources\Contracts\ResourceIndexQueryInterface;
use Velor\SiteInformation\Services\SiteInformationSvgStorage;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteInformationController extends Controller
{
    public function __construct(
        protected ResourceIndexQueryInterface $resourceIndexQuery,
        protected SiteInformationSvgStorage $svgStorage,
        protected Translator $translator,
    ) {
    }

    public function index(Request $request, SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('viewAny', SiteInformation::class);

        return view('cms.layouts.index', [
            'pagination' => $this->resourceIndexQuery->paginate(
                model: SiteInformation::class,
                parent: $siteInformationSubject,
                relationship: 'siteInformation',
                search: $request->string('search')->toString(),
            ),
            'model' => new SiteInformation([
                'site_information_subject_id' => $siteInformationSubject->getKey(),
            ]),
        ]);
    }

    public function create(SiteInformationSubject $siteInformationSubject): View
    {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('create', SiteInformation::class);

        return view('cms.layouts.form', [
            'model' => new SiteInformation([
                'site_information_subject_id' => $siteInformationSubject->getKey(),
                'sort_order'                  => $this->nextSortOrder($siteInformationSubject),
            ]),
        ]);
    }

    public function store(
        SiteInformationRequest $request,
        SiteInformationSubject $siteInformationSubject,
    ): RedirectResponse {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('create', SiteInformation::class);

        $siteInformation = $siteInformationSubject->siteInformation()->create($request->validated());
        $this->svgStorage->persist($siteInformation, $this->value($siteInformation));

        return redirect()
            ->route('site-information.manage')
            ->with('status', $this->translator->get('velor-site-information::cms.fields.created'));
    }

    public function show(SiteInformationSubject $siteInformationSubject, SiteInformation $siteInformation): View
    {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('view', $siteInformation);

        return view('cms.layouts.show', [
            'model' => $siteInformation,
        ]);
    }

    public function edit(SiteInformationSubject $siteInformationSubject, SiteInformation $siteInformation): View
    {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('update', $siteInformation);

        return view('cms.layouts.form', [
            'model' => $siteInformation,
        ]);
    }

    public function update(
        SiteInformationRequest $request,
        SiteInformationSubject $siteInformationSubject,
        SiteInformation $siteInformation,
    ): RedirectResponse {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('update', $siteInformation);

        $wasSvg = $this->svgStorage->isSvg($siteInformation);
        $oldPath = $this->svgStorage->path($siteInformation);

        $siteInformation->update($request->validated());

        if ($wasSvg && (! $this->svgStorage->isSvg($siteInformation) || $oldPath !== $this->svgStorage->path($siteInformation))) {
            $this->svgStorage->deletePath($oldPath);
        }

        $this->svgStorage->persist($siteInformation, $this->value($siteInformation));

        return redirect()
            ->route('site-information.manage')
            ->with('status', $this->translator->get('velor-site-information::cms.fields.updated'));
    }

    public function destroy(
        SiteInformationSubject $siteInformationSubject,
        SiteInformation $siteInformation,
    ): RedirectResponse {
        $this->authorize('view', $siteInformationSubject);
        $this->authorize('delete', $siteInformation);

        $this->svgStorage->delete($siteInformation);
        $siteInformation->delete();

        return redirect()
            ->route('site-information.manage')
            ->with('status', $this->translator->get('velor-site-information::cms.fields.deleted'));
    }

    protected function value(SiteInformation $siteInformation): ?string
    {
        $value = $siteInformation->getAttribute('value');

        return is_string($value) ? $value : null;
    }

    protected function nextSortOrder(SiteInformationSubject $siteInformationSubject): int
    {
        $sortOrder = $siteInformationSubject->siteInformation()
            ->max('sort_order');

        return is_numeric($sortOrder) ? ((int) $sortOrder) + 1 : 1;
    }
}
