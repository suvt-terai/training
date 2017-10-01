<?php

class Controller_Test extends Controller_Base {

	public function action_index() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
		$this->template->add_css = 'top/base.css';
		$this->template->content = View::forge('test/index.twig');
//            
//            $view = View::forge('test/index.twig');
//            $view->set('title', 'テスト');
            
//            return $view;
            
	}
}