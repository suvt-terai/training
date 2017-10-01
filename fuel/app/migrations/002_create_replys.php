<?php

namespace Fuel\Migrations;

class Create_replys
{
	public function up()
	{
		\DBUtil::create_table('replys', array(
			'id' =>  array( 'type' => 'serial', 'returning'=>true  ),
			'user_id' => array('type' => 'int'),
			'adminreply' =>  array( 'type' => 'int', 'default' => '1'),
                        'adminname' =>  array('constraint' => 255, 'type' => 'varchar','null' => true),
			'message' => array('type' => 'text'),
			'created_at' => array('type' => 'timestamp', 'null' => true),
			'updated_at' => array('type' => 'timestamp', 'null' => true),

		), array('id'));
	}

	public function down()
	{
		\DBUtil::drop_table('replys');
	}
}
