<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Repositories\Contracts\CityRepositoryInterface;
use App\Services\CrudService;

class CityService extends CrudService
{
    public function __construct(CityRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
