<?php

namespace Fuel\Migrations;

class Create_users
{
	public function up()
	{
		\DBUtil::create_table('users', array(
			'id' =>  array( 'type' => 'serial', 'returning'=>true  ),
			'username' => array('constraint' => 50, 'type' => 'varchar'),
			'password' => array('constraint' => 255, 'type' => 'varchar'),
			'group' =>  array( 'type' => 'int', 'default' => '1'),
			'email' => array('constraint' => 255, 'type' => 'varchar'),
			'last_login' => array('constraint' => 25, 'type' => 'varchar'),
			'login_hash' => array('constraint' => 255, 'type' => 'varchar'),
			'profile_fields' => array('type' => 'text'),
			'created_at' => array('type' => 'int', 'null' => true),
			'updated_at' => array('type' => 'int', 'null' => true),

		), array('id'));
	}

	public function down()
	{
		\DBUtil::drop_table('users');
	}
}