<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AdminModel;

class AdminUserController extends BaseController
{
    public function index()
    {
        $adminModel = new AdminModel();
        $admins = $adminModel->orderBy('id_admin', 'DESC')->findAll();

        return view('admin/users/index', ['admins' => $admins]);
    }
}
