<?php

require('function.php');

debug('>>>>>>>>>>');
debug('ログアウトページ');
debug('>>>>>>>>>>');

debug('ログアウトします');
//セッションを削除
session_destroy();
debug('ログインページへ遷移します');
//ログインページへ
header("Location:login.php");
