<?php
$t1 = microtime(true);
session_start();
include 'class/ehcaptcha.class.php';

if(!empty($_GET['i'])){
    if ($_GET['i'] > 9 or $_GET['i'] < 1) die();
    header('content-type: image/webp');
    echo file_get_contents($_SESSION['ehCaptcha']['img'][$_GET['i'] - 1]);
    die();
}

if (!empty($_GET['captcha'])){
    $y = ehCaptcha::end($_GET['captcha']);
    $_SESSION['ehCaptcha'] = $y;
    die(json_encode(['captcha' => $y]));
}

if (!empty($_GET['is'])){
    if($_SESSION['ehCaptcha']){
        echo '验证成功';
    } else echo '验证失败';
    $_SESSION['ehCaptcha'] = null;
    die();
}

$user = [
    '127.0.0.1',
    'root',
    '123456',
    'yzm',
];

$captcha = new ehCaptcha($user);
$captcha->init();
?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <link href="https://cdn.bootcss.com/mdui/0.4.3/css/mdui.min.css" rel="stylesheet">
    <script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
    <script>
        $(function () {
            $("div[class='mdui-col-xs-4']>img").click(function(){
                if (this.getAttribute('ehcaptcha') == 0) {
                    this.setAttribute('ehcaptcha', 1);
                    this.setAttribute("style", this.getAttribute('style') + "opacity:0.5;")
                } else {
                    this.setAttribute('ehcaptcha', 0);
                    this.setAttribute("style", "height:120px; width:120px;")
                }
            });

            $("#ehcaptcha").click(function(){
                var arr = [];
                $("div[class='mdui-col-xs-4']>img").each(function(index){
                    arr[index] = this.getAttribute('ehcaptcha');
                });

                $.get("demo.php?captcha=" + arr.join('-'),function(data){
                    if (JSON.parse(data).captcha){
                        console.log(arr.join('-'));
                        html = '<span><i class="mdui-icon material-icons">check</i>验证通过</span>';
                        $("div[class='mdui-card mdui-color-theme-500']").html(html);

                }
                })
            });
        })
    </script>

<body class="mdui-theme-primary-indigo">
    <div class="mdui-card mdui-color-theme-500"  style="width:373px";>
        <div class="mdui-card-header">
            <?php echo "请在下图中选出 《{$_SESSION['ehCaptcha']['name']}》"; ?>
        </div>
        <div class="mdui-card-media">
            <div class="mdui-container-fluid">
            <div class="mdui-row mdui-grid-list"  style="height:360px; width:360px;">
            <?php for($i = 1; $i !== 10 ; $i++) {?>
                <div class="mdui-col-xs-4">
                    <img ehcaptcha="0" style="height:120px; width:120px;" class="mdui-img-fluid" src="http://localhost:63342/ehcaptcha/demo.php?i=<?php echo $i; ?>" alt="img error"/>
                </div>
            <?php }?>
            </div>
            </div>
        </div>
        <button id="ehcaptcha" class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-pink-accent mdui-float-right mdui-m-t-1 mdui-m-r-1 mdui-m-b-1">验证</button>
        </div>


    <a href="http://localhost:63342/ehcaptcha/demo.php?&is=1" class="mdui-btn mdui-btn-raised mdui-ripple mdui-color-pink-accent">登录</a>
</body>



























<?php
$t2 = microtime(true);
echo '脚本耗时'.round($t2-$t1,6).'秒'.PHP_EOL;
?>