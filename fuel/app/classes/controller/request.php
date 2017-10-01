<?php

class Controller_Request extends Controller_Base {

    public function before() {
        parent::before();
        if (Auth::check()) {
            $this->template->add_css = 'mypage/mypage.css';
            $this->template->content = Response::redirect('mypage/');
        }
    }

    public function after($response) {
        $response = parent::after($response);
        $prefall = Pref::prefall();
        $tokenkey = \Config::get('security.csrf_token_key');
        $csrftoken = \Security::fetch_token();
        $this->template->content->set('csrftoken', $csrftoken);
        $this->template->content->set('tokenkey', $tokenkey);
        $this->template->content->set('prefall', $prefall);
        return $response;
    }

    public function action_index() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'request/request.css';
        $this->template->content = View::forge('request/index.twig');

        if (INPUT::post()) {
            $this->template->content->set('data', Input::post());
        }
    }

    public function action_confirm() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'request/request.css';
        $this->template->content = View::forge('request/confirm.twig');
        $this->template->content->set('data', Input::post());

        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = View::forge('request/index.twig');
            } else {
                $val = Validation::forge();
                $val->add_callable('MyRules');
                $val->add_callable(new MyRules());
                $val->add('username', 'お名前')->add_rule('required')
                        ->add_rule('max_length', 20)
                        ->add_rule('unique','users.username');
                $val->add('userkana', 'ふりがな')->add_rule('required')
                        ->add_rule('kana')
                        ->add_rule('max_length', 50);
                $val->add('postalcode', '郵便番号')->add_rule('required')
                        ->add_rule('number')
                        ->add_rule('exact_length', 7);
                $val->add('pref', '都道府県')->add_rule('required')
                        ->add_rule('match_collection', Pref::prefkey());
                $val->add('cities', '市区町村')->add_rule('required')
                        ->add_rule('max_length', 20);
                $val->add('adress1', '町域名・番地など')->add_rule('required')
                        ->add_rule('max_length', 20);
                $val->add('adress2', '建物名・部屋番号など')
                        ->add_rule('max_length', 50);
                $val->add('tel', '電話番号')->add_rule('required')
                        ->add_rule('telnumber');
                $val->add('email', 'メールアドレス')->add_rule('required')
                        ->add_rule('valid_email')
                        ->add_rule('unique', 'users.email')
                        ->add_rule('max_length', 300);
                $val->add('password', 'パスワード')->add_rule('required')
                        ->add_rule('min_length', 4)
                        ->add_rule('match_field', 'passwordconfirm')
                        ->add_rule('max_length', 20);
                $val->add('passwordconfirm', 'パスワード確認')
                        ->add_rule('required')
                        ->add_rule('max_length', 20);

                if ($val->run()) {
                    $this->template->content = View::forge('request/confirm.twig');
                    $this->template->content->set('data', Input::post());
                } else {
                    foreach ($val->error() as $key => $e) {
                        $error[$key] = $e->get_message();
                    }

                    $this->template->content = View::forge('request/index.twig');
                    $this->template->content->set('error', $error);
                    $this->template->content->set('data', Input::post());
                    //var_dump($this->template->content);
                }
            }
        } else {
            $this->template->content = View::forge('request/index.twig');
        }
    }

    public function action_complete() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'request/request.css';
        $this->template->content = View::forge('request/complete.twig');
        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = View::forge('request/index.twig');
            } else {
                try {
                    $input = Input::post();
                    $auth = Auth::instance();
                    $auth->create_user($input['username'], $input['password'], $input['email'], 1, array(
                        'userkana' => $input['userkana'],
                        'postalcode' => $input['postalcode'],
                        'pref' => $input['pref'],
                        'cities' => $input['cities'],
                        'adress1' => $input['adress1'],
                        'adress2' => $input['adress2'],
                        'tel' => $input['tel'],
                    ));
                    $query = DB::select()->from('users')->execute();
                } catch (Exception $e) {
                    $this->template->content = View::forge('top/404.twig');
                }
                $query = DB::query('SELECT id FROM users ORDER BY id DESC LIMIT 1');

                if (Auth::force_login($query)) {
                    $this->template->add_css = 'mypage/mypage.css';
                    $this->template->content = Response::redirect('mypage/index.twig');
                }
            }
        } else {
            $this->template->content = View::forge('request/index.twig');
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
