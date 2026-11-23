@extends('app')

@section('content')
    @php
        $search = request()->string('search')->trim()->toString();
        $showAllQueryParameters = request()->query();

        unset($showAllQueryParameters['search'], $showAllQueryParameters['page']);

        $showAllUrl = url()->current();
        $showAllQueryString = http_build_query($showAllQueryParameters);

        if ($showAllQueryString !== '') {
            $showAllUrl .= '?' . $showAllQueryString;
        }
    @endphp

    <div class="titlebar sticky-top position-sticky">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <h3>
                    @lang('velor-site-information::velor.fields.title', [
                        'subject' => $subject->getAttribute('name'),
                    ])
                </h3>

                <a
                    class="btn btn-secondary ms-4"
                    href="{{ route('site-information-subjects.show', ['site_information_subject' => $subject->getKey()]) }}"
                    role="button"
                    title="{{ __('velor-site-information::velor.subjects.back_to_subject') }}"
                    aria-label="{{ __('velor-site-information::velor.subjects.back_to_subject') }}"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>

                @can('create', \Velor\SiteInformation\Models\SiteInformation::class)
                    <a
                        class="btn btn-primary ms-2"
                        href="{{ route('site-information-subjects.site-information.create', ['site_information_subject' => $subject->getKey()]) }}"
                        role="button"
                        title="{{ __('velor-site-information::velor.fields.add') }}"
                        aria-label="{{ __('velor-site-information::velor.fields.add') }}"
                    >
                        @lang('velor.actions.new')
                    </a>
                @endcan
            </div>

            <div class="d-flex align-items-center gap-2">
                <x-velor.layout.language-switcher />

                @if($search !== '')
                    <a href="{{ $showAllUrl }}" class="btn btn-secondary ws-nowrap" role="button">
                        @lang('velor.actions.show_all')
                    </a>
                @endif
            </div>
        </div>

        <x-velor.layout.paginator-info :pagination="$pagination" />
    </div>

    <x-velor.layout.tabs :tabs="$tabs" />

    <x-velor.layout.index-table :resource="$resource" :items="$pagination->items()" />

    <x-velor.layout.paginator :pagination="$pagination" />

    <x-velor.modal.delete />
@endsection
