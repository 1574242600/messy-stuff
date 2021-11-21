<?php
require_once("curl.class.php");
class bili {
    public const ak = "";
    public const sk = "";
    
    function login($userid,$pwd,$captcha = false) {
        $loginkey = $this->get_loginkey();
        $cookies = $loginkey[1];
        $data = [
            "appkey" => self::ak ,
            "build" => 5220000 ,
            "username" => $userid ,
            "password" => $this->get_rsa_pwd($loginkey[0],$pwd),
            "mobi_app" => "android" ,
            "platform" => "android" ,
            "ts" => time()
        ];

        if ($captcha) {
            $data["captcha"] = $captcha;

            @$cookies = $cookies." ; JSESSIONID=".$_COOKIE['JSESSIONID'];
            print $cookies;
        }

        $data["sign"] = $this->get_sign($data);

        print_r($data);
        $data = c::post("https://passport.bilibili.com/api/v2/oauth2/login",$data,$cookies);
        return $data ;
    }



    private function get_rsa_pwd($loginkey,$pwd) {
        $hash = $loginkey['hash'];
        $loginkey = openssl_pkey_get_public($loginkey['key']);
        if ($loginkey) {
            openssl_public_encrypt($hash . $pwd ,$rsa_pwd,$loginkey);
            return base64_encode($rsa_pwd);
        }
            return false ;
    }

    private function get_sign($params) {
        $_data = array();
        ksort($params);
        reset($params);
        foreach ($params as $k => $v) {
            // rawurlencode 返回的转义数字必须为大写( 如%2F )
            $_data[] = $k . '=' . rawurlencode($v);
        }
        $_sign = implode('&', $_data);
        //print_r($_sign);
        return strtolower(md5($_sign . self::sk));
    }

    private function get_loginkey() {
        $data = [
            "appkey" => self::ak ,
            "build" => 5220000 ,
            "mobi_app" => "android" ,
            "platform" => "android" ,
            "ts" => time()
        ];
        $data["sign"] = $this->get_sign($data);
        $data = c::post("https://passport.bilibili.com/api/oauth2/getKey",$data,null,1);
        //die($data);
        // 解析http
        list($header, $json) = explode("\r\n\r\n", $data);
        $data = json_decode($json,true);
        
 
 
        //cookies
        if ($data['code'] === 0) {
            if (!@$_COOKIE['sid']) {
                preg_match("/set\-cookie:([^\r\n]*)/i", $header, $cookies);
                $sid = $cookies[1];
                setcookie('sid', $sid,time()+99999);
                return [$data['data'] , $sid];
            } else {
                return [$data['data'] , $_COOKIE['sid']];
            }
        } else {
            return $data;
        }

    }
}