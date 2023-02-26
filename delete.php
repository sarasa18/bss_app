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

$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$post_id) {
  header('location: index.php');
  exit;
}

$stmt = $db->prepare('DELETE FROM posts WHERE id=? AND member_id=? LIMIT 1');
if (!$stmt) {
  die($db->error);
}
$stmt->bind_param('ii', $post_id, $id);
$success = $stmt->execute();
if (!$success) {
  die($db->error);
}

header('Location: index.php');
exit();
