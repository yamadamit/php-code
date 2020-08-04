<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>パスワード再発行メール送信ページ>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  
  //変数にPOST情報を代入
  $email = $_POST['email'];
  
  validRequired($email, 'email');
  
  if(empty($err_msg)){
    debug('未入力チェックOK');
    
    //email形式チェック
    validEmail($email, 'email');
    //email最大文字数チェック
    validMaxLen($email, 'email');
          
    if(empty($err_msg)){
      debug('バリデーションOK');

      try{
        $dbh = dbConnect();

        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);

        $stmt = queryPost($dbh, $sql, $data);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt && array_shift($result)){
          debug('クエリ成功。DB登録あり');
          $_SESSION['msg_success'] = SUC03;
          $auth_key = makeRandKey();  //認証キー生成

          //メールを送信
          $from = 'ym10012243@gmail.com';
          $to = $email;
          $subject = '【パスワード再発行認証】せいじぼん!!';
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力いただくとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：
http://localhost:8888/sample/passRemindRecieve.php

認証キー：{$auth_key}
※認証キーの有効期限は30分となります。

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/sample/passRemindSend.php

/////////////////////////////////////////////////
せいじぼん!! カスタマーセンター
URL；   http://localhost:8888/sample/index.php
Email： ym10012243@gmail.com
/////////////////////////////////////////////////

EOT;
          sendMail($from, $to, $subject, $comment);

          //認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time()+(60*30);
          debug('セッション変数の中身:'.print_r($_SESSION,true));

          header("Location:passRemindRecieve.php");

        }else{
          debug('クエリに失敗したかDBに登録のないEmailが入力されました');
          $err_msg['common'] = MSG07;
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
$siteTitle = "パスワード再発行";
require('head.php');
?>
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
          <h2 class="title">パスワード再発行</h2>
          <p>ご指定のメールアドレス宛に<br>
          パスワード再発行用のURLと認証キーをお送りします。</p>
          
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            Email
            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div><br>
          
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="送信する">
          </div>
        </form>
      </div>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>