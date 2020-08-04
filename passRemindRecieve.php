<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>認証キー入力ページ>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();


//セッションに認証キーがあるか確認。なければリダイレクト
if(empty($_SESSION['auth_key'])){
  header("Location:passRemindSend.php");
}

//ーーーーーーーーーーーーーーーーーー
// 画面処理
//ーーーーーーーーーーーーーーーーーー
// post通信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報:'.print_r($_POST,true));
  
  //変数に認証キーを代入
  $auth_key = $_POST['token'];
  
  //未入力チェック
  validRequired($auth_key, 'token');
  
  if(empty($err_msg)){
    debug('未入力チェックOK');

    //固定長チェック
    validLength($auth_key, 'token');    
    //半角チェック
    validHalf($auth_key, 'token');
    
    if(empty($err_msg)){
      debug('バリデーションOK');
      
      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['common'] = MSG15;
      }
      
      if(time() > $_SESSION['auth_key_limit']){
        $err_msg['common'] = MSG16;
      }
      
      if(empty($err_msg)){
        debug('認証OK');
        
        $pass = makeRandKey();
        
        try{
          $dbh = dbConnect();
          
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          
          $stmt = queryPost($dbh, $sql, $data);
          
          //クエリ成功の場合
          if($stmt){
            debug('クエリ成功');
            
            //メールを送信
            $from = 'ym10012243@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】せいじぼん!!';
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力いただき、ログインください。

http://localhost:8888/sample/login.php
再発行パスワード：{$pass}

※ログイン後、下記のページにてパスワードのご変更をお願い致します。
http://localhost:8888/sample/passEdit.php

/////////////////////////////////////////////////
せいじぼん!! カスタマーセンター
URL；   http://localhost:8888/sample/index.php
Email： ym10012243@gmail.com
/////////////////////////////////////////////////
EOT;
            sendMail($from, $to, $subject, $comment);
            
            //セッション削除
            session_unset();
            $_SESSION['msg_success'] = SUC03;
            debug('セッション変数の中身；'.print_r($_SESSION,true));
            
            header("Location:login.php");
            return;
            
          }else{
            debug('クエリに失敗しました');
            $err_msg['common'] = MSG07;
          }
        } catch (Exception $e){
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
?>

<?php
$siteTitle = "パスワード再発行";
require('head.php');
?>
<body>
 
  <?php
  require('header.php');
  ?>
  <div id="js-show-msg" class="msg-slide" style="display:none;">
    <?php echo getSessionFlash('msg_success'); ?>
  </div>  
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-1colum">
    
    <!-- Main -->
    <section id="main">
      
      <div class="form-container" style="width:500px;">
        
        <form action="" class="form" method="post">
          <h2 class="title">パスワード再発行</h2>
          <p style="text-align:center;">ご指定のメールアドレスにお送りした<br>
          【パスワード再発行認証メール】内にある<br>
          「認証キー」をご入力下さい。</p>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <label for="" class="<?php if(!empty($err_msg['token'])) echo 'err'; ?>">
            認証キー
            <input type="text" name="token" value="<?php echo getFormData('token'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('token');
            ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="変更画面へ">
          </div><br><br>
          <a href="passRemindSend.php">&lt;&lt; パスワード再発行メールを再度送信する</a>          
        </form>
      </div>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>