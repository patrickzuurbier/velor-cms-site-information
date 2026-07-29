<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Http\Controllers;

use App\Http\Controllers\Controller;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Services\SiteInformationOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class SiteInformationOrderController extends Controller
{
    public function __construct(
        protected SiteInformationOrderService $orderService,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $input = $request->validate([
            'type'    => ['required', 'string', Rule::in(['subject', 'field'])],
            'context' => ['nullable', 'string'],
            'items'   => ['required', 'array'],
            'items.*' => ['required', 'string'],
        ]);

        $items = array_values(array_filter($input['items'], 'is_string'));
        $context = $input['context'] ?? null;
        $context = is_string($context) && $context !== '' ? $context : null;

        if ($input['type'] === 'subject') {
            $this->authorize('reorder', SiteInformationSubject::class);
            $this->orderService->reorderSubjects($context, $items);

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $this->authorize('reorder', SiteInformation::class);

        if ($context === null) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->orderService->reorderFields($context, $items);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
