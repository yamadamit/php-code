<?php
require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>教科書詳細ページ>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

//ーーーーーーーーーーーーーーーーーー
// 画面処理
//ーーーーーーーーーーーーーーーーーー
//商品IDのGETパラメータを取得
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';

//DBから教科書のデータを取得
$viewData = getTextbookOne($t_id);
//パラメータに不正な値が入っているかチェック
if(empty($viewData)){
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
}
debug('取得したDBデータ:'.print_r($viewData,true));

//DBからコンディション情報を取得
$subData = getTextbookState($t_id);
//パラメータに不正な値が入ってるかチェック
if(empty($subData)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php");
}
debug('取得したDBデータ:'.print_r($subData,true));

//ーーーーーーーーーーーーーーーーーー
// post送信されていた場合
//ーーーーーーーーーーーーーーーーーー
if(!empty($_POST['submit'])){
  debug('POST送信があります');
  
  //ログイン認証
  require('auth.php');
  
  try {
    $dbh = dbConnect();
    
    $sql = 'INSERT into bord (sale_user, buy_user, text_id,create_date) VALUES (:s_uid, :b_uid, :t_id, :date)';
    $data = array(':s_uid' => $viewData['user_id'], ':b_uid' => $_SESSION['user_id'], ':t_id' => $t_id, ':date' => date('Y-m-d H:i:s'));
    
    $stmt = queryPost($dbh, $sql, $data);
    
    if($stmt){
      $_SESSION['msg_success'] = SUC05;
      debug('連絡掲示板へ遷移します');
      header("Location:msg.php?m_id=".$dbh->lastInsertID());  //連絡掲示板へ
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }  
}
debug('>>>>>>>>>>画面表示処理終了>>>>>>>>>>');
?>
<?php
$siteTitle = "教科書の詳細ページ";
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
      
      <div class="form-container">
        
        <div class="title">
          <span class="badge"><?php echo sanitize($viewData['category']); ?></span>
          <?php echo sanitize($viewData['name']); ?>
          
          <i class="fa fa-heart icn-favo js-click-favo <?php if(getFavo($_SESSION['user_id'], $viewData['id'])){ echo 'active'; } ?>" aria-hidden="true" data-textid="<?php echo sanitize($viewData['id']); ?>"></i>
        </div>
                
        <div class="text-img-container">
          <div class="img-main">
            <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="メイン画像：<?php echo sanitize($viewData['name']); ?>" id="js-switch-img-main">
          </div>
          
          <div class="img-sub">
            <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="画像1:<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
            <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="画像2:<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
            <img src="<?php echo showImg(sanitize($viewData['pic3'])); ?>" alt="画像3:<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
          </div>
        </div>
                
        <div class="author-detail">
          <h1>基本情報</h1>
          <div>・著者名：<?php echo sanitize($viewData['author']); ?></div>
          <div>・出版社：<?php echo sanitize($viewData['publisher']); ?></div>
          <div>・コンディション：<?php echo sanitize($subData['state']); ?></div>       
        </div>
        
        <div class="text-detail">
          <h1>内容</h1>         
          <p><?php echo sanitize($viewData['comment']); ?></p>
        </div>
        
        <div class="text-buy">
          <div class="item-left">
            <a href="index.php<?php echo appendGetParam(array('t_id')); ?>">&lt;&lt; 教科書一覧に戻る</a>
          </div>
          <form action="" method="post"> <!-- formタグを追加し、ボタンをinputに変更し、styleを追加 -->
            <div class="item-right">
              <input type="submit" value="買う！" name="submit" class="btn btn-primary cv-button" style="margin-top:0;">
            </div>
          </form>
          <div class="item-right">
            <div class="price">¥<?php echo sanitize(number_format($viewData['price'])); ?></div>
          </div>
        </div>
      </div>
    </section>
  </div>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>