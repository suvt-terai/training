<?php

class Controller_Request_Delete extends Controller_Base {

    public function selectuser() {
        $result = DB::select('replys.id', 'adminreply', 'adminname', 'message', 'replys.created_at', 'users.username', 'user_id')
                ->from('replys')
                ->join('users', 'left')
                ->on('replys.user_id', '=', 'users.id')
                ->where('id', '=', Input::post('id'))
                ->execute()
                ->as_array();

        return $result;
    }

    public function after($response) {
        $response = parent::after($response);
        $tokenkey = \Config::get('security.csrf_token_key');
        $csrftoken = \Security::fetch_token();
        $this->template->content->set('csrftoken', $csrftoken);
        $this->template->content->set('tokenkey', $tokenkey);

        return $response;
    }

    public function action_index() {
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';
        if (Input::post()) {
            $result = $this->selectuser();
            $this->template->content = View::forge('request_delete/index.twig');
            $this->template->content->set('post', Input::post());
            $this->template->content->set('data', $result);
        } else {

            $result = DB::select('replys.id', 'adminreply', 'message', 'adminname', 'replys.created_at', 'users.username', 'user_id')
                    ->from('replys')
                    ->join('users', 'left')
                    ->on('replys.user_id', '=', 'users.id')
                    ->where('replys.id', '=', Input::get('id'))
                    ->execute()
                    ->as_array();
            $data = array(
                'data' => $result[0],
                'id' => Input::get('id')
            );
            var_dump($data);
            $this->template->content = View::forge('request_delete/index.twig', $data);
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
        $this->template->content = View::forge('request_delete/confirm.twig');
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
                  
                    DB::delete('replys')->where('id', '=', Input::post('id'))->execute();
 
                    $this->template->content = View::forge('user_delete/complete.twig');
                } catch (Exception $ex) {
                    var_dump($ex);
                    $this->template->content = View::forge('top/404.twig');
                }
            }
        }
        $this->template->content = View::forge('request_delete/complete.twig');
    }

}
