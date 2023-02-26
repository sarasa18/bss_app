<?php
session_start();
require_once 'library.php';

if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
  $sesid = $_SESSION['id'];
  $sesname = $_SESSION['name'];
} else {
  header('location: login.php');
  exit;
}

$db = dbconnect();


$form = [
  'name' => '',
  'email' => '',
  'password' => '',
];

$error = [];

$first = $db->prepare('SELECT id, name, email, password, picture FROM members WHERE id=?');
if (!$first) {
  die($db->error);
}
$first->bind_param('i', $sesid);
$success = $first->execute();
if (!$success) {
  die($db->error);
}

$first->bind_result($id, $name, $email, $password, $picture);
$first->fetch();

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
  <link rel="stylesheet" href="css/docter_reset.css">
  <link rel="stylesheet" href="css/common.css">
  <link rel="stylesheet" href="css/default.css">
</head>

<body>
  <div class="allcover">
    <div class="c-container">
      <h1 class="logo">JOINING!</h1>
      <div class="login-bg">
        <div class="sub-titbox txta-cent">
          <p class="sub-tit">マイページ</p>
        </div>
        <form action="" method="post" class="">
          <dl class="checklist">
            <dt>ニックネーム</dt>
            <dd><?= h($name) ?></dd>
            <dt>メールアドレス</dt>
            <dd><?= h($email) ?></dd>
            <dt>パスワード</dt>
            <dd>パスワードは表示されません</dd>
            <dt>アイコン用画像</dt>
            <dd>
              <img src="profile_icon/<?= h($picture) ?>" alt="" class="check-image">
            </dd>
          </dl>
          <div class="c-btnbox-big txta-cent">
            <a href="index.php" class="btn c-btn">一覧に戻る</a>
            <a href="update.php" class="btn c-btn btn-red">変更する</a>
          </div>
        </form>
      </div>
    </div>
  </div>


</body>

</html>