<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Controllers;

use App\Http\Controllers\Controller;
use Velor\SiteInformation\Http\Requests\SiteInformationValueRequest;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Services\SiteInformationPanelFactory;
use Velor\SiteInformation\Services\SiteInformationSvgStorage;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteInformationValueController extends Controller
{
    public function __construct(
        protected SiteInformationPanelFactory $panelFactory,
        protected SiteInformationSvgStorage $svgStorage,
        protected Translator $translator,
    ) {
    }

    public function show(): View
    {
        $this->authorize('viewAny', SiteInformationSubject::class);
        $this->authorize('viewAny', SiteInformation::class);

        return view('velor-site-information::cms.layouts.site-information.show', [
            'panels' => $this->panelFactory->showPanels(),
        ]);
    }

    public function manage(): View
    {
        $this->authorize('viewAny', SiteInformationSubject::class);
        $this->authorize('viewAny', SiteInformation::class);
        $this->authorize('create', SiteInformationSubject::class);

        return view('velor-site-information::cms.layouts.site-information.manage', [
            'subjects' => $this->panelFactory->subjects(),
        ]);
    }

    public function edit(Request $request): View
    {
        $this->authorize('viewAny', SiteInformationSubject::class);
        $this->authorize('viewAny', SiteInformation::class);

        return view('velor-site-information::cms.layouts.site-information.form', [
            'panels' => $this->panelFactory->formPanels($request),
        ]);
    }

    public function update(SiteInformationValueRequest $request): RedirectResponse
    {
        $this->authorize('viewAny', SiteInformationSubject::class);

        $values = $request->validated('values');
        $values = is_array($values) ? $values : [];

        foreach (SiteInformation::query()->whereIn('id', array_keys($values))->get() as $siteInformation) {
            $this->authorize('update', $siteInformation);

            $value = $values[$siteInformation->getKey()] ?? null;
            $value = $value === '' ? null : $value;

            $this->svgStorage->persist($siteInformation, $value);

            $siteInformation->update([
                'value' => $value,
            ]);
        }

        return redirect()
            ->route('site-information.show')
            ->with('status', $this->translator->get('velor-site-information::cms.updated'));
    }
}
