<?php
$t1 = microtime(true);
$data = require_once('data.php');
$host = '127.0.0.1';
$username = 'root';
$password = '123456';
$db = 'yzm';
$r_data  = [];   //角色
$x_data  = [];   //系列
$i_data = [];    //未定义角色的图片
$j_data = [];    //角色图片
$allData = []; //所有的图片

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //获取数据
    foreach ($data as $f){
        echo "//--------------------{$f['name']}---------------------------\n";
        //未定义角色的图片
        $filenames = gl(glob("{$f['alias']}".DIRECTORY_SEPARATOR."*.{jpg,png,gif,jpeg}",GLOB_BRACE));
        $Aflag = empty(glob("./{$f['alias']}/already.php"));

        if (!empty($filenames)) {
            foreach ($filenames as $v){
                $p = nameAl($v);
                $i_data[$f['alias']][] = [
                    'xid' => 0,
                    'r18' => $p[1],
                    'path' => __DIR__ . DIRECTORY_SEPARATOR . $v,
                    'diff' => $p[0],
                ];
            }
            unset($filenames);
        }

        #print_r($i_data);

        //角色,图片
        if (!empty($f['role'])){
            foreach ($f['role'] as $r) {
                //角色
                if (empty(glob("{$f['alias']}//{$r['alias']}/already.php"))) {
                    $r_data[$f['alias']][] = $r;
                }

                //角色图片文件
                $filenames = gl(glob("{$f['alias']}/{$r['alias']}/*.{jpg,png,gif,jpeg}", GLOB_BRACE));
                if(empty($filenames)) {echo "{$r['name']}  无需要添加的角色图片文件\n"; continue;};
                foreach ($filenames as $v) {
                    $p = nameAl($v);
                    $v = basename($v);
                    $j_data[ $f['alias'] ][ $r['alias'] ][] = [
                        'id' => 0,
                        'r18' => $p[1],
                        'path' => __DIR__ . DIRECTORY_SEPARATOR . $f['alias']. DIRECTORY_SEPARATOR .$v,
                        'diff' => $p[0],
                    ];
                }
            }
            unset($list,$f['role'],$filenames);
        } else unset($f['role']);

        #print_r($r_data);

        //系列
        if ($Aflag){
            unset($f['mixed']);
            $x_data[] = $f;
        }

    }

    //系列插入
    if (!empty($x_data)) {
        $stmt = $conn->prepare("INSERT INTO captcha_anime (name, alias, rep) VALUES (:name, :alias, :rep)");
        foreach ($x_data as $v) {
            $xid = [animeInsert($v)];
            Afile($xid,$v['alias']);
            echo "系列: {$v['name']} 已添加 \n";
        }
        unset($stmt);
    } else echo '无新系列'.PHP_EOL;

    //角色插入
    if (!empty($r_data)) {

        $stmt = $conn->prepare("INSERT INTO captcha_role (xid, name, alias, rep) VALUES (:xid, :name, :alias, :rep)");
        foreach ($r_data as $k => $v) {
            foreach ($v as $r) {
                $xid = require($k .'/already.php');
                $r['xid'] = $xid[0];
                $id = [roleInsert($r)];
                Afile($id,$k ."/{$r['alias']}");
                echo "角色: {$r['name']} 已添加 \n";
            }
        }
        unset($stmt);
    } else echo '无新角色'.PHP_EOL;

    //图片

    //未定义角色的图片
    if(!empty($i_data)){
        foreach ($i_data as $k => $v) {
            foreach ($v as $r) {
                $xid = require("$k/already.php");
                $r['id'] = 0;
                $r['xid'] = $xid[0];
                $allData[] = $r;
            }
        }
        unset($stmt);
    }

    //角色图片
    if(!empty($j_data)){
        foreach ($j_data as $k => $v){
            $xid = require("$k/already.php");
            foreach ($v as $j => $v){
                $id = require("$k/$j/already.php");
                foreach ($v as $r){
                    $r['id'] = $id[0];
                    $r['xid'] = $xid[0];
                    $r['path'] = dirname($r['path']).DIRECTORY_SEPARATOR.$j.DIRECTORY_SEPARATOR.basename($r['path']);
                    $allData[] = $r;
                }
            }
        }
    };

    // 杂图
    $filenames = gl(glob("za".DIRECTORY_SEPARATOR."*.{jpg,png,gif,jpeg}",GLOB_BRACE));
    if (!empty($filenames)) {
        $r = [
            'id' => 0,
            'xid' => 0,
            'path' => null,
        ];

        foreach ($filenames as $v) {
            $p = nameAl($v);
            $r['r18'] = $p[1];
            $r['diff'] = 1;
            $path = __DIR__. DIRECTORY_SEPARATOR .$v;
            $r['path'] = $path;
            $allData[] = $r;
        }
    }

    if (!empty($allData)){
        $stmt = $conn->prepare("INSERT INTO captcha_js_img (id, xid, path, r18, diff) VALUES (:id, :xid, :path, :r18, :diff)");
        shuffle($allData);
        foreach ($allData as $v){
            $path = dirname($v['path']).DIRECTORY_SEPARATOR.'#'.basename($v['path']);
            rename($v['path'],$path);
            $v['path'] = $path;
            rImgInsert($v);
            echo "$path 已添加\n";
        }
    }
} catch (PDOException $e){
    echo $e->getMessage();
}

$t2 = microtime(true);

echo '耗时'.round($t2-$t1,6).'秒'.PHP_EOL;
echo '最高占用内存'.round((memory_get_peak_usage()/1024)/1024,4) .'MB';








function Afile(array $data,string $path) : void {
    $str = '<?php return '.var_export($data,true).';';
    file_put_contents("./$path/already.php",$str,LOCK_EX);
}


function nameAl(string $path) :array {
    $name = basename($path);
    if (preg_match('/^([1-5])-([0|1])-[0-9]+\.(jpg|jpeg|gif|png)$/',$name,$match)){
        return [$match[1],$match[2]];       //难度,isR18
    } else {
        return [1,0];
    }
}


function queryAuto(string $table_name) :int {
    global $conn;
    $stmt = $conn->prepare("select auto_increment from information_schema.tables where table_schema= database() and table_name= :tablename");
    $stmt->bindParam(':tablename', $table_name);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    return $stmt->fetchAll()[0]['auto_increment'];
}

function animeInsert(array $v) :int {
    global $stmt;
    $stmt->bindParam(':name', $v['name']);
    $stmt->bindParam(':alias', $v['alias']);
    $stmt->bindParam(':rep', $v['rep']);
    $stmt->execute();

    return queryAuto('captcha_anime') - 1;
}

function roleInsert(array $v) :int {
    global $stmt;
    $stmt->bindParam(':xid', $v['xid']);
    $stmt->bindParam(':name', $v['name']);
    $stmt->bindParam(':alias', $v['alias']);
    $stmt->bindParam(':rep', $v['rep']);
    $stmt->execute();
    return queryAuto('captcha_role') - 1;
}

function rImgInsert(array $v) : void {
    global $stmt;
    $stmt->bindParam(':id', $v['id']);
    $stmt->bindParam(':xid', $v['xid']);
    $stmt->bindParam(':path', $v['path']);
    $stmt->bindParam(':r18', $v['r18']);
    $stmt->bindParam(':diff', $v['diff']);
    $stmt->execute();
}

function gl(array $data) : array {
    if (empty($data)) return [];
    $names = [];
    foreach ($data as $v) {
        $name = basename($v);
        if (!preg_match("/^#.*/",$name)) {
            $names[] = $v;
        }
    }
    return $names;
}