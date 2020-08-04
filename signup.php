<?php

require('function.php');

//ーーーーーーーーーーーーーーーーーー
// POST送信されていた場合
//ーーーーーーーーーーーーーーーーーー
if(!empty($_POST)){
  
  //変数にユーザー情報を代入
  $username = $_POST['username'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];
  
  //未入力チェック
  //email
  validRequired($email, 'email');
  //パスワード
  validRequired($pass, 'pass');
  //パスワード再入力
  validRequired($pass_re, 'pass_re');
  
  if(empty($err_msg)){
    
    //emailチェック
    //重複
    validEmailDup($email, 'email');
    //形式
    validEmail($email, 'email');
    //最大文字数
    validMaxLen($email, 'email');
    
    //ユーザー名チェック
    //最大文字数
    validMaxLen($username, 'username');    
    
    //パスワードチェック
    //最大文字数
    validMaxLen($pass, 'pass');
    //最小文字数
    validMinLen($pass, 'pass');
    //半角英数字
    validHalf($pass, 'pass');
    
    //パスワード再入力チェック
    //最大文字数
    validMaxLen($pass_re, 'pass_re');
    //最小文字数
    validMinLen($pass_re, 'pass_re');
    
    if(empty($err_msg)){
      
      //パスワード同値チェック
      validMatch($pass, $pass_re, 'pass_re');
      
      if(empty($err_msg)){
        
        //例外処理
        try {
          //DBへ接続
          $dbh = dbConnect();
          $sql = 'INSERT INTO users (username,email,password,login_time,create_date) VALUES(:username,:email,:pass,:login_time,:create_date)';
          
          $data = array(':username' => $username, ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
          
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          
          //クエリ成功の場合
          if($stmt){
            $sesLimit = 60*60;
            
            $_SESSION['msg_success'] = SUC07;
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            
            $_SESSION['user_id'] = $dbh->lastInsertId();
            debug('セッション変数の中身:'.print_r($_SESSION,true));
            
            //メール送信
            $name = $username ? $username : '名無し';
            $from = 'ym10012243@gmail.com';
            $to = $email;
            $subject = '【せいじぼん】ご登録、ありがとうございます!!';
            $comment = <<<EOF
{$name}さま

お世話になっております。
「せいじぼん!!」カスタマーセンター
担当の山田でございます。

この度はサイトにご登録いただき、誠にありがとうございました。

「せいじぼん!!」では、使い終わった教科書を売ったり、
誰かが使い終わった教科書を安く手に入れることが出来るサイトです。


欲しい教科書があれば、以下のサイドバーから検索してみて下さい。
http://localhost:8888/sample/index.php

教科書を売りたい場合は。以下のページから登録が可能です。
http://localhost:8888/sample/registTextbook.php

プロフィール設定は以下から可能です。
http://localhost:8888/sample/profEdit.php


引き続き、「せいじぼん!!」を宜しくお願い致します。

/////////////////////////////////////////////////
せいじぼん!! カスタマーセンター
URL；   http://localhost:8888/sample/index.php
Email： ym10012243@gmail.com
/////////////////////////////////////////////////            
EOF;
            sendMail($from, $to, $subject, $comment);
            
            //マイページへ
            header("Location:mypage.php");
          }
        } catch (Exception $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
?>
<?php
$siteTitle = "ユーザー登録";
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
          <h2 class="title" style="margin-bottom:0;">ユーザー登録</h2>
          <p style="text-align:center;">せいじぼん!!をご利用頂くには、<br>
          会員登録（無料）が必要です。<br>
          簡単な手続きをお済ませ頂きますと、<br>
          お買い物をお楽しみ頂けます。</p>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div><br>
          
          <label for="" class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            ユーザー名
            <input type="text" name="username" value="<?php if(!empty($_POST['username'])) echo $_POST['username']; ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('username');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            Email<span class="label-require">必須</span>
            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
            パスワード<span class="label-require">必須</span>
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
            パスワード（再入力）<span class="label-require">必須</span>
            <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_re');
            ?>
          </div>
          
          <div class="btn-container">
            <input type="submit" class="btn btn-mid cv-button" value="登録する">
          </div>
        </form>
      </div>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>