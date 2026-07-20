<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Repositories\Contracts\StateRepositoryInterface;
use App\Services\CrudService;

class StateService extends CrudService
{
    public function __construct(StateRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
