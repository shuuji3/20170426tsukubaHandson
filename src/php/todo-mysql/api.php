<?php
//javascriptからajax非同期通信で呼ばれるapiプログラム

//アクセスはすべてjson、utf-8で返す
header("Content-Type: application/json; charset=utf-8");

//mysqlへの接続情報
$mysql_host = 'yourhost';
$mysql_user = 'dbuser';
$mysql_pass = 'dbpass';
$mysql_db_name = 'dbname';


//MySQL in Appを利用する場合、サーバー情報などは環境変数に入っているのでこれを利用する
//環境変数から接続文字列を取得
$conn_str = $_SERVER["MYSQLCONNSTR_localdb"];
//正規表現でキーとバリューを取得
$out = array();
$r = preg_match_all("/([^=]+)=([^;]+);?/", $conn_str, $out, PREG_SET_ORDER);

$map = array();
foreach ($out as $set){
    $map[$set[1]] = $set[2];
}
//キーバリューにしたものを変数に格納
$mysql_host = $map["Data Source"];
$mysql_user = $map["User Id"];
$mysql_pass = $map["Password"];
$mysql_db_name = $map["Database"];
$mysql_db_name = $map["Database"];


//もしリクエストがhttp getメソッドなら
if($_SERVER["REQUEST_METHOD"] == "POST"){
    //httpステータスコードを200に
    http_response_code(200);
    $param = $_POST['param'];
    $eventType = $param['type'];
    //データベースサーバーへ接続
    $sql = mysql_connect($mysql_host,$mysql_user,$mysql_pass);
    //データベースを選択
    $todoDb = mysql_select_db(mysql_db_name, $sql);
    mysql_set_charset('utf-8');
    
    //リクエストがどのタイプかによって処理を変更
    switch($eventType){
        case 'sync':
            sync($param);
        break;
        case 'add':
            add($param);
        break;
        case 'remove':
            remove($param);
        break;
    }
    print(mysql_error($sql));
    mysql_close($sql);
}else{
    //httpステータスコードを400に
    http_response_code(400);
}

//タスク一覧を取得する関数
function sync($param){
    $result = array();
    //selectクエリーを使用し、結果をarrayに入れてjsonとして返す
    $sqlresult = mysql_query('select * from todo');
    while($i =mysql_fetch_assoc($sqlresult)){
        array_push($result,array('task_name'=>$i['name'],'task_date'=>$i['date'],'task_id'=>$i['id']));
    }
    print(json_encode($result));
}

//タスクを追加する関数
function add($param){
    //insertクエリによってデータを追加
    $task = $param['task'];
    $name = $task['task_name'];
    $date = $task['task_date'];
    $result = mysql_query('insert into todo(name,date) values ("'.$name.'","'.$date.'")');
    print('{"message":"success"}');
}

//タスクを削除する関数
function remove($param){
    //idからdeleteクエリによって削除
    $task_id = $param['id'];
    $result = mysql_query("delete from todo where id = '".$task_id."'");
    print('{"message":"success"}');
}

?>
