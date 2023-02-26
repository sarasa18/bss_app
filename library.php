<?php
// htmlspecialchars()
function h($value)
{
  return htmlspecialchars($value, ENT_QUOTES);
}

// DB接続
function dbconnect()
{
  $db = new mysqli('localhost', 'root', 'root', 'bbs_app');
  if (!$db) {
    die($db->error);
  }

  return $db;
}
