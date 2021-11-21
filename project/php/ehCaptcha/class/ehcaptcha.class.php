<?php
class ehCaptcha {
    private static $m = [];
    private static $conn = null;
    private static $sth = [];
    public  static $errInfo = [];

    public function __construct(array $user)
    {
        @self::$conn = new PDO("mysql:host={$user[0]};dbname={$user[3]}", $user[1], $user[2],  [PDO::ATTR_PERSISTENT => true]);
        //self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function init() :bool {

        if(session_status() !== PHP_SESSION_ACTIVE){
            self::$errInfo['msg'] = '[ehCaptcha]会话被禁用或者当前不存在会话';
            return false;
        }

        if (empty($_SESSION['ehCaptcha']) or $_SESSION['ehCaptcha'] === true){
            $_SESSION['ehCaptcha'] = [];
            if(self::randCaptcha1() === false) return false;
            return true;
        } else {

            //if(self::randCaptcha1() === false) return false;
            return true;
        }
    }

    public static function end($y) : bool {    //验证结果
        if ($y === join('-', $_SESSION['ehCaptcha']['y'])) {
            return true;
        }

        return false;
    }

    private static function randCaptcha1() :bool {   //无视难度,知名度
        //if (random_int(0,1) === 1){  //1系列,0角色
        if (1){
            $_SESSION['ehCaptcha']['mod'] = 1;
            $yes = self::randX();
            $_SESSION['ehCaptcha']['name'] = $yes['name'];
            if (empty($yes)) return false;
            $yesImg =  self::one($yes['xid'],0);
            if (empty($yesImg)) return false;
            $row = self::getRow('captcha_js_img') -1;

            $data = [];
            $data[] = $yesImg;
            for ($i = 0;$i !== 8;$i++){
                $sth = self::$conn->query('select id,xid,path from captcha_js_img limit '.random_int(0,$row).',1');

                if($sth === false) {
                    self::$errInfo = $sth->errorInfo();  //todo 错误报告
                    break;
                };
                $sth->setFetchMode(PDO::FETCH_ASSOC);
                $data[] = $sth->fetch();
            }


            shuffle($data);
            $cap = [];
            foreach ($data as $v){
                $_SESSION['ehCaptcha']['img'][] = $v['path'];
                if ($v['xid'] == $yes['xid']) {
                    $cap[] = 1;
                } else $cap[] = 0;
            }
            $_SESSION['ehCaptcha']['y'] = $cap;
            //print_r($_SESSION);
            //$_SESSION['ehCaptcha'] = null;
        } else {
            $yes = self::randX();
            if (empty($yes)) return false;
            //print_r($yes);

        }

        return true;
    }

    private static function randCaptcha2() :void {
        //random_int();
    }

    private static function one(int $xid, int $id) :array {  //随机获取一张指定系列或角色的图片
        if ($id === 0){
            self::$sth['one'] = self::$conn->prepare('select id,xid,path from captcha_js_img where xid = :xid');
            if(self::$sth['one'] ->execute([':xid' => $xid]) === false) return [];
        } else {
            self::$sth['one'] = self::$conn->prepare('select id,xid,path from captcha_js_img where id = :id');
            if(self::$sth['one'] ->execute([':id' => $id]) === false) return [];
        }

        self::$sth['one'] ->setFetchMode(PDO::FETCH_ASSOC);
        $data = self::$sth['one']->fetchAll();
        $key = array_keys($data,end($data))[0];
        $data = $data[random_int(0,$key)];
        unset(self::$sth['one']);

        return $data;
    }

    private static function randX() :array {    //随机返回一个系列名称
        $row = self::getRow('captcha_anime');
        if($row === -1) return [];
        $xid = random_int(1,$row);
        $data = self::getX($xid);
        $data['xid'] = $xid;
        return $data;
    }

    private static function getX(int $xid) :array {     //获取系列名称
        if (empty(self::$sth['getX'])) {
            self::$sth['getX'] = self::$conn->prepare('select name from captcha_anime where xid = :xid');
        }

        self::$sth['getX']->bindParam(':xid',$xid);
        if (self::$sth['getX']->execute() === FALSE){
            self::$errInfo = self::$sth['getX']->errorInfo();
            return [];
        };

        self::$sth['getX']->setFetchMode(PDO::FETCH_ASSOC);
        return self::$sth['getX']->fetchAll()[0];
    }



    private static function getJ() :array {    //随机角色
        //random_int();
    }

    private static function randJ() :array {    //随机角色
        //random_int();
    }

    public static function getImg() :array {
        return $_SESSION['ehCaptcha']['path'];
    }

    private static function getRow(string $table) :int {    //获取最大行数  MyISAM
        $data = self::$conn->query('select count(*) from '.$table);
        if ($data === FALSE) {
            self::$errInfo = self::$conn->errorInfo();
            return -1;
        };
        $data->setFetchMode(PDO::FETCH_ASSOC);
        return $data->fetchAll()[0]['count(*)'];
    }
}