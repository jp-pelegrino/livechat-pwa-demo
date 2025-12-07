<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsernameToMessages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('messages', [
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'sender_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('messages', 'username');
    }
}
