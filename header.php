<!-- メニュー -->
<header>
  <div class="header-bar">
    政治学部・学科の教科書シェアリングサービス
  </div>   
  <div class="site-width">
    <h1><a href="index.php"><img src="img/logeeegege.png" alt=""></a></h1>
    <nav id="top-nav">
      <ul>
        <li><a href="about.php">はじめての方へ</a></li>
        <?php
          if(empty($_SESSION['user_id'])){
        ?>
          <li><a href="login.php">ログイン</a></li>
          <li><a href="signup.php">ユーザー登録</a></li>
        <?php
          }else{
        ?>
          <li><a href="logout.php">ログアウト</a></li>
          <li><a href="mypage.php">マイページ</a></li>        
        <?php
          }
        ?>
        <li><a href="contact.php">お問い合わせ</a></li>
      </ul>
    </nav>
  </div>
</header>