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


// 投稿
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
  $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
  if ($title !== '' && $message !== '') {
    $stmt = $db->prepare('INSERT INTO posts (title, message, member_id, created) VALUES (?,?,?,NOW())');
    if (!$stmt) {
      die($db->error);
    }
    $stmt->bind_param('ssi', $title, $message, $id);
    $success = $stmt->execute();
    if (!$success) {
      die($db->error);
    }
    $stmt->close();

    header('location: index.php');
    exit;
  }
}


// ページャー
if (isset($_REQUEST['page']) && is_numeric($_REQUEST['page'])) {
  $page = $_REQUEST['page'];
} else {
  $page = 1;
}

//マイナス値を無効
$page = max($page, 1);
$perPage = 30; // １ページあたりのデータ件数

$res = mysqli_query($db, 'SELECT * FROM posts');
$cnt = mysqli_num_rows($res);
// $counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
// $cnt = $counts->fetch_assoc();
$max_page = ceil($cnt / $perPage);
$res->close();

//最大値を最大ページで表示
$page = min($page, $max_page);
$start = $perPage * ($page - 1);

//ページの表示範囲
if ($page == 1 || $page == $max_page) {
  $range = 4;
} elseif ($page == 2 || $page == $max_page - 1) {
  $range = 3;
} else {
  $range = 2;
}


// メッセージの表示
$stmt = $db->prepare('SELECT p.id, p.title, p.message, p.member_id, p.created, m.name, m.picture FROM posts p, members m WHERE m.id=p.member_id ORDER BY id DESC LIMIT ?,?');
if (!$stmt) {
  die($db->error);
}
$stmt->bind_param('ii', $start, $perPage);
$success = $stmt->execute();
if (!$success) {
  die($db->error);
}
$stmt->bind_result($id, $title, $message, $member, $created, $name, $picture,);


?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOINING!</title>
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
          <p><?= h($name) ?>さん、メッセージをどうぞ</p>
          <form action="" method="post" class="">
            <input type="text" name="title" class="inputarea input-m2" value="" placeholder="タイトル" required>
            <textarea name="message" class="postarea" placeholder="メッセージ" required></textarea>
            <div class="c-btnbox txta-cent">
              <input type="submit" name="submit" class="c-btn" value="投稿する">
            </div>
          </form>
          <!-- /投稿 -->
          <hr>
          <?php while ($stmt->fetch()) : ?>
            <section class="post-bg">
              <div class="post-imgbox">
                <img src="profile_icon/<?= h($picture) ?>" class="post-img" alt="" onerror="this.src='img/icon_profile.jpg'">
              </div>
              <div class="post-txtarea">
                <p class="post-name"><?= h($name) ?></p>
                <h2 class="post-tit"><?= h($title) ?></h2>
                <a href="view.php?id=<?= h($id) ?>" class="post-txt">
                  <?php if (mb_strlen(h($message)) <= 50) : ?>
                    <article><?= mb_substr(h($message), 0, 50) ?></article>
                  <?php else : ?>
                    <article><?= mb_substr(h($message), 0, 50) . '...' ?></article>
                  <?php endif; ?>
                </a>
                <p class="post-datearea">
                  <a href="view.php?id=<?= h($id) ?>" class=""><time><?= h($created) ?></time></a>
                  <?php if ($_SESSION['id'] === $member) : ?>
                    <a href="delete.php?id=<?= h($id) ?>" class="error" onclick="return confirm('本当に削除してよろしいですか？')">削除</a>
                  <?php endif; ?>
                </p>
              </div>
            </section>
          <?php endwhile; ?>
          <!-- ページャー -->
          <!-- 前へ -->
          <div class="pager-box">
            <?php if ($page >= 2) : ?>
              <a href="index.php?page=<?= $page - 1 ?>" class="pager">◀︎ PREV </a>
            <?php endif; ?>
            <!-- 数字 -->
            <?php for ($i = 1; $i <= $max_page; $i++) : ?>
              <?php if ($i >= $page - $range && $i <= $page + $range) : ?>
                <?php if ($i == $page) : ?>
                  <a href="index.php?page=<?php echo $i; ?>" disable=”disabled” tabindex="-1" class="now-page"><?php echo $i; ?></a>
                <?php else : ?>
                  <a href="index.php?page=<?php echo $i; ?>" class="pager"><?php echo $i; ?></a>
                <?php endif; ?>
              <?php endif; ?>
            <?php endfor; ?>
            <!-- 次へ -->
            <?php if ($page < $max_page) : ?>
              <a href="index.php?page=<?= $page + 1 ?>" class="pager"> NEXT ▶︎</a>
            <?php endif; ?>
          </div>
          <!-- /ページャー -->
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