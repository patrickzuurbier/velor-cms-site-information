@extends('app')

@section('content')
    <div class="titlebar sticky-top position-sticky">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <h3>{{ $show->getTitle() }}</h3>
            </div>

            <x-velor.layout.language-switcher />
        </div>
    </div>

    <x-velor.layout.tabs :tabs="$show->getTabs()" />

    <div class="content overflow-auto">
        @foreach ($show->getPanels() as $panelIndex => $panel)
            @include('components.velor.layout.show-panel', [
                'panel' => $panel,
                'panelIdPrefix' => 'show-panel-' . $panelIndex,
            ])
        @endforeach

        <div class="d-flex flex-wrap gap-2">
            @foreach ($show->getButtons() as $button)
                <x-velor.layout.button :buttonData="$button" />
            @endforeach

            @if($deleteButton !== null)
                <x-velor.layout.button :buttonData="$deleteButton" />
            @endif
        </div>
    </div>

    <x-velor.modal.delete />
@endsection
