<?php
class Migration_Add_Table_Category_Count extends CI_Migration {

    public function __construct()
    {
        parent::__construct();
    }
    public function up()
    {
        $this->dbforge->add_field(array(
            'category_id' => array(
                'type'           => 'INT',
                'constraint'     => 8,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
                'comment'        => 'カテゴリID'
            ),
            'name' => array(
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'カテゴリ名'
            ),
            'last_learned_date' => array(
                'type'    => 'DATETIME',
                'comment' => '最終学習日'
            ),
            'count' => array(
                'type'       => 'INT',
                'constraint' => 8,
                'unsigned'   => TRUE,
                'comment'    => 'カウント数'
            ),
        ));
        $this->dbforge->add_key('category_id', true);
        $this->dbforge->create_table('category_count', TRUE,  ['comment' => '"単語テーブル"']);
    }

    public function down()
    {
        $this->dbforge->drop_table('category_count');
    }
}
