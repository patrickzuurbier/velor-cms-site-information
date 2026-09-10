<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Providers;

use App\Services\Authorization\Contracts\PolicyRegistryInterface;
use App\Services\CmsMenu\Contracts\CmsMenuItemRegistryInterface;
use App\Services\CmsMenu\Data\CmsMenuItemData;
use App\Services\CmsRouting\Contracts\CmsRouteRegistrarInterface;
use App\Services\Resources\Contracts\ResourceRegistryInterface;
use Illuminate\Support\ServiceProvider;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Policies\SiteInformationPolicy;
use Velor\SiteInformation\Policies\SiteInformationSubjectPolicy;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationRepositoryInterface;
use Velor\SiteInformation\Repositories\Contracts\SiteInformationSubjectRepositoryInterface;
use Velor\SiteInformation\Repositories\SiteInformationRepository;
use Velor\SiteInformation\Repositories\SiteInformationSubjectRepository;
use Velor\SiteInformation\Resources\SiteInformationResource;
use Velor\SiteInformation\Resources\SiteInformationSubjectResource;

class SiteInformationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteInformationRepositoryInterface::class, SiteInformationRepository::class);
        $this->app->singleton(SiteInformationSubjectRepositoryInterface::class, SiteInformationSubjectRepository::class);
    }

    public function boot(
        CmsRouteRegistrarInterface $cmsRoutes,
        ResourceRegistryInterface $resources,
        PolicyRegistryInterface $policies,
        CmsMenuItemRegistryInterface $cmsMenuItems,
    ): void {
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'velor-site-information');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'velor-site-information');

        $resources->register($this->app->make(SiteInformationSubjectResource::class));
        $resources->register($this->app->make(SiteInformationResource::class));

        $policies->register(SiteInformationSubject::class, SiteInformationSubjectPolicy::class);
        $policies->register(SiteInformation::class, SiteInformationPolicy::class);

        $cmsMenuItems->registerBefore(
            'users.index',
            new CmsMenuItemData(SiteInformationSubject::class, 'site-information.show', 'velor-site-information::resources.site-information-subjects.plural', 'bi-info-circle'),
        );

        $cmsRoutes->loadAuthenticated(__DIR__ . '/../../routes/cms.php');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations'),
        ], 'velor-site-information-migrations');

        $this->publishes([
            __DIR__ . '/../../lang' => $this->app->langPath('vendor/velor-site-information'),
        ], 'velor-site-information-lang');

        $this->publishes([
            __DIR__ . '/../../resources/views' => $this->app->resourcePath('views/vendor/velor-site-information'),
        ], 'velor-site-information-views');
    }
}
