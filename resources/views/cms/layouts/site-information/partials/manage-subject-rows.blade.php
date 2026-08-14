@php
    /** @var \Velor\SiteInformation\Models\SiteInformationSubject $subject */
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
        <a href="{{ route('site-information-subjects.show', ['site_information_subject' => $subject->getKey()]) }}">
            {{ $subject->getAttribute('name') }}
        </a>
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
            <a href="{{ route('site-information-subjects.site-information.show', [
                'site_information_subject' => $subject->getKey(),
                'site_information' => $field->getKey(),
            ]) }}">
                {{ $field->getAttribute('label') }}
            </a>
        </td>
        <td>{{ $field->getAttribute('type')?->value }}</td>
    </tr>
@endforeach

@foreach ($subject->children as $child)
    @include('velor-site-information::cms.layouts.site-information.partials.manage-subject-rows', [
        'subject' => $child,
        'depth' => $depth + 1,
    ])
@endforeach
