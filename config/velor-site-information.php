<?php

declare(strict_types=1);

use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Policies\SiteInformationPolicy;
use Velor\SiteInformation\Policies\SiteInformationSubjectPolicy;
use Velor\SiteInformation\Resources\SiteInformationResource;
use Velor\SiteInformation\Resources\SiteInformationSubjectResource;

return [
    'resources' => [
        'site_information'         => SiteInformationResource::class,
        'site_information_subject' => SiteInformationSubjectResource::class,
    ],

    'policies' => [
        SiteInformation::class        => SiteInformationPolicy::class,
        SiteInformationSubject::class => SiteInformationSubjectPolicy::class,
    ],
];
