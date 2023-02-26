<?php
session_start();
require_once 'library.php';
$db = dbconnect();

if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
  $sesid = $_SESSION['id'];
  $sesname = $_SESSION['name'];

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

  $_SESSION['image'] = $picture;
  $sesimage = $_SESSION['image'];
  $first->close();
} else {
  header('location: login.php');
  exit;
}

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 名前変更
  $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
  if (isset($_POST['submit_name'])) {
    if ($name === '') {
      $error['name'] = 'blank';
    }

    if (empty($error)) {
      $stmt = $db->prepare('UPDATE members SET name=? WHERE id=?');
      if (!$stmt) {
        die($db->error);
      }
      $stmt->bind_param('si', $name, $sesid);
      $success = $stmt->execute();
      if (!$success) {
        die($db->error);
      }
      $stmt->close();

      $_SESSION['name'] = $name;
      header('location: mypage.php');
      exit;
    }
  }


  // email変更
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_STRING);
  if (isset($_POST['submit_email'])) {
    if ($email === '') {
      $error['email'] = 'blank';
    } else {
      // 重複登録
      $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM members WHERE email=?');
      if (!$stmt) {
        die($db->error);
      }
      $stmt->bind_param('s', $email);
      $success = $stmt->execute();
      if (!$success) {
        die($db->error);
      }
      $stmt->bind_result($cnt);
      $stmt->fetch();
      if ($cnt > 0) {
        $error['email'] = 'duplicate';
      }
      $stmt->close();
    }
    if (empty($error)) {
      $stmt = $db->prepare('UPDATE members SET email=? WHERE id=?');
      if (!$stmt) {
        die($db->error);
      }
      $stmt->bind_param('si', $email, $sesid);
      $success = $stmt->execute();
      if (!$success) {
        die($db->error);
      }
      $stmt->close();
      header('location: mypage.php');
      exit;
    }
  }

  // パスワード変更
  $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
  if (isset($_POST['submit_password'])) {
    if ($password === '') {
      $error['password'] = 'blank';
    } elseif (mb_strlen($password) < 4) {
      $error['password'] = 'length';
    }

    if (empty($error)) {
      $stmt = $db->prepare('UPDATE members SET password=? WHERE id=?');
      if (!$stmt) {
        die($db->error);
      }
      $password = password_hash($password, PASSWORD_DEFAULT);
      $stmt->bind_param('si', $password, $sesid);
      $success = $stmt->execute();
      if (!$success) {
        die($db->error);
      }
      $stmt->close();

      header('location: mypage.php');
      exit;
    }
  }

  // 画像変更
  $image = $_FILES['image'];
  if (isset($_POST['submit_image'])) {
    if ($image['name'] !== '' && $image['error'] === 0) {
      $type = mime_content_type($image['tmp_name']);
      if ($type !== 'image/png' && $type !== 'image/jpeg') {
        $error['image'] = 'type';
      }
    }

    if (empty($error)) {
      // 過去画像消去
      if (isset($sesimage) && file_exists('profile_icon/' . $sesimage)) {
        unlink('profile_icon/' . $sesimage);
      }

      if ($image['name'] !== '') {
        $filename = date('YmdHis') . '_' . $image['name'];
        if (!move_uploaded_file($image['tmp_name'], 'profile_icon/' . $filename)) {
          die('アップロードできませんでした');
        }
        $stmt = $db->prepare('UPDATE members SET picture=? WHERE id=?');
        if (!$stmt) {
          die($db->error);
        }
        $stmt->bind_param('si', $filename, $sesid);
        $success = $stmt->execute();
        if (!$success) {
          die($db->error);
        }
        $stmt->close();
      } else {
        $_SESSION['image'] = '';
      }

      unset($_SESSION['image']);

      header('location: mypage.php');
      exit;
    }
  }
}



// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//   $form['name'] = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
//   if ($form['name'] === '') {
//     $error['name'] = 'blank';
//   }

//   $form['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
//   if ($form['email'] === '') {
//     $error['email'] = 'blank';
//   }
//   // 重複登録
//   $db = dbconnect();
//   $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM members WHERE email=?');
//   if (!$stmt) {
//     die($db->error);
//   }
//   $stmt->bind_param('s', $form['email']);
//   $success = $stmt->execute();
//   if (!$success) {
//     die($db->error);
//   }
//   $stmt->bind_result($cnt);
//   $stmt->fetch();
//   if ($cnt > 0) {
//     $error['email'] = 'duplicate';
//   }


