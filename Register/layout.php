<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($title) ?> - <?= $e($orgName) ?></title>
  <link rel="icon" href="../images/logo.jpg">
  <link rel="stylesheet" href="../css/site.css">
</head>
<body>

<header class="site-header">
  <div class="wrap">
    <a class="brand" href="../index.php">
      <img src="../images/logo.jpg" alt="UMBO STR logo">
      <span><?= $e($orgName) ?></span>
    </a>
    <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="main-nav">Menu</button>
    <nav class="nav" id="main-nav" data-nav aria-label="Main">
      <a href="../index.php">Home</a>
      <a href="../index.php?page=activities">Activities</a>
      <a href="../index.php#contact">Contact</a>
    </nav>
  </div>
</header>

<div class="page-title">
  <div class="wrap"><h1><?= $e($heading) ?></h1></div>
</div>

<main>
  <div class="wrap">
    <?= $content ?>
  </div>
</main>

<footer class="site-footer">
  <div class="wrap">&copy; <?= date('Y') ?> <?= $e($orgName) ?></div>
</footer>

<script src="java.js"></script>
</body>
</html>
