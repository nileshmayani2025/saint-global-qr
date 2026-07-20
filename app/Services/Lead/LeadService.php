<?php

declare(strict_types=1);

namespace App\Services\Lead;

use App\Repositories\Contracts\LeadRepositoryInterface;
use App\Services\CrudService;

class LeadService extends CrudService
{
    public function __construct(LeadRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}
