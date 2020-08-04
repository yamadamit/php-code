<?php
$siteTitle = "このサービスについて";
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
      <img src="img/politics-top.png" alt="">
      <div class="about-container">
        <h1 class="about-title">はじめての方へ</h1>
        <p>
          「せいじぼん!!」では、使い終わった教科書を売ったり、<br>
          誰かが使い終わった教科書を安く手に入れることが出来るサイトです。<br><br>

          政治学部・学科で使用する教科書を中心に扱っています。          
        </p><br>
        <h1 class="about-title">サービスのメリット</h1>
        <p>
          新学期、教科書を買うお金がない、<br>
          譲ってもらえる先輩がいない…<br>
          そんな経験はありませんか？<br><br>

          このサービスでは、<br>
          ・新品で買うには高すぎる教科書を格安で購入できる<br>
          ・使い終わった教科書をお金に変えられる<br><br>
          
          そんな環境が揃っています。
        </p><br>
        <h1 class="about-title">利用するのにお金はかかるの？</h1>
        <p>
          「せいじぼん!!」の登録、利用にあたって料金はかかりません。<br>
          現在先行リリース中であるため、<br>
          取引手数料も無料となっています。<br><br>
          
          ご登録は、以下のボタンからどうぞ。
        </p>
        
        <hr>
        <div class="btn-container" style="text-align:center">
          <input type="submit" class="cv-button" value="無料で利用する" style="width:250px;" onClick="location.href='http://localhost:8888/sample/signup.php'">
        </div>
        
      </div>
    </section>
  </div><br>
  
  <!-- footer -->
  <?php
  require('footer.php');
  ?>