<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>連絡掲示板>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

//ーーーーーーーーーーーーーーーーーー
// 変数の設定
//ーーーーーーーーーーーーーーーーーー
$partnerUserId = '';
$partnerUserInfo = '';
$myUserInfo = '';
$textbookInfo = '';
$viewData = '';

//ーーーーーーーーーーーーーーーーーー
// 画面表示
//ーーーーーーーーーーーーーーーーーー
// GETパラメータを取得
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';

//DBから掲示板とメッセージデータを取得
$viewData = getMsgsAndBord($m_id);
debug('取得したDBデータ:'.print_r($viewData,true));

//パラメータに不正な値が入ってるかチェック
if(empty($viewData)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:mypage.php");
}

//教科書の情報を取得
$textbookInfo = getTextbookOne($viewData[0]['text_id']);
debug('取得したDBデータ:'.print_r($textbookInfo,true));

//教科書の情報が入ってるかチェック
if(empty($textbookInfo)){
  error_log('エラー発生:教科書の情報が取得できませんでした');
  header("Location:mypage.php");
}

//viewDataから取引相手のユーザーIDを取り出す
$dealUserIds[] = $viewData[0]['sale_user'];
$dealUserIds[] = $viewData[0]['buy_user'];
if(($key = array_search($_SESSION['user_id'],$dealUserIds)) !== false){
  unset($dealUserIds[$key]);
}

$partnerUserId = array_shift($dealUserIds);
debug('取得した相手のユーザーID:'.$partnerUserId);

//DBから取引相手のユーザー情報を取得
if(isset($partnerUserId)){
  $partnerUserInfo = getUser($partnerUserId);
}

//相手のユーザー情報が取れたかチェック
if(empty($partnerUserInfo)){
  error_log('エラー発生:相手のユーザー情報が取得できませんでした');
  header("Location:mypage.php");
}

//DBから自分のユーザー情報を取得
$myUserInfo = getUser($_SESSION['user_id']);
debug('取得したユーザーデータ:'.print_r($partnerUserInfo,true));

//自分のユーザー情報が取れたかチェック
if(empty($myUserInfo)){
  error_log('エラー発生:自分のユーザー情報が取得できませんでした');
  header("Location:mypage.php");
}

//ーーーーーーーーーーーーーーーーーー
// POST送信
//ーーーーーーーーーーーーーーーーーー
if(!empty($_POST)){
  debug('POST送信があります');
  
  //ログイン認証
  require('auth.php');
  
  //バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  //最大文字数
  validMaxLen($msg, 'msg', 500);
  validRequired($msg, 'msg');
  
  if(empty($err_msg)){
    debug('バリデーションOKです');
    
    try {
      // DBへ接続
      $dbh = dbConnect();
      //SQL文作成
      $sql = 'INSERT INTO message (bord_id, send_date, to_user, from_user, msg, create_date) VALUES (:b_id, :send_date, :to_user, :from_user, :msg, :date)';
      $data = array(':b_id' => $m_id, ':send_date' => date('Y-m-d H:i:s'), ':to_user' => $partnerUserId, ':from_user' => $_SESSION['user_id'], ':msg' => $msg, ':date' => date('Y-m-d H:i:s'));
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      
      //クエリ成功の場合
      if($stmt){
        $_POST = array(); //postをクリア
        debug('連絡掲示板へ遷移します');
        header("Location:" . $_SERVER['PHP_SELF'].'?m_id='.$m_id);  //自分自身に遷移
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }  
  }
}
debug('>>>>>>>>>>画面表示処理終了>>>>>>>>>>');
?>

<?php
$siteTitle = "連絡掲示板";
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
      <div class="msg-info">
        <div class="avatar-img">
          <img src="<?php echo showImg(sanitize($partnerUserInfo['pic'])); ?>" alt="" class="avatar">
        </div>
        <div class="avatar-info">
          取引相手：<?php echo sanitize($partnerUserInfo['username']).' ('.sanitize($partnerUserInfo['age']).'歳)' ?><br>
          〒：<?php echo substr($partnerUserInfo['zip'],0,3)."-".substr($partnerUserInfo['zip'],3); ?><br>
          居住地：<?php echo sanitize($partnerUserInfo['addr']); ?><br>
          Tel: <?php echo sanitize($partnerUserInfo['tel']); ?>
        </div>
        
        <div class="text-info">
          <div class="left">
            取引商品<br>
            <img src="<?php echo showImg(sanitize($textbookInfo['pic1'])); ?>" alt="" height="70px" width="auto">
          </div>
          <div class="right">
            <?php echo sanitize($textbookInfo['name']); ?><br>
            取引金額：<span class="price">¥<?php echo number_format($textbookInfo['price']); ?></span><br>
            取引開始日：<?php echo date('Y/m/d', strtotime(sanitize($viewData[0]['create_date']))); ?>
          </div>
        </div>
      </div>
      
      <div class="area-bord" id="js-scroll-bottom">
        
        <?php
          if(!empty($viewData[0]['m_id'])){
            foreach($viewData as $key => $val){
              if(!empty($val['from_user']) && $val['from_user'] == $partnerUserId){
          ?>
                <div class="msg-cnt msg-left">
                  <div class="avatar">
                    <img src="<?php echo sanitize(showImg($partnerUserInfo['pic'])); ?>" alt="" class="avatar">
                  </div>
                  <p class="msg-inrTxt">
                    <span class="triangle"></span>
                    <?php echo sanitize($val['msg']); ?>
                  </p>
                  <div style="font-size:.5em;"><?php echo sanitize($val['send_date']); ?></div>
                </div>
          <?php
              }else{        
          ?>               
              <div class="msg-cnt msg-right">
                <div class="avatar">
                  <img src="<?php echo sanitize(showImg($myUserInfo['pic'])); ?>" alt="" class="avatar">
                </div>
                <p class="msg-inrTxt">
                  <span class="triangle"></span>
                  <?php echo sanitize($val['msg']); ?>
                </p>
                <div style="font-size:.5em;text-align:right;"><?php echo sanitize($val['send_date']); ?></div>
              </div>
          <?php
                }
              }
            }else{
          ?>
         <p style="text-align:center;line-height:20px;">メッセージ投稿はまだありません</p>
        <?php
          }        
        ?>
              
      </div>
      <div class="area-send-msg">
        <form action="" method="post">
          <textarea name="msg" id="" cols="30" rows="3"></textarea>
          <input type="submit" value="送信" class="btn btn-send">
          <div class="area-msg" style="color:red;text-align:center;">
            <?php
            echo getErrMsg('msg');
            ?>
          </div>        
        </form>
      </div>
    </section>
  </div><br>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>