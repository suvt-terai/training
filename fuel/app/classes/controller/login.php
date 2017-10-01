<?php

class Controller_Login extends Controller_Base {

    public function after($response) {
        $response = parent::after($response);
        $tokenkey = \Config::get('security.csrf_token_key');
        $csrftoken = \Security::fetch_token();
        $this->template->content->set('csrftoken', $csrftoken);
        $this->template->content->set('tokenkey', $tokenkey);
         if (Auth::check()) {
            $this->template->add_css = 'mypage/mypage.css';
            $this->template->content = Response::redirect('mypage/');
        } 
        return $response;
    }

    public function action_index() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル     
            $this->template->add_css = 'login/login.css';
            $this->template->content = View::forge('login/index.twig');
       
    }

    public function action_login() {

            $this->template->add_css = 'login/login.css';
            $this->template->content = View::forge('login/index.twig');
            if (INPUT::post()) {
                if (!\Security::check_token()) {
                    $this->template->add_css = 'top/404.css';
                    $this->template->content = View::forge('top/404.twig');
                } else {
                    $val = Validation::forge();
                    $val->add('email', 'メールアドレス')->add_rule('required');
                    $val->add('password', 'パスワード')->add_rule('required');
                    $val->add('submit', '', array('type' => 'submit', 'value' => 'ログイン'));

                    if ($val->run()) {
                        if (Auth::login(Input::post('email'), Input::post('password'))) {
                            $this->template->content = Response::redirect('mypage/');
                        } else {
                            $this->template->content = View::forge('login/index.twig');
                            $this->template->content->set('failedlogin', true);
                            $this->template->content->set('data', Input::post());
                        }
                    } else {
                        foreach ($val->error() as $key => $e) {
                            $error[$key] = $e->get_message();
                        }
                        $this->template->content = View::forge('login/index.twig');
                        $this->template->content->set('error', $error);
                        $this->template->content->set('data', Input::post());
                    }
                }
            
        }
    }

    public function action_404() {
        $this->response_status = 404;
        $this->template->title = 'Not found';
        $this->template->noindex = true;
        $this->template->add_css = 'top/404.css';
        $this->template->content = View::forge('top/404.twig');
    }



}
