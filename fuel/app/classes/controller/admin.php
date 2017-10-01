<?php

class Controller_Admin extends Controller_Base {
    
//    public function before(){
//          parent::before();
//          if(!(Input::real_ip()=='192.168.11.32')){
//            $this->template = View::forge('admin_template.twig');
//            $this->template->add_css = 'admin/admin.css';
//            return $this->template->content =Response::redirect('admin/403.twig');
//  
//          }
//    }

    public function action_index() {
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';
        echo Input::ip();
        if(!(Input::real_ip()=='192.168.11.32')){
           return   Response::forge(View::forge('admin/403.twig'),403);
         }
        try{
        $result = DB::select('*')->from('users')->execute();
        $num_rows = count($result);
        } catch (Exception $ex){
            
        }
        $data = array(
            'num' => $num_rows,
        );

        $this->template->content = View::forge('admin/index.twig', $data);
    }

    public function action_404() {
        $this->response_status = 404;
        $this->template->title = 'Not found';
        $this->template->noindex = true;
        $this->template->add_css = 'top/404.css';
        $this->template->content = View::forge('top/404.twig');
    }

    public function action_usersall() {
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';
         if(!(Input::real_ip()=='192.168.11.32')){
           return   Response::forge(View::forge('admin/403.twig'),403);
         }
        try {
            $num = DB::select()->from('users')->execute()->count();
            $config = array(
                'pagination_url' => 'admin/usersall/',
                'uri_segment' => 3,
                'per_page' => 30,
                'total_items' => $num
            );
            $pagination = Pagination::forge('mypagination', $config);
            $page['example_data'] = DB::select('id', 'username')
                    ->from('users')
                    ->limit($pagination->per_page)
                    ->offset($pagination->offset)
                    ->order_by('id', 'asc')
                    ->execute()
                    ->as_array();
        } catch (Exception $ex) {
            
        }

        $data = array(
            'page' => $page['example_data'],
            'pagination' => $pagination,
        );

        $this->template->content = View::forge('admin/usersall.twig', $data);
        $this->template->content->auto_filter(false);
    }

    public function action_requestall() {
        $this->template = View::forge('admin_template.twig');
//		$this->template->description = SITE_NAME;		//←サンプル
//		$this->template->keywords = SITE_NAME . ',top';	//←サンプル
        $this->template->add_css = 'admin/admin.css';
          if(!(Input::real_ip()=='192.168.11.32')){
           return   Response::forge(View::forge('admin/403.twig'),403);
         }
        try {
            $num = DB::select()->from('users')->execute()->count();
            $config = array(
                'pagination_url' => 'admin/requestall/',
                'uri_segment' => 3,
                'per_page' => 30,
                'total_items' => $num
            );
            $pagination = Pagination::forge('mypagination', $config);
// 
//            $result = DB::query('SELECT * FROM users LEFT JOIN'
//                            . ' (SELECT user_id,count(*),max(created_at) FROM replys WHERE adminreply=1 GROUP BY user_id)AS sub '
//                            . 'ON user_id=users.id '
//                            . 'ORDER BY count ASC NULLS FIRST', DB::SELECT)
//                    ->limit($pagination->per_page)
//                    ->offset($pagination->offset)
//                    ->execute();
            $sub_query = DB::select('user_id',array(DB::expr('count(*)'),'count'),array(DB::expr('max(created_at)'),'max') )
                    ->from('replys')
                    ->where('adminreply','=',1)
                    ->group_by('user_id')
                    ->compile(Database_Connection::instance());
//$sub_query = "( ( $sub_query ) AS sub";
                    
             $result = DB::select('users.id','username','sub.count','sub.max','created_at')
                 ->from('users')
                 ->join(array(DB::expr($sub_query),'sub'),'left')
                 ->on('sub.user_id','=','users.id')
                 ->limit($pagination->per_page)
                 ->order_by('count','NULLS FIRST')
                 ->offset($pagination->offset)
                 ->execute()
                 ->as_array();
            
//            
//            
//         $result = DB::select('users.id','username','sub.created_at','sub.adminreply')
//                 ->from('users')
//                 ->join('replys','left')
//                 ->select(array(DB::expr('( user_id,count(*),max(created_at)  from replys WHERE adminreply=1 group by user_id)AS sub ')))
//                ->on('sub.user_id','=','users.id')
//                 ->limit($pagination->per_page)
//                ->order_by('count','asc','NULLS FIRST')
//                 ->offset($pagination->offset)
//                 ->execute()
//                 ->as_array();
//        
        } catch (Exceptin $ex) {
            
        }
        $data = array(
            'data' => $result,
             'pagination' => $pagination,
        );
//         var_dump($data);

        $this->template->content = View::forge('admin/requestall.twig', $data);
            $this->template->content->auto_filter(false);
    }

    public function action_selectdata() {
        $this->template = View::forge('admin_template.twig');
        $this->template->add_css = 'admin/admin.css';
          if(!(Input::real_ip()=='192.168.11.32')){
           return   Response::forge(View::forge('admin/403.twig'),403);
         }
        $val = Validation::forge();
        $val->add_callable('MyRules');
        $val->add_callable(new MyRules());

        $val->add('data', '日付検索')->add_rule('required')
                ->add_rule('data');
        if ($val->run()) {
            $input = Input::post('data');
            $min = $input . ' 00:00:00';
            $max = $input . ' 23:59:59';

            $mintimestamp = strtotime($min);
            $maxtimestamp = strtotime($max);

            $result = DB::select('created_at', 'username', 'id')
                    ->from('users')
                    ->where('created_at', 'between', array($mintimestamp, $maxtimestamp))
                    ->execute();

            $data = array(
                'num' => count($result),
                'data' => $result,
            );
            $this->template->content = View::forge('admin/selectdata.twig', $data);
        } else {
            foreach ($val->error() as $key => $e) {
                $error[$key] = $e->get_message();
            }
            $result = DB::query('SELECT * FROM users LEFT JOIN'
                            . ' (SELECT user_id,count(*),max(created_at) FROM replys WHERE adminreply=1 GROUP BY user_id)AS sub '
                            . 'ON user_id=users.id '
                            . 'ORDER BY count ASC NULLS FIRST', DB::SELECT)->execute();
            $data = array(
                'post' => Input::post(),
                'error' => $error,
                'data' => $result
            );
            $this->template->content = View::forge('admin/requestall.twig', $data);
        }
    }
    public function action_403() {
        $this->template = View::forge('admin_template.twig');
        $this->template->add_css = 'admin/admin.css';
        $this->template->content = View::forge('admin/403.twig');
    }
}
