<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\BatchRepositoryInterface;
use App\Repositories\Contracts\BrandRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use App\Repositories\Eloquent\BatchRepository;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CompanyRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\QrCodeRepository;
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
    ];
}
