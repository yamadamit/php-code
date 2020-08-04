<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>> Ajax読み込み >>>>>>>>>>');
debug('>>>>>>>>>>');

//ーーーーーーーーーーーーーーーーーー
// Ajax処理
//ーーーーーーーーーーーーーーーーーー
// postがあり、ユーザーIDがあり、ログインしている場合
if(isset($_POST['textId']) && isset($_SESSION['user_id']) && isLogin()){
  debug('POST送信があります');
  $t_id = $_POST['textId'];
  debug('教科書ID:'.$t_id);
  
  try {
    
    $dbh = dbConnect();
    
    $sql = 'SELECT * FROM favo WHERE text_id = :t_id AND user_id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':t_id' => $t_id);
    
    $stmt = queryPost($dbh, $sql, $data);
    $resultCount = $stmt->rowCount();
    debug($resultCount);
    
    //レコードが１件でもある場合
    if(!empty($resultCount)){
      //レコードを削除する
      $sql = 'DELETE FROM favo WHERE text_id = :t_id AND user_id = :u_id';
      $data = array(':u_id' => $_SESSION['user_id'], ':t_id' => $t_id);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
    }else{
      //レコードを插入する
      $sql = 'INSERT INTO favo (text_id, user_id, create_date) VALUES(:t_id, :u_id, :date)';
      $data = array(':u_id' => $_SESSION['user_id'], ':t_id' => $t_id, ':date' => date('Y-m-d H:i:s'));
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
    }
    
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }  
}


?>