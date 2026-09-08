<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\DepartmentRepoInterface;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private $departmentRepo;
    public function __construct(DepartmentRepoInterface $departmentRepo)
    {
        $this->departmentRepo = $departmentRepo;
    }
    public function index(){
        $departments = $this->departmentRepo->index();
        return response()->json($departments);
    }
}
