@extends('app')

@section('content')
    <div class="titlebar sticky-top position-sticky">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <h3>@lang('velor-site-information::cms.manage.title')</h3>
            </div>

            <div class="d-flex gap-2">
                @can('create', \Velor\SiteInformation\Models\SiteInformationSubject::class)
                    <a
                        class="btn btn-primary"
                        href="{{ route('site-information-subjects.create') }}"
                        role="button"
                    >
                        <i class="bi bi-plus-lg"></i>
                    </a>
                @endcan

                <a
                    class="btn btn-secondary"
                    href="{{ route('site-information.show') }}"
                    role="button"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="content overflow-auto">
        <table
            class="index resource-order-table scoped-resource-order-table"
            data-reorder-url="{{ route('site-information.reorder') }}"
        >
            <thead>
                <tr>
                    <th scope="col">@lang('velor-site-information::resources.site-information.fields.label')</th>
                    <th scope="col">@lang('velor-site-information::resources.site-information.fields.type')</th>
                    <th scope="col" class="text-end">@lang('velor-site-information::cms.manage.actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $subject)
                    @include('velor-site-information::cms.layouts.site-information.partials.manage-subject-rows', [
                        'subject' => $subject,
                        'depth' => 0,
                    ])
                @endforeach
            </tbody>
        </table>
    </div>

    <x-modal.delete />
@endsection
