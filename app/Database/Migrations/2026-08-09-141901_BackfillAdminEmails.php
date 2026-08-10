<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillAdminEmails extends Migration
{
    public function up()
    {
        // Generate email sementara (username@mymember.internal)
        // untuk admin existing yang belum punya email
        $this->db->query(
            "UPDATE admin SET email = CONCAT(username, '@mymember.internal') WHERE email IS NULL"
        );
    }

    public function down()
    {
        // Rollback: set email kembali ke NULL untuk email placeholder
        $this->db->query(
            "UPDATE admin SET email = NULL WHERE email LIKE '%@mymember.internal'"
        );
    }
}