//   $form['password'] = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
//   if ($form['password'] === '') {
//     $error['password'] = 'blank';
//   } elseif (mb_strlen($form['password']) < 4) {
//     $error['password'] = 'length';
//   }

//   $image = $_FILES['image'];
//   if ($image['name'] !== '' && $image['error'] === 0) {
//     $type = mime_content_type($image['tmp_name']);
//     if ($type !== 'image/png' && $type !== 'image/jpeg') {
//       $error['image'] = 'type';
//     }
//   }



//   // 書き直し用(画像アップロード)
//   if (isset($_GET['action']) && $_GET['action'] === 'rewrite' && isset($_SESSION['form'])) {
//     if (isset($_SESSION['form']['image']) && file_exists('../profile_icon/' . $_SESSION['form']['image'])) {
//       unlink('../profile_icon/' . $_SESSION['form']['image']);
//     }
//   }

//   // エラーがない場合
//   if (empty($error)) {
//     $_SESSION['form'] = $form;
//     if ($image['name'] !== '') {
//       $filename = date('YmdHis') . '_' . $image['name'];
//       if (!move_uploaded_file($image['tmp_name'], '../profile_icon/' . $filename)) {
//         die('アップロードできませんでした');
//       }

//       $_SESSION['form']['image'] = $filename;
//     } else {
//       $_SESSION['form']['image'] = '';
//     }

//     header('location: check.php');
//     exit;
//   }
// }

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
          <p class="sub-tit">登録編集</p>
        </div>
        <form action="" method="post" class="" enctype="multipart/form-data">
          <label for="name" class="">ニックネーム</label>
          <input type="name" id="name" name="name" class="inputarea" value="<?= h($name); ?>" required>
          <?php if (isset($error['name']) && $error['name'] === 'blank') : ?>
            <p class="error">！ニックネームを入力してください</p>
          <?php endif; ?>
          <div class="login-btnbox txta-cent mgb-1em">
            <input type="submit" name="submit_name" class="login-btn" value="ニックネームを変更">
          </div>

          <label for="email" class="regist-label">メールアドレス</label>
          <input type="email" id="email" name="email" class="inputarea" value="<?= h($email) ?>" required>
          <?php if (isset($error['email']) && $error['email'] === 'blank') : ?>
            <p class="error">！メールアドレスを入力してください</p>
          <?php endif; ?>
          <?php if (isset($error['email']) && $error['email'] === 'duplicate') : ?>
            <p class="error">！指定されたメールアドレスはすでに登録されています</p>
          <?php endif; ?>
          <div class="login-btnbox txta-cent mgb-1em">
            <input type="submit" name="submit_email" class="login-btn" value="メールアドレスを変更">
          </div>

          <label for="password" class="regist-label">パスワード(4文字以上)</label>
          <input type="password" id="password" name="password" class="inputarea" value="" placeholder="パスワードは非表示です">
          <?php if (isset($error['password']) && $error['password'] === 'blank') : ?>
            <p class="error">！パスワードを入力してください</p>
          <?php endif; ?>
          <?php if (isset($error['password']) && $error['password'] === 'length') : ?>
            <p class="error">！パスワードは4文字以上で入力してください</p>
          <?php endif; ?>
          <div class="login-btnbox txta-cent mgb-1em">
            <input type="submit" name="submit_password" class="login-btn" value="パスワードを変更">
          </div>

          <label for="image" class="regist-label">アイコン用画像(.pngもしくは.jpg)</label>
          <input type="file" name="image" class="inputarea inputfile" value="">
          <?php if (isset($error['image']) && $error['image'] === 'type') : ?>
            <p class="error">！ 画像は「.png」または「.jpg」の画像を指定してください</p>
          <?php endif; ?>
          <div class="login-btnbox txta-cent mgb-1em">
            <input type="submit" name="submit_image" class="login-btn" value="画像を変更">
          </div>
        </form>
        <div class="login-btnbox txta-cent">
          <a href="mypage.php" class="login-btn btn-red">マイページに戻る</a>
        </div>
      </div>
    </div>
  </div>


</body>

</html>