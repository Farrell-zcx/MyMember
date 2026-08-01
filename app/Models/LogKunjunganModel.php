<?php

namespace App\Models;

use CodeIgniter\Model;

class LogKunjunganModel extends Model
{
    protected $table            = 'log_kunjungan';
    protected $primaryKey       = 'id_kunjungan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['NIK', 'waktu_kunjungan', 'kuota_awal', 'kuota_akhir'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = false; 
}
