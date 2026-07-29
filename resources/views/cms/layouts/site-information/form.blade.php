@extends('app')

@section('content')
    <div class="titlebar sticky-top position-sticky">
        <div class="d-flex align-items-center">
            <h3>@lang('cms.titles.edit', ['resource' => __('velor-site-information::resources.site-information-subjects.plural')])</h3>
        </div>
    </div>

    <div class="form overflow-auto">
        <form action="{{ route('site-information.update') }}" method="POST" role="form" novalidate>
            @csrf
            @method('PUT')

            @foreach ($panels as $panelIndex => $panelData)
                @include('components.form.panel', [
                    'panelData' => $panelData,
                    'panelIdPrefix' => 'site-information-form-panel-' . $panelIndex,
                ])
            @endforeach

            <button type="submit" class="btn btn-success">
                @lang('cms.actions.save')
            </button>

            <a
                class="btn btn-secondary"
                href="{{ route('site-information.show') }}"
                role="button"
            >
                @lang('cms.actions.cancel')
            </a>
        </form>
    </div>
@endsection
