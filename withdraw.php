<?php

require('function.php');

debug('>>>>>>>>>>');
debug('退会ページ');
debug('>>>>>>>>>>');
debugLogStart();

require('auth.php');

//ーーーーーーーーーーーーーーーーーー
// 画面処理
//ーーーーーーーーーーーーーーーーーー
//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
    $sql2 = 'UPDATE text SET delete_flg = 1 WHERE user_id = :us_id';
    $sql3 = 'UPDATE favo SET delete_flg = 1 WHERE user_id = :us_id';
    
    //データ流し込み
    $data = array(':us_id' => $_SESSION['user_id']);
    //クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);
    
    if($stmt1){
      session_destroy();
      debug('セッション変数の中身:'.print_r($_SESSION,true));
      debug('トップページへ遷移します');
      header("Location:index.php");
    }else{
      debug('クエリが失敗しました');
      $err_msg['common'] = MSG07;
    }
    
  } catch (Exception $e){
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug('>>>>>>>>>>画面表示処理終了>>>>>>>>>>');
?>

<?php
$siteTitle = "アカウント削除";
require('head.php');
?>

<style>
  .form{
    text-align: center;
  }
  
  .form .btn{
    float: none;
  }
</style>
<body>
 
  <?php
  require('header.php');
  ?>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-1colum">
    
    <!-- Main -->
    <section id="main">
      <div class="form-container" style="width:500px;">
        <form action="" class="form" method="post">
          <h2 class="title">アカウント削除</h2>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <div>アカウントを削除した場合、<br>
          これまで登録してきた教科書の情報も<br>
          全て削除されてしまいます。<br>
          それでも削除しますか？</div><br>
          <div class="btn-container" style="margin-top:20px;">
            <p style="font-size:12px;">
              ※アカウントを削除する際は、<br>
              以下のボタンにチェックを入れてください。
            </p>           
            <input type="checkbox" id="check">
          </div><br>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid withdraw-submit" value="削除する" name="submit" disabled="disabled">
          </div>
        </form>
      </div>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>