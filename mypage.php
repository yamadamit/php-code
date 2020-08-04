<?php

require('function.php');

debug('>>>>>>>>>>');
debug('マイページ');
debug('>>>>>>>>>>');
debugLogStart();

require('auth.php');

//ーーーーーーーーーーーーーーーーーー
// 画面表示用データ取得
//ーーーーーーーーーーーーーーーーーー
//セッションから自分のユーザーIDを取得
$u_id = $_SESSION['user_id'];

//DBから自分が出品した教科書データを取得
$textData = getMyTextBooks($u_id);

//DBから連絡掲示板データを取得
$bordData = getMyMsgsAndBord($u_id);

//DBからお気に入りデータを取得
$favoData = getMyFavo($u_id);

//DBから全てのデータが取れてるかのチェックは行わない
//取れてなければ何も表示されないことにする

debug('取得した教科書データ:'.print_r($textData, true));
debug('取得したお気に入りデータ:'.print_r($favoData, true));
debug('取得した掲示板データ:'.print_r($bordData,true));

//ーーーーーーーーーーーーーーーーーー
// 画面表示用データ（サイドバー）
//ーーーーーーーーーーーーーーーーーー
//DBからサイドバーのユーザーデータを取得
$sideFormData = sideUser($_SESSION['user_id']);

debug('>>>>>>>>>>画面表示処理終了>>>>>>>>>>');

?>

<?php
$siteTitle = "マイページ";
require('head.php');
?>
<body>

  <style>
    #main{
      border: none !important;
    }
  </style>
 
  <?php
  require('header.php');
  ?>
  
  <div id="js-show-msg" class="msg-slide" style="display:none;">
    <?php echo getSessionFlash('msg_success'); ?>
  </div>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-2colum page-logined">
    <h1 class="page-title">マイページ</h1>
    
    <!-- Main -->
    <section id="main">
     
      <section class="list panel-list">
        <h2 class="title">
          お気に入り一覧
        </h2>
        <?php
          if(!empty($favoData)):
            foreach($favoData as $key => $val):
        ?>
          <a href="textbookDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['id'] : '?t_id='.$val['id']; ?>" class="panel">
            <div class="panel-head">
              <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
            </div>
            <div class="panel-body">
              <p class="panel-title"><?php echo sanitize($val['name']); ?> <spna class="price">¥<?php echo sanitize(number_format($val['price'])); ?></spna></p>
            </div>
          </a>        
        <?php
            endforeach;
          endif;
        ?>
      </section>
      
      <section class="list list-table">
        <h2 class="title">
          連絡掲示板一覧
        </h2>
        <table class="table">
          <thead>
            <tr>
              <th style="width:30%;">最新送信日時</th>
              <th>送信メッセージ</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if(!empty($bordData)){
                foreach($bordData as $key => $val){
                  if(!empty($val['msg'])){
                    $msg = array_shift($val['msg']);
            ?>
              <tr>
                <td><?php echo sanitize(date('Y.m.d H:i:s',strtotime($msg['send_date']))); ?></td>
                <td style="padding-left:20px;"><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']),0,20); ?>...</a></td>
              </tr>
            <?php
                  }
                }
              }
            ?>
          </tbody>
        </table>
      </section>
     
      <section class="list panel-list">
        <h2 class="title">
          登録商品一覧
        </h2>
        <?php
          if(!empty($textData)):
          foreach($textData as $key => $val):
        ?>
          <a href="registTextbook.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['id'] : '?t_id='.$val['id']; ?>" class="panel" style="height:500px;">
            <div class="panel-head">
              <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
            </div>
            <div class="panel-body">
              <p class="panel-title"><?php echo sanitize($val['name']); ?> <spna class="price">¥<?php echo sanitize(number_format($val['price'])); ?></spna></p>
            </div>
          </a>
        <?php
            endforeach;
          endif;
        ?>
      </section>
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