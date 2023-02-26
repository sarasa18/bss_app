<?php
session_start();
require_once 'library.php';

if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
  $id = $_SESSION['id'];
  $name = $_SESSION['name'];
} else {
  header('location: login.php');
  exit;
}

$db = dbconnect();

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
  header('location: index.php');
  exit;
}

// メッセージの表示
$stmt = $db->prepare('SELECT p.id, p.title, p.member_id, p.message, p.created, m.name, m.picture FROM posts p, members m WHERE p.id=? AND m.id=p.member_id');
if (!$stmt) {
  die($db->error);
}
$stmt->bind_param('i', $id);
$success = $stmt->execute();
if (!$stmt) {
  die($db->error);
}

$stmt->bind_result($id, $title, $member, $message, $created, $name, $picture);


?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOINING! | 詳細</title>
  <!-- GOOGLE FONTS -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Paytone+One&family=Train+One&family=Zen+Maru+Gothic:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Ultra&display=swap" rel="stylesheet">
  <!-- css -->
  <link rel="stylesheet" href="css/docter_reset.css">
  <link rel="stylesheet" href="css/common.css">
  <link rel="stylesheet" href="css/default.css">
  <!-- js -->
  <script src="js/ofi.min.js"></script>
</head>

<body>
  <div class="allcover">
    <div class="c-container">
      <main class="shadow">
        <header>
          <h1 class="h-logo">JOINING!</h1>
          <div class="h-btn-flex">
            <?php if ($_SESSION['id'] !== 6) : ?>
              <a href="mypage.php" class="h-btn h-btn-my" title="マイページ"><img src="img/icon_mypage.svg" alt="マイページ"></a>
            <?php endif; ?>
            <a href="logout.php" class="h-btn" title="ログアウト"><img src="img/icon_logout.svg" alt="ログアウト" onclick="return confirm('ログアウトしますか？')"></a>
          </div>
        </header>
        <!-- /header -->
        <div class="c-bg">
          <div class="sub-titbox txta-cent">
            <p class="sub-tit">メッセージ詳細</p>
          </div>
          <?php if ($stmt->fetch()) : ?>
            <section class="post-bg">
              <div class="post-imgbox">
                <img src="profile_icon/<?= h($picture) ?>" class="post-img" alt="" onerror="this.src='img/icon_profile.jpg'">
              </div>
              <div class="post-txtarea">
                <p class="post-name"><?= h($name) ?></p>
                <h2 class="post-tit"><?= h($title) ?></h2>
                <article class="post-txt"><?= h($message) ?></article>
                <p class="post-datearea">
                  <time><?= h($created) ?></time>
                  <?php if ($_SESSION['id'] === $member) : ?>
                    <a href="delete.php?id=<?= h($id) ?>" class="error" onclick="return confirm('本当に削除してよろしいですか？')">削除</a>
                  <?php endif; ?>
                </p>
              </div>
            </section>
            <div class="c-btnbox txta-cent">
              <a href="index.php" class="c-btn">一覧に戻る</a>
            </div>
          <?php else : ?>
            <p>その投稿は削除されたか、URLが間違えています</p>
          <?php endif; ?>
        </div><!-- /c-bg -->
      </main>
      <!-- /main -->
      <footer>
        <small>Presented by SARASA</small>
      </footer>
    </div><!-- /c-container -->
  </div>

  <!-- js -->
  <script>
    objectFitImages('img.post-img');
  </script>
</body>

</html>