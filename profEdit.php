<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>プロフィール編集>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

require('auth.php');

//ーーーーーーーーーーーーーーーーーー
// 画面処理
//ーーーーーーーーーーーーーーーーーー
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($dbFormData,true));

//ーーーーーーーーーーーーーーーーーー
// 画面表示用データ（サイドバー）
//ーーーーーーーーーーーーーーーーーー
//DBからサイドバーのユーザーデータを取得
$sideFormData = sideUser($_SESSION['user_id']);

//ーーーーーーーーーーーーーーーーーー
// post送信されていた場合
//ーーーーーーーーーーーーーーーーーー
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報:'.print_r($_FILES,true));
  
  //変数にユーザー情報を代入
  $username = $_POST['username'];
  $tel = $_POST['tel'];
  $zip = (!empty($_POST['zip'])) ? $_POST['zip'] : 0;
  $addr = $_POST['addr'];
  $age = $_POST['age'];
  $email = $_POST['email'];
  $twitter = $_POST['twitter'];
  $line = $_POST['line'];
  $url = $_POST['url'];
  
  //画像をアップロードし、パスを格納
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
  //編集の場合、画像をPOSTしてないが既にDBに登録されてる場合、DBのパスを入れる
  $pic = ( empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
  
  //ーーーーーーーーーーーーーーーーーー
  // DBの情報と入力情報が異なる場合に
  // バリデーションを行う
  //ーーーーーーーーーーーーーーーーーー
  //名前の最大文字数チェック
  if($dbFormData['username'] !== $username){
    validMaxLen($username, 'username');
  }
  
  //電話番号形式チェック
  if((!empty($tel)) && ($dbFormData['tel'] !== $tel)){
    validTel($tel, 'tel');
  }
  
  //住所の最大文字数チェック
  if($dbFormData['addr'] !== $addr){
    validMaxLen($addr, 'addr');
  }
  
  //郵便番号形式チェック
  if((!empty($zip)) && ((int)$dbFormData['zip'] !== $zip)){
    validZip($zip, 'zip');
  }
  
  //年齢
  if((!empty($age) && ($dbFormData['age'] !== $age))){
    //半角数字チェック
    validNumber($age, 'age');
    //最大文字数
    validMaxLen($age, 'age');
  }
  
  //email
  if($dbFormData['email'] !== $email){
    //未入力
    validRequired($email, 'email');
    //形式
    validEmail($email, 'email');
    //最大文字数
    validMaxLen($email, 'email');
    //重複
    if(empty($err_msg['email'])){
      validEmailDup($email);
    }
  }
  
  //twitterアカウント
  if((!empty($line)) && ($dbFormData['line'] !== $line)){
    //最大文字数
    validMaxLen($line, 'line');
    //半角
    validHalf($line, 'line');
  }
  
  //LINEアカウント
  if((!empty($twitter)) && ($dbFormData['twitter'] !== $twitter)){
    //最大文字数
    validMaxLen($twitter, 'twitter');
    //半角
    validHalf($twitter, 'twitter');
  }
  
  //URL
  if((!empty($url)) && ($dbFormData['url'] !== $url)){
    //URL形式
    validUrl($url, 'url');
  }
  
  if(empty($err_msg)){
    debug('バリデーションOKです');
    
    try {
      $dbh = dbConnect();
      $sql = 'UPDATE users SET username = :u_name, tel = :tel, zip = :zip, addr = :addr, age = :age, email = :email, twitter = :twitter, line = :line, url = :url, pic = :pic WHERE id = :u_id';
      $data = array(':u_name' => $username, ':tel' => $tel, ':zip' => $zip, ':addr' => $addr, ':age' => $age, ':email' => $email, ':twitter' => $twitter, ':line' => $line, ':url' => $url, ':pic' => $pic, ':u_id' => $dbFormData['id']);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      
      //クエリ成功の場合
      if($stmt){
        debug('クエリ成功');
        $_SESSION['msg_success'] = SUC02;
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }
      
    } catch (Exception $e){
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('>>>>>>>>>>画面表示処理終了>>>>>>>>>>')
?>

<?php
$siteTitle = "プロフィール編集";
require('head.php');
?>
<body>
 
  <?php
  require('header.php');
  ?>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-2colum page-logined">
    <h1 class="page-title">プロフィール編集</h1>
    
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" class="form" style="width:400px;" method="post" enctype="multipart/form-data">
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
                   
          <label for="" class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            名前
            <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('username');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['tel'])) echo 'err'; ?>">
            TEL
            <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('tel');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['zip'])) echo 'err'; ?>">
            郵便番号
            <input type="text" name="zip" value="<?php echo getFormData('zip'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('zip');
            ?>
          </div>          
          
          <label for="" class="<?php if(!empty($err_msg['addr'])) echo 'err'; ?>">
            住所
            <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('addr');
            ?>
          </div>                    
          
          <label for="" class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
            年齢
            <input type="number" name="age" value="<?php echo getFormData('age'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('age');
            ?>
          </div>                    
          
          <label for="" class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            email<span class="label-require">必須</span>
            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['twitter'])) echo 'err'; ?>">
            Twitterアカウント
            <input type="text" name="twitter" value="<?php echo getFormData('twitter'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('twitter');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['line'])) echo 'err'; ?>">
            LINEアカウント
            <input type="text" name="line" value="<?php echo getFormData('line'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('line');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['url'])) echo 'err'; ?>">
            URL
            <input type="text" name="url" value="<?php echo getFormData('url'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('url');
            ?>
          </div>
          
          プロフィール画像
          <div style="font-size:12px;">※クリックすると、画像を選択できます。</div>          
          <label for="" class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:370px;line-height:370px;">
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            <input type="file" class="input-file" name="pic">
            <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
              ドラッグ＆ドロップ
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pic');
            ?>
          </div><br><br>
          
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
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>  