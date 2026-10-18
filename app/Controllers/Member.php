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
        $builder->where('members.is_deleted', 0);
        $builder->orderBy('members.created_at', 'DESC');

        $data['members'] = $builder->get()->getResultArray();

        return view('admin/member', $data);
    }

    public function getLiveMembers()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Unauthorized']);
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('members');
        $builder->select('members.*, master_type_member.type_member');
        $builder->join('master_type_member', 'master_type_member.id_type = members.id_type', 'left');
        $builder->where('members.is_deleted', 0);
        $builder->orderBy('members.created_at', 'DESC');

        $members = $builder->get()->getResultArray();

        foreach ($members as &$m) {
            $m['formatted_expired'] = !empty($m['tgl_expired_member']) ? date('d M Y', strtotime($m['tgl_expired_member'])) : 'No Limit';
        }

        return $this->response->setJSON([
            'status' => 'sukses',
            'data'   => $members
        ]);
    }
}
