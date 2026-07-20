<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\BannerRepositoryInterface;
use App\Repositories\Contracts\BatchRepositoryInterface;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Repositories\Contracts\BusinessCardRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CityRepositoryInterface;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Repositories\Contracts\LeadRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductTradingVideoRepositoryInterface;
use App\Repositories\Contracts\PushNotificationRepositoryInterface;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use App\Repositories\Contracts\StateRepositoryInterface;
use App\Repositories\Eloquent\BannerRepository;
use App\Repositories\Eloquent\BatchRepository;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\BusinessCardRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CityRepository;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\CountryRepository;
use App\Repositories\Eloquent\LeadRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProductTradingVideoRepository;
use App\Repositories\Eloquent\PushNotificationRepository;
use App\Repositories\Eloquent\QrCodeRepository;
use App\Repositories\Eloquent\StateRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds repository contracts to their Eloquent implementations so services and
 * controllers depend on interfaces, never on concrete data-access classes.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        CompanyRepositoryInterface::class => CompanyRepository::class,
        BrandRepositoryInterface::class => BrandRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        BatchRepositoryInterface::class => BatchRepository::class,
        QrCodeRepositoryInterface::class => QrCodeRepository::class,
        BannerRepositoryInterface::class => BannerRepository::class,
        CountryRepositoryInterface::class => CountryRepository::class,
        StateRepositoryInterface::class => StateRepository::class,
        CityRepositoryInterface::class => CityRepository::class,
        PushNotificationRepositoryInterface::class => PushNotificationRepository::class,
        ProductTradingVideoRepositoryInterface::class => ProductTradingVideoRepository::class,
        LeadRepositoryInterface::class => LeadRepository::class,
        BusinessCardRepositoryInterface::class => BusinessCardRepository::class,
    ];
}
