<?php


class Controller_Inquiry extends Controller_Base {

    public function after($response) {
        $response = parent::after($response);
        $tokenkey = \Config::get('security.csrf_token_key');
        $csrftoken = \Security::fetch_token();
        $this->template->content->set('csrftoken', $csrftoken);
        $this->template->content->set('tokenkey', $tokenkey);
        return $response;
    }

    public function action_index() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'inquiry/inquiry.css';
        $login = Auth::check();
        $this->template->content = View::forge('inquiry/index.twig');
        $this->template->content->set('login', $login);
        if (INPUT::post()) {
            $this->template->content->set('data', Input::post());
        }
    }

    public function action_confirm() {
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'inquiry/inquiry.css';
        $login = Auth::check();
        $this->template->content = View::forge('inquiry/confirm.twig');
        $this->template->content->set('login', $login);

        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = View::forge('inquiry/index.twig');
            } else {
                $this->template->content->set('data', Input::post());

                $val = Validation::forge();
                $val->add_callable('MyRules');
                $val->add_callable(new MyRules());
                $val->add('name', 'お名前')->add_rule('required')
                        ->add_rule('max_length', 20);
                $val->add('kana', 'ふりがな')->add_rule('required')
//                        ->add_rule('kana')
                        ->add_rule('max_length', 50);
                $val->add('email', 'メールアドレス')->add_rule('required')
                        ->add_rule('valid_email')
                        ->add_rule('max_length', 200);
                $val->add('detail', '問い合わせ内容')->add_rule('required')
                        ->add_rule('max_length', 200);

                if ($val->run()) {
                    $data = array(
                        'login' => $login,
                        'data' => Input::post(),
                     );
                    $this->template->content = View::forge('inquiry/confirm.twig',$data);
                   
                } else {
                    foreach ($val->error() as $key => $e) {
                        $error[$key] = $e->get_message();
                    }
                    $data = array(
                        'login' => $login,
                        'data' => Input::post(),
                        'error' => $error,
                     );
                    $this->template->content = View::forge('inquiry/index.twig');
                }
            }
        } else {
            $this->template->content = View::forge('inquiry/index.twig');
        }
    }

    public function action_complete() {
        $this->template->add_css = 'inquiry/inquiry.css';
        $login = Auth::check();
        $this->template->content = View::forge('inquiry/complete.twig');
        $this->template->content->set('login', $login);

        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = View::forge('inquiry/index.twig');
            } else {
                \Package::load('email');
                 $email=Email::forge('utf-8');
                 $email->from(Input::post('email'),Input::post('name'));
                 $email->to('n.terai@suvt.co.jp','寺井');
                 $email->subject('Blank Pur 問い合わせ');
                 $email->body(mb_convert_encoding(Input::post('detail'),'utf-8'));
                 
                 try{
                     $email->send();
                 } catch (Exception $ex) {
                    $this->template->add_css = 'top/404.css';
                    $this->template->content = View::forge('top/404.twig');
                 }
              $this->template->content = View::forge('inquiry/complete.twig');

            }
        } else {
            $this->template->content = View::forge('inquiry/index.twig');
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