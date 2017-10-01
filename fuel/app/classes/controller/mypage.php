<?php

class Controller_Mypage extends Controller_Base {

    public function before() {
        parent::before();
        $this->template->add_css = 'mypage/mypage.css';
        if (!Auth::check()) {
            $this->template->add_css = 'login/login.css';
            $this->template->content = Response::redirect('login/');
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
        $login = Auth::check();
        $name = Auth::get_screen_name();
        $id = Auth::get_user_id();
        $this->template->add_css = 'mypage/mypage.css';

        
        try{
            $num = DB::select()->from('replys')->where('user_id', '=', $id[1])->execute()->count();
            $config = array(
                'pagination_url' => '/mypage/index',
                'uri_segment' => 3,
                'per_page' => 10,
                'total_items' => $num
            );
            $pagination = Pagination::forge('mypagination', $config);
            
            $result = DB::select('adminreply', 'message', 'created_at','adminname')
                ->from('replys')
                ->where('user_id', '=', $id[1])
                ->limit($pagination->per_page)
                ->offset($pagination->offset)
                ->execute()
                ->as_array();
        }catch (Exception $ex){
            $this->template->content = View::forge('mypage/index.twig');
        }

        $data = array(
            'username' => $name,
            'login' => $login,
            'data' => $result,
            'post' => Input::post(),
            'pagination' => $pagination
        );

        $this->template->content = View::forge('mypage/index.twig', $data);
        $this->template->content->auto_filter(false);
    }

    public function action_replyconfirm() {
        $login = Auth::check();
        $name = Auth::get_screen_name();
        $id = Auth::get_user_id();

        if (Input::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('mypage/');
            } else {
                $val = Validation::forge();
                $val->add_callable('MyRules');
                $val->add_callable(new MyRules());
                $val->add('reply', '返答')->add_rule('required');

                if ($val->run()) {
                    $data = array(
                        'username' => $name,
                        'login' => $login,
                        'post' => Input::post(),
                    );
                    $this->template->content = View::forge('mypage/replyconfirm.twig', $data);
                } else {
                    foreach ($val->error() as $key => $e) {
                        $error[$key] = $e->get_message();
                    }
                    $result = DB::select('adminreply', 'message', 'created_at')
                            ->from('replys')
                            ->where('user_id', '=', $id[1])
                            ->execute()
                            ->as_array();
                    $data = array(
                        'username' => $name,
                        'login' => $login,
                        'post' => Input::post(),
                        'error' => $error,
                        'data' => $result,
                    );
                    $this->template->content = View::forge('mypage/index.twig', $data);
                }
            }
        } else {
            $this->template->content = Response::redirect('mypage/');
        }
    }

