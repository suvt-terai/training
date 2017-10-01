<?php

class MyRules {

    // 静的メソッドであることに注意する
    //$val=チェックしたい値、$options=検索したいテーブル名.フィールド名
    public static function _validation_unique($val, $options) {

        Validation::active()->set_message('unique', 'こちらはご利用できません');

        //テーブル名とフィールド名を分割して変数に代入している
        list($table, $field) = explode('.', $options);
        $result = DB::select($field)
                        ->where($field, '=', $val)
                        ->from($table)->execute();

        return !($result->count() > 0);
    }

    public static function _validation_oneunipue($val, $options = null) {
        Validation::active()->set_message('oneunipue', 'このメールアドレスはご利用できません');

        $email = Auth::get_email();

        var_dump($email);

        if (!($email == Input::post('email'))) {

            $result = DB::select('email')
                            ->where('email', '=', Input::post('email'))
                            ->from('users')->execute();

            return !($result->count() > 0);
        }
    }

    public static function _validation_oneemail($val, $options = null) {
        Validation::active()->set_message('oneemail', 'このメールアドレスはご利用できません');
        $email = DB::select('email')
                ->from('users')
                ->where('id', '=', Input::post('id'))
                ->execute()
                ->as_array();

        if (!($email[0]['email'] == Input::post('email'))) {

            $result = DB::select('email')
                            ->where('email', '=', Input::post('email'))
                            ->from('users')->execute();

            return !($result->count() > 0);
        }
    }

    public static function _validation_oldpassword($val, $options = null) {
        Validation::active()->set_message('oldpassword', '現在のパスワードと一致しません');

        $name = Auth::get_screen_name();
        $pass = Input::post('old_password');

        return (Auth::validate_user($name, $pass));
    }

    public static function _validation_oldpw($val, $options = null) {
        Validation::active()->set_message('oldpw', 'パスワードが一致しません');
        $password = DB::select('password')
                        ->where('id', '=', Input::post('id'))
                         ->from('users')->execute();
        return ($password[0]['password'] == Input::post('password'));
    }

    // 非静的メソッドであることに注意する
    public function _validation_is_upper($val) {
        return $val === strtoupper($val);
    }

    public static function _validation_zenkaku($val, $options = null) {
        Validation::active()->set_message('zenkaku', '全角で入力してください');
        mb_regex_encoding("UTF-8");
        return preg_match("/^[ぁ-んァ-ン一-龥]/u", $val) === 1;
    }

    public static function _validation_kana($val, $options = null) {
        Validation::active()->set_message('kana', 'ひらがなで入力してください');
        mb_regex_encoding("UTF-8");
        return preg_match("/^[ぁ-ん一-龥]/u", $val) === 1;
    }

    public static function _validation_number($val, $options = null) {
        Validation::active()->set_message('number', '半角数字のみで入力してください');

        return preg_match("/^[0-9]+$/", $val) === 1;
    }

    public static function _validation_telnumber($val, $options = null) {
        Validation::active()->set_message('telnumber', '電話番号を適切な形で入力してください');

        return preg_match("/\A0[0-9]{9,10}\z/", $val) === 1;
    }
    public static function _validation_data($val,$options = null){
         Validation::active()->set_message('data', '日付を適切な形で入力してください');
        return (strptime(Input::post('data'), '%Y/%m/%d'));
    }

}
