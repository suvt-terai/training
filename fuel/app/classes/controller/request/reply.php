<?php

class Controller_Request_reply extends Controller_Base {

    public function selectuser(){
        $result = DB::select('adminreply','adminname', 'message', 'replys.created_at','users.username','user_id')
                    ->from('replys')
                    ->join('users','left')
                    ->on('replys.user_id','=','users.id')
                    ->where('user_id', '=', Input::post('user_id'))
                    ->execute()
                    ->as_array();

            return $result;
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
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';

        if (Input::post()) {
            $result=$this->selectuser();
            $this->template->content = View::forge('request_reply/index.twig');
            $this->template->content->set('post', Input::post());
            $this->template->content->set('data', $result);
        } else {

            $result = DB::select('replys.id','adminreply', 'message', 'adminname','replys.created_at','users.username','user_id')
                    ->from('replys')
                    ->join('users','left')
                    ->on('replys.user_id','=','users.id')
                    ->where('user_id', '=', Input::get('id'))
                    ->execute()
                    ->as_array();
            
            $data = array(
                'data' => $result,
                 'id' =>  Input::get('id')
            );
            $this->template->content = View::forge('request_reply/index.twig', $data);
        }
    }

    public function action_404() {
        $this->response_status = 404;
        $this->template->title = 'Not found';
        $this->template->noindex = true;
        $this->template->add_css = 'top/404.css';
        $this->template->content = View::forge('top/404.twig');
    }

    public function action_confirm() {
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';
        
          if (Input::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('/admin');
            } else {
               
                $result=$this->selectuser();
            
                $val = Validation::forge();
                $val->add('adminname', '管理者')->add_rule('required')
                        ->add_rule('max_length',20);
                $val->add('reply', '返答')->add_rule('required')
                        ->add_rule('max_length',200);

                if ($val->run()) {
                    $data = array(
                        'post' => Input::post(),
                         'data' => $result,
                    );
                    $this->template->content = View::forge('request_reply/confirm.twig', $data);
                } else {
                    foreach ($val->error() as $key => $e) {
                        $error[$key] = $e->get_message();
                    }
                    
                    $data = array(
                        'post' => Input::post(),
                        'error' => $error,
                        'data' => $result,
                    );
                    $this->template->content = View::forge('request_reply/index.twig', $data);
                }
            }
        } else {
            $this->template->content = Response::redirect('admin/requestsall');
        }
       
    }

    public function action_complete() {
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';
         if (Input::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('mypage/');
            } else {
                try {
                    $arrTime = explode('.', microtime(true));
                    $result = DB::insert('replys')->set(array(
                                'user_id' => Input::post('user_id'),
                                'adminreply' => 1,
                                'adminname' => Input::post('adminname'),
                                'message' => Input::post('reply'),
                                'created_at' => date('Y-m-d H:i:s', $arrTime[0]) . '.' . $arrTime[1],
                            ))->execute();

                    $this->template->content = View::forge('request_reply/complete.twig');
                } catch (Exception $e) {
                    var_dump($e);
                    $this->template->content = View::forge('top/404.twig');
                }
            }
        }
       
    
    }

}
