<?php
session_start();
require_once '../library.php';

if (isset($_SESSION['form'])) {
  $form = $_SESSION['form'];
} else {
  header('location: register.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $db = dbconnect();
  $stmt = $db->prepare('INSERT INTO members (name, email, password, picture, created) VALUES (?,?,?,?,NOW())');
  if (!$stmt) {
    die($db->error);
  }

  $password = password_hash($form['password'], PASSWORD_DEFAULT);
  $stmt->bind_param('ssss', $form['name'], $form['email'], $password, $form['image']);
  $success = $stmt->execute();
  if (!$success) {
    die($db->error);
  }

  unset($_SESSION['form']);
  header('location: thanks.php');
  exit;
}

?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOINING! | 会員登録</title>
  <!-- GOOGLE FONTS -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Paytone+One&family=Train+One&family=Zen+Maru+Gothic:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ultra&display=swap" rel="stylesheet">
  <!-- css -->
  <link rel="stylesheet" href="../css/docter_reset.css">
  <link rel="stylesheet" href="../css/common.css">
  <link rel="stylesheet" href="../css/default.css">
</head>

<body>
  <div class="allcover">
    <div class="c-container">
      <h1 class="logo">JOINING!</h1>
      <div class="login-bg">
        <div class="sub-titbox txta-cent">
          <p class="sub-tit">確認画面</p>
        </div>
        <form action="" method="post" class="">
          <dl class="checklist">
            <dt>ニックネーム</dt>
            <dd><?= h($form['name']) ?></dd>
            <dt>メールアドレス</dt>
            <dd><?= h($form['email']) ?></dd>
            <dt>パスワード</dt>
            <dd>パスワードは表示されません</dd>
            <dt>アイコン用画像</dt>
            <dd>
              <img src="../profile_icon/<?= h($form['image']) ?>" alt="" class="check-image">
            </dd>
          </dl>
          <div class="c-btnbox-big txta-cent">
            <a href="register.php?action=rewrite" class="btn c-btn btn-red">書き直す</a>
            <input type="submit" value="登録する" class="btn c-btn">
          </div>
        </form>
      </div>
    </div>
  </div>

</body>

</html>