    public function action_replycomplete() {
        $login = Auth::check();
        $name = Auth::get_screen_name();
        $id = Auth::get_user_id();
        $reply = Input::post('reply');
        $this->template->add_css = 'mypage/mypage.css';

        if (Input::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('mypage/');
            } else {
                try {
                    $arrTime = explode('.', microtime(true));
                    $result = DB::insert('replys')->set(array(
                                'user_id' => $id[1],
                                'adminreply' => 0,
                                'message' => $reply,
                                'created_at' => date('Y-m-d H:i:s', $arrTime[0]) . '.' . $arrTime[1],
                            ))->execute();

                    $data = array(
                        'username' => $name,
                        'login' => $login,
                    );

                    $this->template->content = View::forge('mypage/replycomplete.twig', $data);
                } catch (Exception $e) {
                    var_dump($e);
                    $this->template->content = View::forge('top/404.twig');
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

    public function action_userinfo() {
//              $this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $login = Auth::check();
        $this->template->add_css = 'mypage/mypage.css';
        $name = Auth::get_screen_name();
        $user = Auth::instance()->get_user_array();
        $this->template->content = View::forge('mypage/userinfo.twig');
        $this->template->content->set('username', $name);
        $this->template->content->set('data', $user);
        $this->template->content->set('login', $login);

        if (Input::post()) {
            $this->template->content->set('post', Input::post());
        }
    }

    public function action_userpw() {
        $login = Auth::check();
        $this->template->add_css = 'mypage/mypage.css';
        $name = Auth::get_screen_name();
        $user = Auth::instance()->get_user_array();
        $this->template->content = View::forge('mypage/userpw.twig');
        $this->template->content->set('username', $name);
        $this->template->content->set('data', $user);
        $this->template->content->set('login', $login);

        if (Input::post()) {
            $this->template->content->set('post', Input::post());
        }
    }

    public function action_userpwconfirm() {
        $login = Auth::check();
        $this->template->add_css = 'mypage/mypage.css';
        $name = Auth::get_screen_name();
        $this->template->content = View::forge('mypage/userpwconfirm.twig');
        $this->template->content->set('username', $name);
        $this->template->content->set('login', $login);

        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('mypage/userpw.twig');
            } else {
                $val = Validation::forge();
                $val->add_callable('MyRules');
                $val->add_callable(new MyRules());
                $val->add('old_password', 'パスワード')->add_rule('required')
                        ->add_rule('min_length', 4)
                        ->add_rule('oldpassword');
                $val->add('new_password', 'パスワード')->add_rule('required')
                        ->add_rule('min_length', 4)
                        ->add_rule('match_field', 'new_passwordconfirm');
                $val->add('new_passwordconfirm', 'パスワード確認')
                        ->add_rule('required');
                if ($val->run()) {
                     $data = array(
                        'username' => $name,
                        'login' => $login,
                        'post' => Input::post(),
                    );                   
                    $this->template->content = View::forge('mypage/userpwconfirm.twig',$data);

                } else {
                    foreach ($val->error() as $key => $e) {
                        $error[$key] = $e->get_message();
                    }
                    $data = array(
                        'username' => $name,
                        'login' => $login,
                        'post' => Input::post(),
                        'error' => $error,
                    );
                    $this->template->content = View::forge('mypage/userpw.twig',$data);

                }
            }
        } else {
            $this->template->content = Response::redirect('mypage/userinfo.twig');
        }
    }

    public function action_userpwcomplete() {
        $login = Auth::check();
        $name = Auth::get_screen_name();
        $this->template->add_css = 'mypage/mypage.css';
        $this->template->content = View::forge('mypage/userpwcomplete.twig');
        $this->template->content->set('username', $name);
        $this->template->content->set('login', $login);

        if (Input::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('mypage/userpw.twig');
            } else {

                try {
                    $input = Input::post();
                    $auth = Auth::instance();
                    $auth->update_user(
                            array(
                                'old_password' => $input['old_password'],
                                'password' => $input['new_password'],
                    ));
         
                } catch (Exception $e) {
                    var_dump($e);
                    $this->template->content = View::forge('top/404.twig');
                }
            }
        } else {
            $this->template->content = Response::redirect('mypage/userinfo.twig');
        }
    }

    public function action_userinfoconfirm() {
        $login = Auth::check();
        $this->template->add_css = 'mypage/mypage.css';
        $name = Auth::get_screen_name();
        $this->template->content = View::forge('mypage/userinfoconfirm.twig');
        $this->template->content->set('username', $name);
        $this->template->content->set('login', $login);

        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('mypage/userinfo.twig');
            } else {
                $val = Validation::forge();
                $val->add_callable('MyRules');
                $val->add_callable(new MyRules());

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
                        ->add_rule('oneunipue', 'users.email')
                        ->add_rule('max_length', 300);
              
                if ($val->run()) {
                     $data = array(
                        'username' => $name,
                        'login' => $login,
                        'data' => Input::post(),
                    );
                    $this->template->content = View::forge('mypage/userinfoconfirm.twig',$data);
                } else {
                    foreach ($val->error() as $key => $e) {
                        $error[$key] = $e->get_message();
                    }
                     $data = array(
                        'username' => $name,
                        'login' => $login,
                        'post' => Input::post(),
                        'error' => $error,
                    );
                    $this->template->content = View::forge('mypage/userinfo.twig',$data);
                }
            }
        } else {
            $this->template->content = Response::redirect('mypage/userinfo.twig');
        }
    }

    public function action_userinfocomplete() {
        $login = Auth::check();
        $name = Auth::get_screen_name();
        $this->template->add_css = 'mypage/mypage.css';
        $this->template->content = View::forge('mypage/userinfocomplete.twig');
        $this->template->content->set('username', $name);
        $this->template->content->set('login', $login);

        try {
            $input = Input::post();
            $auth = Auth::instance();
            $auth->update_user(
                    array(
                        'email' => $input['email'],
                        'userkana' => $input['userkana'],
                        'postalcode' => $input['postalcode'],
                        'pref' => $input['pref'],
                        'cities' => $input['cities'],
                        'adress1' => $input['adress1'],
                        'adress2' => $input['adress2'],
                        'tel' => $input['tel'],
            ));
               } catch (Exception $e) {
//                $this->template->content = View::forge('top/404.twig');
        }
    }

    public function action_logout() {

        Auth::logout();
        $this->template->add_css = 'top/top.css';
        $this->template->content = Response::redirect('/');
    }

}
