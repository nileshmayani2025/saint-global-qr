<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Repositories\Contracts\BannerRepositoryInterface;
use App\Services\CrudService;

class BannerService extends CrudService
{
    public function __construct(BannerRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
