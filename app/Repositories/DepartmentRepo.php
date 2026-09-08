<?php

namespace App\Repositories;

use App\Models\Department;
use App\Repositories\Interfaces\DepartmentRepoInterface;

class DepartmentRepo implements DepartmentRepoInterface
{
    public function index(): array
    {
        return Department::all()->toArray();
    }
}