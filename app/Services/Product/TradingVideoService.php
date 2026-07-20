<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Repositories\Contracts\ProductTradingVideoRepositoryInterface;
use App\Services\CrudService;

class TradingVideoService extends CrudService
{
    public function __construct(ProductTradingVideoRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
