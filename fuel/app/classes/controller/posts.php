<?php

class Controller_Posts extends Controller_Template
{

	public function action_action1()
	{
		$data["subnav"] = array('action1'=> 'active' );
		$this->template->title = 'Posts &raquo; Action1';
		$this->template->content = View::forge('posts/action1', $data);
	}

	public function action_action2()
	{
		$data["subnav"] = array('action2'=> 'active' );
		$this->template->title = 'Posts &raquo; Action2';
		$this->template->content = View::forge('posts/action2', $data);
	}

	public function action_action3()
	{
		$data["subnav"] = array('action3'=> 'active' );
		$this->template->title = 'Posts &raquo; Action3';
		$this->template->content = View::forge('posts/action3', $data);
	}

}
