<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRooms extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'SERIAL',
                'unsigned'       => true,
            ],
            'name'        => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_public'   => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'created_by'  => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'created_at'  => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at'  => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('rooms');

        // Insert default rooms
        $db = \Config\Database::connect();
        $db->table('rooms')->insertBatch([
            ['name' => 'General', 'description' => 'General chat room for everyone', 'is_public' => true, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Support', 'description' => 'Get help and support', 'is_public' => true, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'Random', 'description' => 'Random discussions', 'is_public' => true, 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('rooms');
    }
}
