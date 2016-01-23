<?php
class Migration_Add_Table_Category_Count extends CI_Migration
{

    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->dbforge->add_field(
            [
                'category_id' => [
                    'type'           => 'INT',
                    'constraint'     => 8,
                    'unsigned'       => true,
                    'auto_increment' => true,
                    'comment'        => 'カテゴリID'
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => '255',
                    'comment'    => 'カテゴリ名'
                ],
                'last_learned_date' => [
                    'type'    => 'DATETIME',
                    'comment' => '最終学習日'
                ],
                'count' => [
                    'type'       => 'INT',
                    'constraint' => 8,
                    'unsigned'   => true,
                    'comment'    => 'カウント数'
                ],
            ]
        );
        $this->dbforge->add_key('category_id', true);
        $this->dbforge->create_table('category_count', true,  ['comment' => '"単語テーブル"']);
    }

    public function down()
    {
        $this->dbforge->drop_table('category_count');
    }
}
