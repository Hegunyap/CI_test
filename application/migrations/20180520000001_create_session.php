<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_Session extends CI_Migration
{
    protected $table;

    public function __construct()
    {
        parent::__construct();

        $this->table = $this->config->item('sess_save_path');
    }

    public function up()
    {
        if (!$this->db->table_exists($this->table)) {
            $this->dbforge->add_key('timestamp');

            $this->dbforge->add_field([
                'id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 128,
                    'null' => false,
                ],
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => false,
                ],
                'timestamp' => [
                    'type' => 'INT',
                    'constraint' => 10,
                    'unsigned' => true,
                    'default' => 0,
                    'null' => false,
                ],
                'data' => [
                    'type' => 'BLOB',
                    'null' => false,
                ],
            ]);

            // create table
            $this->dbforge->create_table($this->table, true);
        }
    }

    public function down()
    {
        $this->dbforge->drop_table($this->table, true);
    }
}
