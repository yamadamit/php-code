<?php
require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>トップページ>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

//ーーーーーーーーーーーーーーーーーー
// 画像処理
//ーーーーーーーーーーーーーーーーーー
//現在のページのGETパラメータを取得
$currentPageNum = (!empty($_GET['t'])) ? $_GET['t'] : 1;

//カテゴリ
$category = (!empty($_GET['c_id'])) ? $_GET['c_id']  : '';

//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';

//パラメータに不正な値が入ってるかチェック
if(!is_int((int)$currentPageNum)){
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
}

//表示件数
$listSpan = 15;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
//DBから商品データを取得
$dbProductData = getTextbookList($currentMinNum, $category, $sort);
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();
debug('現在のページ：'.$currentPageNum);
debug('>>>>>>>>>>画面表示処理終了>>>>>>>>>>');
?>

<?php
$siteTitle = "HOME";
require('head.php');
?>

<body>
 
  <?php
  require('header.php');
  ?>
  <!-- トップ画像 -->
  <div class="hero">
   <img src="img/politics-top.png" alt="">
  </div><br><br>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-2colum">
  
   <!-- サイドバー -->
   <section id="sidebar">
     <form action="" method="get">
      
       <h1 class="title">カテゴリー</h1>
       <div class="selectbox">
         <span class="icn_select"></span>
         <select name="c_id" id="">
           <option value="0" <?php if(getFormData('c_id',true) == 0){ echo 'selected'; } ?>>選択してください</option>
           <?php
             foreach($dbCategoryData as $key => $val){
           ?>
             <option value="<?php echo $val['id'] ?>" <?php if(getFormData('c_id',true) == $val['id']){ echo 'selected'; } ?>>
               <?php echo $val['name']; ?>
             </option>
           <?php
            }
           ?>
         </select>
       </div>
       
       <h1 class="title">表示順（金額）</h1>
       <div class="selectbox">
         <span class="icn_select"></span>
         <select name="sort" id="">
           <option value="0" <?php if(getFormData('sort',true) == 0 ){ echo 'selected'; } ?>>選択してください</option>
           <option value="1" <?php if(getFormData('sort',true) == 1 ){ echo 'selected'; } ?>>金額が安い順</option>
           <option value="2" <?php if(getFormData('sort',true) == 2 ){ echo 'selected'; } ?>>金額が高い順</option>           
         </select>
       </div>
       
       <input type="submit" value="検索">
     </form>
   </section>
   
   <!-- Main -->
   <section id="main">
     <div class="search-title">
       <div class="search-left">
         <span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>件の商品が見つかりました
       </div>
       <div class="search-right">
         <span class="num"><?php echo $currentMinNum+1; ?></span> - <span class="num"><?php echo $currentMinNum+$listSpan; ?></span>件 / <span class="num"><?php echo sanitize($dbProductData['total']); ?></span>件中
       </div>
     </div>
     
     <div class="panel-list">
       <?php
          foreach($dbProductData['data'] as $key => $val):
       ?>
           <a href="textbookDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['id'] : '?t_id='.$val['id']; ?>" class="panel" style="height:500px;">
             <div class="panel-head">
               <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
             </div>
             <div class="panel-body">
               <p class="panel-title"><?php echo sanitize($val['name']); ?><span class="price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
             </div>
           </a>       
       <?php
          endforeach;
       ?>
     </div>
     
     <!-- ページネーション -->
     <?php pagination($currentPageNum, $dbProductData['total_page']); ?>
     
     <!-- ページトップ -->
     <div id="page_top"><a href="#"></a></div>
     
   </section>
    
  </div>
  
  <?php
  require('footer.php');
  ?>