<?php

require('function.php');

debug('>>>>>>>>>>');
debug('>>>>>>>>>>教科書登録ページ>>>>>>>>>>');
debug('>>>>>>>>>>');
debugLogStart();

require('auth.php');

//ーーーーーーーーーーーーーーーーーー
// 画面表示用データ取得
//ーーーーーーーーーーーーーーーーーー
//GETデータを格納
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';

//DBから商品データを取得
$dbFormData = (!empty($t_id)) ? getTextbook($_SESSION['user_id'], $t_id) : '';

//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;

//DBからカテゴリデータを取得
$dbCategoryData = getCategory();

// DBから商品の状態データを取得
$dbStateData = getState();

debug('教科書ID:'.$t_id);
debug('フォーム用DBデータ:'.print_r($dbFormData,true));
debug('カテゴリデータ:'.print_r($dbCategoryData,true));
debug('商品の状態データ:'.print_r($dbStateData,true));

//ーーーーーーーーーーーーーーーーーー
// 画面表示用データ（サイドバー）
//ーーーーーーーーーーーーーーーーーー
//DBからサイドバーのユーザーデータを取得
$sideFormData = sideUser($_SESSION['user_id']);

//ーーーーーーーーーーーーーーーーーー
// パラメータ改ざんチェック
//ーーーーーーーーーーーーーーーーーー
// GETパラメータはあるが、改ざんされてる（URLをいじった）場合、
// 正しい商品データが取れないのでマイページへ遷移させる
if(!empty($t_id) && empty($dbFormData)){
  debug('GETパラメータの教科書IDが違います。マイページへ遷移します');
  header("Location:mypage.php");  //マイページへ
}

