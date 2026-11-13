<?php
$pageTitle = $pageTitle ?? 'Task Tracker';
$currentUserName = $_SESSION['user_name'] ?? 'Workspace';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f5f6f3">
  <title><?= escape($pageTitle) ?> · RTIH Task Tracker</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css?v=20261113-2">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php' ?>" aria-label="RTIH Task Tracker home">
    <span class="brand-mark">R</span><span class="brand-copy"><strong>RTIH</strong><small>TASK TRACKER</small></span>
  </a>
  <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="topnav" aria-label="Main navigation"><a class="nav-active" href="dashboard.php">My tasks</a></nav>
    <div class="top-user"><span class="avatar"><?= escape(strtoupper(substr($currentUserName, 0, 1))) ?></span><span class="user-name"><?= escape($currentUserName) ?></span><a class="account-link" href="account.php" aria-label="Account settings" title="Account settings">⚙ <span>Account</span></a>
      <form action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><button class="logout" type="submit">Log out</button></form>
    </div>
  <?php endif; ?>
</header>
<main class="page-shell">
