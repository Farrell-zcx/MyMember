<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Member extends Controller
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('members');
        $builder->select('members.*, master_type_member.type_member');
        $builder->join('master_type_member', 'master_type_member.id_type = members.id_type', 'left');
        $builder->orderBy('members.created_at', 'DESC');

        $data['members'] = $builder->get()->getResultArray();

        return view('admin/member/index', $data);
    }
}
