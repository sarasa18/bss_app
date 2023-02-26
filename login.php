<?php
session_start();
require_once 'library.php';

$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);

  if ($email === '' || $password === '') {
    $error['login'] = 'blank';
  } else {
    $db = dbconnect();
    $stmt = $db->prepare('SELECT id, name, password FROM members WHERE email=? LIMIT 1');
    if (!$stmt) {
      die($db->error);
    }
    $stmt->bind_param('s', $email);
    $success = $stmt->execute();
    if (!$success) {
      die($db->error);
    }

    $stmt->bind_result($id, $name, $hash);
    $stmt->fetch();

    if (password_verify($password, $hash)) {
      // ログイン成功
      session_regenerate_id();
      $_SESSION['id'] = $id;
      $_SESSION['name'] = $name;

      header('location: index.php');
      exit;
    } else {
      $error['login'] = 'failed';
    }
  }
}


?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOINING! | ログイン</title>
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
          <p class="sub-tit">ログイン</p>
        </div>
        <form action="" method="post" class="">
          <input type="email" name="email" class="inputarea input-m1" value="<?= h($email) ?>" placeholder="メールアドレス" required>
          <?php if (isset($error['login']) && $error['login'] === 'blank') : ?>
            <p class="error">！メールアドレスとパスワードを入力してください</p>
          <?php endif; ?>
          <input type="password" name="password" class="inputarea input-m1" value="<?= h($password) ?>" placeholder="パスワード" required>
          <?php if (isset($error['login']) && $error['login'] === 'failed') : ?>
            <p class="error">！ログインに失敗しました。正しくご記入ください。</p>
          <?php endif; ?>
          <div class="login-btnbox txta-cent">
            <input type="submit" name="submit" class="login-btn" value="ログイン">
          </div>
        </form>
        <div class="register-linkbox">
          <a href="register/register.php" class="">>> 会員登録はこちら</a>
        </div>
      </div>
    </div>
  </div>

</body>

</html>