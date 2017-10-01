<?php

class Controller_User_Delete extends Controller_Base {

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
        $result = DB::select('id', 'username', 'email', 'profile_fields')
                ->from('users')
                ->where('id', '=', Input::get('id'))
                ->execute()
                ->as_array();
        $profile_fields = unserialize($result[0]['profile_fields']);
        $data = array(
            'data' => $result[0],
            'profile_fields' => $profile_fields
        );
        $this->template->content = View::forge('user_delete/index.twig', $data);
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
        $this->template->content = View::forge('user_delete/confirm.twig');
    }

    public function action_complete() {
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';
        if (INPUT::post()) {
            if (!\Security::check_token()) {
                $this->template->content = Response::redirect('top/404.twig');
            } else {
                
                try {
                    
                    $result = DB::select('user_id')
                            ->where('user_id', '=', Input::post('id'))
                            ->from('replys')->execute();
                    $num = count($result);
                    if($num>0){
                    DB::delete('replys')->where('user_id', '=', Input::post('id'))->execute();
                    }
                    Auth::delete_user(Input::post('username'));
                    $this->template->content = View::forge('user_delete/complete.twig');
                } catch (Exception $ex) {
                    var_dump($ex);
                    $this->template->content = View::forge('top/404.twig');
                }
            }
        }
    }

}
