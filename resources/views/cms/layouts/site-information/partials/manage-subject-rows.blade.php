@php
    /** @var \Velor\SiteInformation\Models\SiteInformationSubject $subject */
    use App\Data\View\ButtonData;
    use App\Enums\ButtonTypeEnum;
@endphp

<tr
    class="table-section-row @if($depth > 0) table-section-row-nested @endif"
    data-order-id="{{ $subject->getKey() }}"
    data-order-type="subject"
    data-order-context="{{ $subject->getAttribute('parent_id') ?? '' }}"
    data-order-depth="{{ $depth }}"
    draggable="true"
>
    <th colspan="2" scope="rowgroup">
        @if ($depth > 0)
            <span class="ms-4"></span>
        @endif
        {{ $subject->getAttribute('name') }}
    </th>
    <th class="text-end">
        @can('create', \Velor\SiteInformation\Models\SiteInformation::class)
            <a
                class="btn btn-secondary"
                href="{{ route('site-information-subjects.site-information.create', ['site_information_subject' => $subject->getKey()]) }}"
                role="button"
            >
                <i class="bi bi-plus-lg"></i>
            </a>
        @endcan

        @can('update', $subject)
            <x-layout.button :buttonData="new ButtonData(
                ButtonTypeEnum::EDIT,
                route('site-information-subjects.edit', ['site_information_subject' => $subject->getKey()]),
                null,
                null,
            )" />
        @endcan

        @can('delete', $subject)
            <x-layout.button :buttonData="new ButtonData(
                ButtonTypeEnum::DELETE,
                route('site-information-subjects.destroy', ['site_information_subject' => $subject->getKey()]),
                __('velor-site-information::resources.site-information-subjects.singular'),
                $subject->getAttribute('name'),
                __('velor-site-information::cms.manage.delete_subject_warning', ['subject' => $subject->getAttribute('name')]),
            )" />
        @endcan
    </th>
</tr>

@foreach ($subject->siteInformation as $field)
    <tr
        data-order-id="{{ $field->getKey() }}"
        data-order-type="field"
        data-order-context="{{ $subject->getKey() }}"
        data-order-depth="{{ $depth + 1 }}"
        draggable="true"
    >
        <td>
            @if ($depth > 0)
                <span class="ms-4"></span>
            @endif
            {{ $field->getAttribute('label') }}
        </td>
        <td>{{ $field->getAttribute('type')?->value }}</td>
        <td class="text-end">
            @can('update', $field)
                <x-layout.button :buttonData="new ButtonData(
                    ButtonTypeEnum::EDIT,
                    route('site-information-subjects.site-information.edit', [
                        'site_information_subject' => $subject->getKey(),
                        'site_information' => $field->getKey(),
                    ]),
                    null,
                    null,
                )" />
            @endcan

            @can('delete', $field)
                <x-layout.button :buttonData="new ButtonData(
                    ButtonTypeEnum::DELETE,
                    route('site-information-subjects.site-information.destroy', [
                        'site_information_subject' => $subject->getKey(),
                        'site_information' => $field->getKey(),
                    ]),
                    __('velor-site-information::resources.site-information.singular'),
                    $field->getAttribute('label'),
                    __('velor-site-information::cms.manage.delete_field_warning', ['field' => $field->getAttribute('label')]),
                )" />
            @endcan
        </td>
    </tr>
@endforeach

@foreach ($subject->children as $child)
    @include('velor-site-information::cms.layouts.site-information.partials.manage-subject-rows', [
        'subject' => $child,
        'depth' => $depth + 1,
    ])
@endforeach
