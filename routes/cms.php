<?php

declare(strict_types=1);

use Illuminate\Routing\Router;
use Velor\SiteInformation\Http\Controllers\SiteInformationController;
use Velor\SiteInformation\Http\Controllers\SiteInformationOrderController;
use Velor\SiteInformation\Http\Controllers\SiteInformationSubjectController;
use Velor\SiteInformation\Http\Controllers\SiteInformationValueController;

/**
 * @var Router $router
 */
$router->get('/site-information', [SiteInformationValueController::class, 'show'])->name('site-information.show');
$router->get('/site-information/manage', [SiteInformationValueController::class, 'manage'])->name('site-information.manage');
$router->post('/site-information/reorder', SiteInformationOrderController::class)->name('site-information.reorder');
$router->get('/site-information/edit', [SiteInformationValueController::class, 'edit'])->name('site-information.edit');
$router->match(['PUT', 'PATCH'], '/site-information', [SiteInformationValueController::class, 'update'])->name('site-information.update');
$router->redirect('/site-information-subjects', '/cms/site-information/manage')
    ->name('site-information-subjects.index');
$router->redirect('/site-information-subjects/{site_information_subject}/site-information', '/cms/site-information/manage')
    ->name('site-information-subjects.site-information.index');
$router->resource('site-information-subjects', SiteInformationSubjectController::class)->except('index');
$router->resource('site-information-subjects.site-information', SiteInformationController::class)->except('index');
