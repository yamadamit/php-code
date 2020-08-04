<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('log_errors', 'on');
ini_set('error_log', 'php.log');

//ーーーーーーーーーーーーーーーーーー
// セッション
//ーーーーーーーーーーーーーーーーーー
//セッションファイルの置き場を変更する
session_save_path("/var/tmp");
//ガーベージコレクションが削除するセッションの有効期限を設定
ini_set('session.gc_maxlifetime', 60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える
session_regenerate_id();

//ーーーーーーーーーーーーーーーーーー
// デバッグ
//ーーーーーーーーーーーーーーーーーー
//デバッグフラグ
$debug_flg = true;

//デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ:'.$str);
  }
}

//画面表示処理ログ吐き出し関数
function debugLogStart(){
  debug('>>>>>>>>>>画面表示処理開始>>>>>>>>>>');
  debug('セッションID:'.session_id());
  debug('セッション変数の中身:'.print_r($_SESSION,true));
  debug('現在日時タイムスタンプ:'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン期限日時タイムスタンプ:'.($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//ーーーーーーーーーーーーーーーーーー
// 定数
//ーーーーーーーーーーーーーーーーーー
//エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '255文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角で入力してください');
define('MSG18', 'URLの形式が違います');

//サクセスメッセージを定数に設定
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '教科書の情報を登録しました。');
define('SUC05', 'メッセージを送って、相手と連絡を取りましょう');
define('SUC06', 'お問い合わせいただきありがとうございます。');
define('SUC07', 'ご登録、ありがとうございます！');

//ーーーーーーーーーーーーーーーーーー
// バリデーション関数
//ーーーーーーーーーーーーーーーーーー
//エラーメッセージ格納用の配列
$err_msg = array();

//未入力チェック
function validRequired($str, $key){
  if(empty($str)){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

//Email形式チェック
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

//Email重複チェック
function validEmailDup($email){
  global $err_msg;
  //例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email = :email';
    $data = array(':email' => $email);
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//同値チェック
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

//最小文字数チェック
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

//最大文字数チェック
function validMaxLen($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

//半角チェック
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

//電話番号形式チェック
function validTel($str, $key){
  if(!preg_match("/\A(((0(\d{1}[-(]?\d{4}|\d{2}[-(]?\d{3}|\d{3}[-(]?\d{2}|\d{4}[-(]?\d{1}|[5789]0[-(]?\d{4})[-)]?)|\d{1,4}\-?)\d{4}|0120[-(]?\d{3}[-)]?\d{3})\z/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}

//郵便番号形式チェック
function validZip($str, $key){
  if(!preg_match("/^\d{3}[-]\d{4}$|^\d{3}[-]\d{2}$|^\d{3}$|^\d{5}$|^\d{7}$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}

//半角数字チェック
function validNumber($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG17;
  }
}

//パスワードチェック
function validPass($str, $key){
  //半角英数字
  validHalf($str, $key);
  //最大文字数
  validMaxLen($str, $key);
  //最小文字数
  validMinLen($str, $key);
}

//固定長チェック
function validLength($str, $key, $len = 8){
  if(mb_strlen($str) !== $len){
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}

//セレクトボックスチェック
function validSelect($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}

// URLチェック
function validUrl($str, $key){
  if(!preg_match("/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG18;
  }
}

//ーーーーーーーーーーーーーーーーーー
// DB接続関数
//ーーーーーーーーーーーーーーーーーー
//DB接続
function dbConnect(){
  // DBへの接続準備
  $dsn = 'mysql:dbname=politicsbook;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  $dbh = new PDO($dsn ,$user, $password, $options);
  return $dbh;
}

//SQL実行関数
//function queryPost($dbh, $sql, $data){
//  $stmt = $dbh->prepare($sql);
//  $stmt->execute($data);
//  return $stmt;
//}
function queryPost($dbh, $sql, $data){
  //クエリ作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました');
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功');
  return $stmt;
}

//ユーザー情報を取得
function getUser($u_id){
  debug('ユーザー情報を取得します');
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    
    $stmt = queryPost($dbh, $sql, $data);
    
    //クエリ成功の場合
//    if($stmt){
//      debug('クエリ成功');
//    }else{
//      debug('クエリに失敗しました');
//    }
    //クエリ結果のデータを１レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function sideUser($u_id){
  debug('サイドバーのユーザー情報を取得します');
  try {
    $dbh = dbConnect();
    $sql = 'SELECT id, username, pic FROM users WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

//教科書の情報を取得
function getTextbook($u_id, $t_id){
  debug('教科書の情報を取得します');
  debug('ユーザーID:'.$u_id);
  debug('教科書ID'.$t_id);
  
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM textbook WHERE user_id = :u_id AND id = :t_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':t_id' => $t_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
  }
}

//カテゴリ情報を取得
function getCategory(){
  debug('カテゴリ情報を取得します');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//教科書の一覧表示情報を取得
function getTextbookList($currentMinNum = 1, $category, $sort, $span = 15){
  debug('教科書の一覧表示情報を取得します');
  try{
    $dbh = dbConnect();
    
    //件数用のSQL作成
    $sql = 'SELECT id FROM textbook';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $data = array();
    
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount();
    $rst['total_page'] = ceil($rst['total']/$span);
    if(!$stmt){
      return false;
    }    
    
    //ページング用のSQL文作成
    $sql = 'SELECT * FROM textbook';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $sql .= ' LIMIT '.$span. ' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL：'.$sql);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      //クエリ結果のデータを全レコード格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

//教科書とカテゴリ情報を取得
function getTextbookOne($t_id){
  debug('教科書とカテゴリ情報を取得します');
  debug('教科書ID:'.$t_id);
  try {
    
    //DBへ接続
    $dbh = dbConnect();
    
    //SQL文作成
    $sql = 'SELECT t.id, t.name, t.author, t.publisher, t.comment, t.price, t.pic1, t.pic2, t.pic3, t.user_id, t.create_date, t.update_date, c.name AS category
            FROM textbook AS t LEFT JOIN category AS c ON t.category_id = c.id WHERE t.id = :t_id AND t.delete_flg = 0 AND c.delete_flg = 0';
    
    //クエリ実行
    $data = array('t_id' => $t_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

function getTextbookState($t_id){
  debug('教科書とコンディション情報を取得します');
  debug('テキストID:'.$t_id);
  try {
    
    $dbh = dbConnect();
    
    $sql = 'SELECT t.id, t.name, s.name AS state FROM textbook AS t LEFT JOIN state AS s ON t.state_id = s.id WHERE t.id = :t_id AND t.delete_flg = 0 AND s.delete_flg = 0';
    
    $data = array('t_id' => $t_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

//掲示板とメッセージ情報を取得します
function getMsgsAndBord($id){
  debug('掲示板とメッセージ情報を取得します');
  debug('掲示板ID:'.$id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    
    //SQL文作成
    $sql = 'SELECT m.id AS m_id, text_id, bord_id, send_date, to_user, from_user, sale_user, buy_user, msg, b.create_date FROM message AS m RIGHT JOIN bord AS b ON b.id = m.bord_id WHERE b.id = :id ORDER BY send_date ASC';
    $data = array(':id' => $id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

//お気に入り情報の取得
function getFavo($u_id, $t_id){
  debug('お気に入り情報があるか確認します');
  debug('ユーザーID:'.$u_id);
  debug('教科書ID:'.$t_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favo WHERE text_id = :t_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':t_id' => $t_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt->rowCount()){
      debug('お気に入りです');
      return true;
    }else{
      debug('特に気に入っていません');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

//自分が出品した教科書情報を取得
function getMyTextBooks($u_id){
  debug('自分が出品した教科書情報を取得します');
  debug('ユーザーID:'.$u_id);
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM textbook WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

//自分のお気に入り情報を取得
function getMyFavo($u_id){
  debug('自分のお気に入り情報を取得します');
  debug('ユーザーID:'.$u_id);
  try {
    
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favo AS f LEFT JOIN textbook AS t ON f.text_id = t.id WHERE f.user_id = :u_id';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

function getMyMsgsAndBord($u_id){
  debug('自分の掲示板とメッセージ情報を取得します');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM bord AS b WHERE b.sale_user = :id OR b.buy_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    if(!empty($rst)){
      foreach($rst as $key => $val){
        $sql = 'SELECT * FROM message WHERE bord_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':id' => $val['id']);
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }
    if($stmt){
      return $rst;
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}

//
function getState(){
  debug('商品の状態を取得します。');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM state';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}


//ーーーーーーーーーーーーーーーーーー
// その他の関数
//ーーーーーーーーーーーーーーーーーー
//エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}

///フォーム入力保持
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  //ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($_POST[$str])){
        return $_POST[$str];
      }else{
        //ない場合はDBの情報を表示
        return $dbFormData[$str];
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合
      if(isset($_POST[$str]) && $_POST[$str] !== $dbFormData[$str]){
        return $_POST[$str];
      }else{
        return $dbFormData[$str];
      }
    }
  }else{
    if(isset($_POST[$str])){
      return $_POST[$str];
    }
  }
}

//フォーム入力保持（サイドバー用）
function getSideData($str){
  global $sideFormData;
  //ユーザーデータがある場合
  if(!empty($sideFormData)){
    if(!empty($err_msg[$str])){
      if(isset($_POST[$str])){
        return $_POST[$str];
      }else{
        return $sideFormData[$str];
      }
    }else{
      if(isset($_POST[$str]) && $_POST[$str] !== $sideFormData[$str]){
        return $_POST[$str];
      }else{
        return $sideFormData[$str];
      }
    }
  }else{
    if(isset($_POST[$str])){
      return $_POST[$str];
    }
  }
}



//メール送信
function sendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
    
    //文字化けしないように設定
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");
    
    $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
    if($result){
      debug('メールを送信しました');
    }else{
      debug('【エラー発生】メールの送信に失敗しました');
    }
  }
}

//セッションを１回だけ取得
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

//認証キー生成
function makeRandKey($length = 8){
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i){
    $str .= $chars[mt_rand(0,61)];
  }
  return $str;
}

//画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報:'.print_r($file,true));
  
  if(isset($file['error']) && is_int($file['error'])) {
    try {
      //バリデーション
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }
      
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        throw new RuntimeException('画像形式が未対応です');
      }
      
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      
      if (!move_uploaded_file($file['tmp_name'], $path)){
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      
      //保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);
      
      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;
      
    } catch (Exception $e) {
      
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

//サニタイズ
function sanitize($str){
  return htmlspecialchars($str, ENT_QUOTES);
}

//ページング
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  if($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }
  
  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?t=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i){ echo 'active';} 
        echo '"><a href="?t='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?t='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}

//画像表示用関数
function showImg($path){
  if(empty($path)){
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}

//画像表示用関数（サイドバーのプロフ用）
function showImgSub($path){
  if(empty($path)){
    return 'img/sample-img-prof.png';
  }else{
    return $path;
  }
}

//GETパラメータ付与
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){
        $str .= $key. '='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

//ログイン認証
function isLogin(){
  if( !empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです');
    
    //現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです');
      
      //セッションを削除（ログアウト）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      return true;
    }
  }else{
    debug('未ログインユーザーです');
    return false;
  }
}


?>