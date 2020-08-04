  <!-- footer -->
  <footer id="footer">
    <div>Copyright <a href="">せいじぼん!!</a>. All Rights Reserved.</div>
  </footer>
  
  <script src="js/jquery-3.4.1.min.js"></script>
    <script>
      $(function(){
        //フッターを最下部に固定
        var $ftr = $('#footer');
        if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
          $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
        }
        
        //メッセージ表示
        var $jsShowMsg = $('#js-show-msg');
        var msg = $jsShowMsg.text();
        if(msg.replace(/^[\s ]+|[\s ]+$/g, "").length){
          $jsShowMsg.slideToggle('slow');
          setTimeout(function(){
            $jsShowMsg.slideToggle('slow')
          },5000);
        }
        
        //テキストエリアカウント
        var $countUp = $('#js-count'),
            $countView = $('#js-count-view');
        $countUp.on('keyup', function(e){
          $countView.html($(this).val().length);
        });        
        
        //画像ライブプレビュー
        var $dropArea = $('.area-drop');
        var $fileInput = $('.input-file');
        $dropArea.on('dragover', function(e){
          e.stopPropagation();
          e.preventDefault();
          $(this).css('border', '3px #ccc dashed');
        });
        $dropArea.on('dragleave', function(e){
          e.stopPropagation();
          e.preventDefault();
          $(this).css('border', 'none');
        });
        $fileInput.on('change', function(e){
          $dropArea.css('border', 'none');
          var file = this.files[0],
              $img = $(this).siblings('.prev-img'),
              fileReader = new FileReader();
          //読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
          fileReader.onload = function(event){
            //読み込んだデータをimgに設定
            $img.attr('src', event.target.result).show();
          };
          //画像読み込み
          fileReader.readAsDataURL(file);
        });
        
        //画像切り替え
        var $switchImgSubs = $('.js-switch-img-sub'),
            $switchImgMain = $('#js-switch-img-main');
        $switchImgSubs.on('click',function(e){
          $switchImgMain.attr('src',$(this).attr('src'));
        });
        
        //連絡掲示板のスクロール
        $(function(){
          $('#js-scroll-bottom').animate({
            scrollTop: $('#js-scroll-bottom')[0].scrollHeight
          }, 'fast');
        });
        
        //お気に入り登録・削除
        var $faco,
            favoTextId;
        $favo = $('.js-click-favo') || null;
        favoTextId = $favo.data('textid') || null;
        
        if(favoTextId !== undefined && favoTextId !== null){
          $favo.on('click',function(){
            var $this = $(this);
            $.ajax({
              type: "POST",
              url: "ajaxFavo.php",
              data: { textId: favoTextId }
            }).done(function(data){
              console.log('Ajax Success');
              $this.toggleClass('active');
            }).fail(function(msg){
              console.log('Ajax Error');
            });
          });
        }
        
        //アカウント削除ページの非活性化
        // 1. チェックボックスが入力された場合のイベントをセットする
        $('#check').click(function(){
          // 2. チェックボックスが入力されているか確認
          if( $(this).prop('checked')){
            // 3. 入力されていればsubmitを活性にする（disabledを外す）
            $('.withdraw-submit').prop('disabled', false);
          }else{
            // 4. 中身が入っていなければ非活性に戻す
            $('.withdraw-submit').prop('disabled', true);
          }
        });
        
        //トップページの上に戻るボタン
        var appear = false;
        var pagetop = $('#page_top');
        $(window).scroll(function () {
          if ($(this).scrollTop() > 100) {  //100pxスクロールしたら
            if (appear == false) {
              appear = true;
              pagetop.stop().animate({
                'bottom': '50px' //下から50pxの位置に
              }, 300); //0.3秒かけて現れる
            }
          } else {
            if (appear) {
              appear = false;
              pagetop.stop().animate({
                'bottom': '-50px' //下から-50pxの位置に
              }, 300); //0.3秒かけて隠れる
            }
          }
        });
        pagetop.click(function () {
          $('body, html').animate({ scrollTop: 0 }, 500); //0.5秒かけてトップへ戻る
          return false;
        });
        
        
      });
    </script>  
</body>
</html>