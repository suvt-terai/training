<?php

class Controller_Sitemap extends Controller_Base {

    public function action_index() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'sitemap/sitemap.css';
        $login = Auth::check();
        $this->template->content = View::forge('sitemap/index.twig');
        $this->template->content->set('login', $login);
    }

    public function action_404() {
        $this->response_status = 404;
        $this->template->title = 'Not found';
        $this->template->noindex = true;
        $this->template->add_css = 'top/404.css';
        $this->template->content = View::forge('top/404.twig');
    }

}