//ーーーーーーーーーーーーーーーーーー
// パラメータ改ざんチェック
//ーーーーーーーーーーーーーーーーーー
//POST送信時処理
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報:'.print_r($_POST,true));
  debug('FILE情報:'.print_r($_FILES,true));
  
  //変数にユーザー情報を代入
  $name = $_POST['name'];
  $author = $_POST['author'];
  $publisher = $_POST['publisher'];
  $category = $_POST['category_id'];
  
  $state = $_POST['state_id'];
  
  $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;
  $comment = $_POST['comment'];
  
  //画像をアップロードし、パスを格納
  $pic1 = ( !empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : '';
  //編集の場合、画像を登録してないが既にDBに登録されている場合、DBのパスを入れる
  $pic1 = ( empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  
  $pic2 = ( !empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'],'pic2') : '';
  $pic2 = ( empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  
  $pic3 = ( !empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'],'pic3') : '';
  $pic3 = ( empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

  
  //新規登録バリデーション
  if(empty($dbFormData)){
    //未入力（商品名）
    validRequired($name, 'name');
    //最大文字数（商品名）
    validMaxLen($name, 'name');
    
    //未入力（カテゴリ）
    validRequired($category, 'category_id');
    //セレクトボックス（カテゴリ）
    validSelect($category, 'category_id');
    
    //未入力（状態）
    validRequired($state, 'state_id');
    //セレクトボックス（状態）
    validSelect($state, 'state_id');
    
    //最大文字数（詳細）
    validMaxLen($comment, 'comment');
    
    //未入力（金額）
    validRequired($price, 'price');
    //半角数字（金額）
    validNumber($price, 'price');
  }else{
    
    //編集バリデーション
    if($dbFormData['name'] !== $name){
      //商品名
      validRequired($name, 'name');
      validMaxLen($name, 'name');
    }
    
    //著者名
    if((!empty($author)) && ($dbFormData['author'] !== $author)){
      validMaxLen($author, 'author');
    }
    
    //出版社
    if((!empty($publisher)) && ($dbFormData['publisher'] !== $publisher)){
      validMaxLen($publisher, 'publisher');
    }
    
    //カテゴリ
    if($dbFormData['category_id'] !== $category){
      validSelect($category, 'category_id');
    }
    
    //状態
    if($dbFormData['state_id'] !== $state){
      validRequired($state, 'state_id');      
      validSelect($state, 'state_id');
    }

    //詳細
    if($dbFormData['comment'] !== $comment){
      validMaxLen($comment, 'comment', 500);
    }

    //金額
    if($dbFormData['price'] != $price){
      validRequired($price, 'price');
      validNumber($price, 'price');
    }
  }
  
  
  
  if(empty($err_msg)){
    debug('バリデーションOKです');
    
    try{
      $dbh = dbConnect();
      //SQL文作成
      //編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を使用
      if($edit_flg){
        debug('DB更新です');
        $sql = 'UPDATE textbook SET name = :name, author = :author, publisher = :publisher, category_id = :category, state_id = :state, price = :price, comment= :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :t_id';
        $data = array(':name' => $name, ':author' => $author, ':publisher' => $publisher, ':category' => $category, ':state' => $state, ':price' => $price, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], 't_id' => $t_id);
      }else{
        debug('DB新規登録です');
        $sql = 'insert into textbook (name, author, publisher, category_id, state_id, price, comment, pic1, pic2, pic3, user_id, create_date) VALUES (:name, :author, :publisher, :category, :state, :price, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
        $data = array(':name' => $name, ':author' => $author, 'publisher' => $publisher, ':category' => $category, ':state' => $state, ':price' => $price, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL:'.$sql);
      debug('流し込みデータ:'.print_r($data,true));
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      
      //クエリ成功の場合
      if($stmt){
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }
      
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}

?>


<?php
$siteTitle = (!$edit_flg) ? '教科書出品登録' : '教科書情報の編集';
require('head.php');
?>
<body>
 
  <?php
  require('header.php');
  ?>
  
  <!-- メインコンテンツ -->
  <div id="contents" class="page-2colum page-logined">
    <h1 class="page-title"><?php echo (!$edit_flg) ? '教科書を出品する' : '教科書を編集する'; ?></h1>
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" class="form" style="width:100%;box-sizing:border-box;" enctype="multipart/form-data" method="post">
          
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
            教科書のタイトル<span class="label-require">必須</span>
            <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('name');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['author'])) echo 'err'; ?>">
            著者名
            <input type="text" name="author" value="<?php echo getFormData('author'); ?>" style="width:40%;">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('author');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['publisher'])) echo 'err'; ?>">
            出版社
            <input type="text" name="publisher" value="<?php echo getFormData('publisher'); ?>" style="width:40%;">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('publisher');
            ?>
          </div>
          
          <label for="" class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
            カテゴリ<span class="label-require">必須</span>
            <select name="category_id" id="" style="width:40%;">
              <option value="0" <?php if(getFormData('category_id') == 0){ echo 'selected'; } ?> >選択してください</option>
              <?php
                foreach($dbCategoryData as $key => $val){
              ?>
                <option value="<?php echo $val['id']; ?>" <?php if(getFormData('category_id') == $val['id'] ){ echo 'selected'; } ?>>
                  <?php echo $val['name']; ?>
                </option>
              <?php
                }
              ?>
            </select>
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('category_id');
            ?>
          </div><br>
          
          <label for="" class="<?php if(!empty($err_msg['state_id'])) echo 'err'; ?>">
            コンディション<span class="label-require">必須</span>
            <select name="state_id" id="" style="width:40%;">
              <option value="0" <?php if(getFormData('state_id') == 0){ echo 'selected'; } ?>>選択してください</option>
              <?php
                foreach($dbStateData as $key => $val){
              ?>
                <option value="<?php echo $val['id']; ?>"　<?php if(getFormData('state_id') == $val['id']){ echo 'selected'; } ?>>
                  <?php echo $val['name']; ?>
                </option>
              <?php
                }
              ?>
            </select>
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('state_id');
            ?>
          </div><br>
          
          <label for="" class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
            詳細
            <textarea id="js-count" name="comment" id="" cols="30" rows="10" style="height:150px;"><?php echo getFormData('comment'); ?></textarea>
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('comment');
            ?>
          </div>
          <p class="counter-text"><span id="js-count-view">0</span>/255文字</p>          
          
          <label for="" style="text-align:left;">
            金額<span class="label-require">必須</span>
            <div class="form-group">
              <input type="text" name="price" style="width:150px;" placeholder="3,000" value="<?php echo (!empty(getFormData('price'))) ? getFormData('price') : 0; ?>"><span class="option">円</span>
            </div>
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('price');
            ?>
          </div>
          
          <div style="overflow:hidden;">
            <div class="imgDrop-container">
              画像1
              <label for="" class="area-drop <?php if(!empty($err_msg['pic1'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic1" class="input-file">
                <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic1'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ                  
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pic1');
                ?>
              </div>
            </div>
            
            <div class="imgDrop-container">
              画像2
              <label for="" class="area-drop <?php if(!empty($err_msg['pic2'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic2" class="input-file">
                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic2'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pic2');
                ?>
              </div>
            </div>
            
            <div class="imgDrop-container">
              画像3             
              <label for="" class="area-drop <?php if(!empty($err_msg['pic3'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic3" class="input-file">
                <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic3'))) echo 'display:none;' ?>">
                  ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pic3');
                ?>
              </div>
            </div>
          </div>
          
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '出品する' : '更新する'; ?>">
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