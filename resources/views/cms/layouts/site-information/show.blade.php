@extends('app')

@section('content')
    <div class="titlebar sticky-top position-sticky">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <h3>@lang('velor-site-information::resources.site-information-subjects.plural')</h3>
            </div>

            <div class="d-flex gap-2">
                @can('create', \Velor\SiteInformation\Models\SiteInformationSubject::class)
                    <a
                        class="btn btn-secondary"
                        href="{{ route('site-information.manage') }}"
                        role="button"
                    >
                        <i class="bi bi-gear"></i>
                    </a>
                @endcan

                <a
                    class="btn btn-primary"
                    href="{{ route('site-information.edit') }}"
                    role="button"
                >
                    <i class="bi bi-pencil"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="content overflow-auto">
        @foreach ($panels as $panelIndex => $panel)
            @include('components.layout.show-panel', [
                'panel' => $panel,
                'panelIdPrefix' => 'site-information-panel-' . $panelIndex,
            ])
        @endforeach
    </div>
@endsection
