<?php

class Controller_Top extends Controller_Base {

    public function action_index() {
//		$this->template->description = SITE_NAME;		//竊舌し繝ｳ繝励Ν
//		$this->template->keywords = SITE_NAME . ',top';	//竊舌し繝ｳ繝励Ν
        $this->template->add_css = 'top/top.css';
        $login = Auth::check();
        $this->template->content = View::forge('top/index.twig');
        $this->template->content->set('login',$login); 
    }

    public function action_404() {
        $this->response_status = 404;
        $this->template->title = 'Not found';
        $this->template->noindex = true;
        $this->template->add_css = 'top/404.css';
        $this->template->content = View::forge('top/404.twig');
    }

}
