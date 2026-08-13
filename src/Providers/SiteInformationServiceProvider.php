<?php

declare(strict_types=1);

namespace Velor\SiteInformation\Providers;

use App\Services\Authorization\Contracts\PolicyRegistryInterface;
use App\Services\CmsMenu\Contracts\CmsMenuItemRegistryInterface;
use App\Services\CmsMenu\Data\CmsMenuItemData;
use App\Services\CmsRouting\Contracts\CmsRouteRegistrarInterface;
use App\Services\Resources\Contracts\ResourceRegistryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;
use Velor\SiteInformation\Models\SiteInformation;
use Velor\SiteInformation\Models\SiteInformationSubject;
use Velor\SiteInformation\Policies\SiteInformationPolicy;
use Velor\SiteInformation\Policies\SiteInformationSubjectPolicy;
use Velor\SiteInformation\Resources\SiteInformationResource;
use Velor\SiteInformation\Resources\SiteInformationSubjectResource;

class SiteInformationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/velor-site-information.php', 'velor-site-information');
    }

    public function boot(
        CmsRouteRegistrarInterface $cmsRoutes,
        ResourceRegistryInterface $resources,
        PolicyRegistryInterface $policies,
        CmsMenuItemRegistryInterface $cmsMenuItems,
        ConfigRepository $config,
    ): void {
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'velor-site-information');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'velor-site-information');

        if ($config->get('velor-site-information.enabled') === true) {
            $resources->register($this->configuredClass($config, 'velor-site-information.resources.site_information_subject', SiteInformationSubjectResource::class));
            $resources->register($this->configuredClass($config, 'velor-site-information.resources.site_information', SiteInformationResource::class));

            $policies->register(
                SiteInformationSubject::class,
                $this->configuredClass($config, 'velor-site-information.policies.' . SiteInformationSubject::class, SiteInformationSubjectPolicy::class),
            );
            $policies->register(
                SiteInformation::class,
                $this->configuredClass($config, 'velor-site-information.policies.' . SiteInformation::class, SiteInformationPolicy::class),
            );

            $cmsMenuItems->registerBefore(
                'users.index',
                new CmsMenuItemData(SiteInformationSubject::class, 'site-information.show', 'velor-site-information::resources.site-information-subjects.plural', 'bi-info-circle'),
            );

            $cmsRoutes->loadAuthenticated(__DIR__ . '/../../routes/cms.php');
        }

        $this->publishes([
            __DIR__ . '/../../config/velor-site-information.php' => $this->app->configPath('velor-site-information.php'),
        ], 'velor-site-information-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => $this->app->databasePath('migrations'),
        ], 'velor-site-information-migrations');

        $this->publishes([
            __DIR__ . '/../../database/seeders' => $this->app->databasePath('seeders'),
        ], 'velor-site-information-seeders');

        $this->publishes([
            __DIR__ . '/../../lang' => $this->app->langPath('vendor/velor-site-information'),
        ], 'velor-site-information-lang');

        $this->publishes([
            __DIR__ . '/../../resources/views' => $this->app->resourcePath('views/vendor/velor-site-information'),
        ], 'velor-site-information-views');
    }

    /**
     * @param class-string $default
     *
     * @return class-string
     */
    protected function configuredClass(ConfigRepository $config, string $key, string $default): string
    {
        $value = $config->get($key);

        if (! is_string($value) || ! class_exists($value)) {
            return $default;
        }

        return $value;
    }
}
