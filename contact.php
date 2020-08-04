<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>お問い合わせページ>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報:'.print_r($_POST,true));
  
  //変数にPOST情報を格納
  $name = $_POST['name'];
  $email = $_POST['email'];
  $subject = $_POST['subject'];
  $comment = $_POST['comment'];
  
  //ーーーーーーーーーーーーーーーーーー
  //バリデーション
  //ーーーーーーーーーーーーーーーーーー
  
  //名前
  //最大文字数
  validMaxLen($name, 'name');
  
  //email
  //入力必須
  validRequired($email, 'email');
  //email形式
  validEmail($email, 'email');
  
  //件名
  //最大文字数
  validMaxLen($subject, 'subject');
  
  //内容
  //入力必須
  validRequired($comment, 'comment');
  //最大文字数
  validMaxLen($comment, 'comment');
  
  if(empty($err_msg)){
    
    
    //例外処理
    try {
      
      $dbh = dbConnect();
      
      $sql = 'INSERT INTO contact (name, email, subject, comment, create_date) VALUES(:name, :email, :subject, :comment, :create_date)';
      $data = array(':name' => $name, ':email' => $email, ':subject' => $subject, ':comment' => $comment, ':create_date' => date('Y-m-d H:i:s'));
      
      $stmt = queryPost($dbh, $sql, $data);
      
      if($stmt){
        debug('クエリ成功');
        $_SESSION['msg_success'] = SUC06;
        debug('セッション変数の中身:'.print_r($_SESSION,true));
        
        //メールを送信
        $username = $name;
        $from = 'ym10012243@gmail.com';
        $to = $email;
        $subject = '【せいじぼん!!】お問い合わせありがとうございます。';
        $comment = <<<EOT
{$username}さま

お世話になっております。
「せいじぼん!!」カスタマーセンター
担当の山田でございます。

この度はお問い合わせをいただき、誠にありがとうございました。
いただいた内容を確認後、
返信させていただきます。

※返信には最大48時間かかる場合がございます。

引き続き、「せいじぼん!!」を宜しくお願い致します。

/////////////////////////////////////////////////
せいじぼん!! カスタマーセンター
URL；   http://localhost:8888/sample/index.php
Email： ym10012243@gmail.com
/////////////////////////////////////////////////
EOT;
        sendMail($from, $to, $subject, $comment);
        
      }
      
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
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
  
  <div id="js-show-msg" class="msg-slide" style="display:none;">
    <?php echo getSessionFlash('msg_success'); ?>
  </div>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-1colum">
    
    <!-- Main -->
    <section id="main">
      
      <div class="form-container" style="width:600px;">
        
        <form action="" class="form" method="post">
          <h2 class="title">お問い合わせ</h2>
          <label for="">
            お名前
            <input type="text" name="name">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('name');
            ?>
          </div>
          
          <label for="">
            メールアドレス<span class="label-require">必須</span>
            <input type="text" name="email">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          
          <label for="">
            件名
            <input type="text" name="subject">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('subject');
            ?>
          </div>
          
          <label for="">
            内容<span class="label-require">必須</span>
            <textarea name="comment" id="js-count" cols="10" rows="40" style="height:200px;"></textarea>
          </label>
          <div class="counter-text"><span id="js-count-view">0</span>/255文字</div>           
          <div class="area-msg">
            <?php
            echo getErrMsg('comment');
            ?>
          </div>
          
          <div class="btn-container" style="margin: 0 auto;">
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