<?php

require('function.php');

debug('>>>>>>>>>>');
debug('ログインページ');
debug('>>>>>>>>>>');
debugLogStart();

//ログイン認証
require('auth.php');

//ーーーーーーーーーーーーーーーーーー
// 画面処理
//ーーーーーーーーーーーーーーーーーー
if(!empty($_POST)){
  debug('POST送信があります');
  
  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;
  
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  
  //emailのチェック
  //形式
  validEmail($email, 'email');
  //最大文字数
  validMaxLen($email, 'email');
  
  //パスワードのチェック
  //最大文字数
  validMaxLen($pass, 'pass');
  //最小文字数
  validMinLen($pass, 'pass');
  //半角英数字
  validHalf($pass, 'pass');
  
  if(empty($err_msg)){
    debug('バリデーションOKです');
    
    //例外処理
    try {
      $dbh = dbConnect();
      $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      
      $stmt = queryPost($dbh, $sql, $data);
      
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      
      debug('クエリ結果の中身:'.print_r($result,true));
      
      //パスワード照合
      if(!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワードがマッチしました');
        
        //ログイン有効期限
        $sesLimit = 60*60;
        //最終ログイン日時を現在日時に
        $_SESSION['login_date'] = time();
        
        //ログイン保持にチェックがある場合
        if($pass_save){
          debug('ログイン保持にチェックがあります');
          
          //ログイン有効期限を30日にしてセット
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        }else{
          debug('ログイン保持にチェックはありません');
          
          $_SESSION['login_limit'] = $sesLimit;
        }
        
        $_SESSION['user_id'] = $result['id'];
        
        debug('セッション変数の中身:'.print_r($_SESSION,true));
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }else{
        debug('パスワードがアンマッチです');
        $err_msg['common'] = MSG09;
      } 
    } catch (Exception $e){
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了>>>>>>>>>>');
?>


<?php
$siteTitle = "ログイン";
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
          <h2 class="title">ログイン</h2>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            Email
            <input type="text" name="email" value="<?php if(!empty($_POST['email'])); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
            パスワード
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass');
            ?>
          </div>
          
          <label for="">
            <input type="checkbox" name="pass_save">次回ログインを省略する
          </label>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="登録する">
          </div>
          パスワードを忘れた方は<a href="passRemindSend.php" style="text-decoration:underline;">コチラ</a>
        </form>
      </div>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>