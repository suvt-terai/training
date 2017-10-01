<?php

class Controller_User_Pwupdate extends Controller_Base {

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
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
//		$this->template->add_css = 'admin_updata/admin_updata.css';


        if (Input::post()) {
            $this->template->content = View::forge('user_pwupdate/index.twig');
            $this->template->content->set('post', Input::post());
        } else {
            $result = DB::select('id', 'username', 'password')
                    ->from('users')
                    ->where('id', '=', Input::get('id'))
                    ->execute()
                    ->as_array();

            $data = array(
                'data' => $result[0],
            );
            $this->template->content = View::forge('user_pwupdate/index.twig', $data);
        }
    }

    public function action_reset() {
        $this->template = View::forge('admin_template.twig');
        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('top/404.twig');
            } else {
                $oldPw = \Auth::reset_password(Input::post(username));
                $this->template->content = View::forge('user_pwupdate/index.twig', $oldPw);
            }
        }
    }


    public function action_confirm() {
        $this->template = View::forge('admin_template.twig');

        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('top/404.twig');
            } else {
                $val = Validation::forge();
                $val->add_callable('MyRules');
                $val->add_callable(new MyRules());
              
                $val->add('new_password', 'パスワード')->add_rule('required')
                        ->add_rule('min_length', 4)
                        ->add_rule('match_field', 'new_passwordconfirm');
                $val->add('new_passwordconfirm', 'パスワード確認')
                        ->add_rule('required');
                if ($val->run()) {
                    $data = array(
                        'post' => Input::post(),
                    );
                    $this->template->content = View::forge('user_pwupdate/confirm.twig', $data);
                } else {
                    foreach ($val->error() as $key => $e) {
                        $error[$key] = $e->get_message();
                    }
                    $data = array(
                        'post' => Input::post(),
                        'error' => $error,
                    );
                    $this->template->content = View::forge('user_pwupdate/index.twig', $data);
                }
            }
        } else {
            $this->template->content = Response::redirect('admin/usersall');
        }
    }

    public function action_complete() {
        $this->template = View::forge('admin_template.twig');

        $this->template->content = View::forge('user_pwupdate/complete.twig');
        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('top/404.twig');
            } else {
                try {
                       $oldPw = \Auth::reset_password(Input::post('username'));
                       
                       \Auth::change_password($oldPw, Input::post('new_password'), Input::post('username'));
                       
                } catch (Exception $e) {
                    var_dump($e);
                    $this->template->content = View::forge('top/404.twig');
                }
            }
        } else {
            $this->template->content = Response::redirect('admin/usersall');
        }
    }
    

}
