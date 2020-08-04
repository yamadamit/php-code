<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>パスワード変更>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

require('auth.php');

//ーーーーーーーーーーーーーーーーーー
// 画面表示用データ（サイドバー）
//ーーーーーーーーーーーーーーーーーー
//DBからサイドバーのユーザーデータを取得
$sideFormData = sideUser($_SESSION['user_id']);

//ーーーーーーーーーーーーーーーーーー
// 画面処理
//ーーーーーーーーーーーーーーーーーー
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData,true));

//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  
  //変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];
  
  //未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');
  
  if(empty($err_msg)){
    debug('未入力チェックOK');
    
    //古いパスワードのチェック
    validPass($pass_old, 'pass_old');
    //新しいパスワードのチェック
    validPass($pass_new, 'pass_new');
    
    //古いパスワードとDBパスワードを照合
    if(!password_verify($pass_old, $userData['password'])){
      $err_msg['pass_old'] = MSG12;
    }
    
    //古いパスワードと新しいパスワードが同じかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG13;
    }
    
    //パスワードとパスワード再入力が合っているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');
    
    if(empty($err_msg)){
      debug('バリデーションOK');
      
      //例外処理
      try{
        
        //DBへ接続
        $dbh = dbConnect();
        
        //SQL文作成
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
          debug('クエリ成功');
          $_SESSION['msg_success'] = SUC01;
          
          //メールを送信
          $username = ($userData['username']) ? $userData['username'] : '名無し';
          $from = 'ym10012243@gmail.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知|せいじぼん!!';
          $comment = <<<EOT
{$username} さん
パスワードが変更されました。

/////////////////////////////////////////////////
せいじぼん!! カスタマーセンター
URL；   http://localhost:8888/sample/index.php
Email： ym10012243@gmail.com
/////////////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);
          
          header("Location:mypage.php");
        }
        
      } catch (Exception $e){
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

?>

<?php
$siteTitle = "パスワード変更";
require('head.php');
?>
<body>
 
  <?php
  require('header.php');
  ?>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-2colum page-logined">
    <h1 class="page-title">パスワード変更</h1>
    
    <!-- Main -->
    <section id="main" style="min-height:500px;">
      <div class="form-container">
        <form action="" class="form" style="width:400px;" method="post">
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
         
          <label for="" class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
            古いパスワード
            <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_old');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
            新しいパスワード
            <input type="password" name="pass_new" value="<?php echo getFormData('pass_new'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_new');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
            新しいパスワード（再入力）
            <input type="password" name="pass_new_re" value="<?php echo getFormData('pass_new_re'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_new_re');
            ?>
          </div>
          
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="変更する">
          </div>
        </form>
      </div>
      
    </section>
    
    <!-- サイドバー -->
    <?php
    require('sidebar.php');
    ?>
  </div><br>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>  