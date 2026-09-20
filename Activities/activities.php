<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Activities - <?= $e($orgName) ?></title>
  <link rel="icon" href="../images/logo.jpg">
  <style>
    :root { --brown-900:#2b1608; --brown-700:#4a2810; --gold:#e2a520; --gold-deep:#b9791a; --teal:#25606b; --teal-tint:#eaf2f3; --ink:#241a12; --paper:#fdfcfa; --line:#dccfc0; --white:#fff; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif; color: var(--ink); background: var(--paper); line-height: 1.65; }
    a { color: inherit; }
    :focus-visible { outline: 3px solid var(--gold-deep); outline-offset: 3px; }
    .wrap { width: min(1080px, 92%); margin: 0 auto; }
    .site-header { background: var(--white); border-bottom: 3px solid var(--gold); }
    .site-header .wrap { display: flex; align-items: center; justify-content: space-between; min-height: 68px; }
    .brand { display: flex; align-items: center; gap: .75rem; font-weight: 800; color: var(--brown-700); text-decoration: none; }
    .brand img { width: 46px; height: 46px; border-radius: 50%; }
    .nav { display: flex; gap: 1.5rem; }
    .nav a { color: var(--brown-700); text-decoration: none; font-weight: 600; border-bottom: 3px solid transparent; }
    .nav a:hover { border-color: var(--gold); }
    .nav-toggle { display: none; background: none; border: 2px solid var(--brown-700); color: var(--brown-700); padding: .35rem .8rem; border-radius: 4px; font: inherit; font-weight: 600; cursor: pointer; }
    .page-title { background: var(--brown-900); color: var(--white); padding: 2.5rem 0; border-bottom: 8px solid var(--gold); }
    .page-title h1 { font-size: clamp(2rem, 6vw, 3rem); }
    main { padding: 2.5rem 0 4rem; }
    .activity-list { list-style: none; }
    .activity-list li { display: grid; grid-template-columns: 9rem 1fr; gap: 1rem; padding: 1.25rem 0; border-top: 1px solid var(--line); }
    .date { font-weight: 700; color: var(--gold-deep); }
    .activity-list h2 { font-size: 1.25rem; color: var(--brown-700); }
    .detail { background: var(--white); border-left: 6px solid var(--teal); padding: 1.5rem; margin-bottom: 2rem; }
    .detail h2 { color: var(--brown-700); }
    .back { display: inline-block; margin-top: 1rem; font-weight: 700; color: var(--teal); }
    .site-footer { background: var(--brown-900); color: #d8c6ab; padding: 1.5rem 0; font-size: .9rem; border-top: 4px solid var(--gold); }
    @media (max-width: 720px) {
      .nav-toggle { display: block; }
      .nav { display: none; position: absolute; top: 68px; left: 0; right: 0; background: var(--white); border-bottom: 3px solid var(--gold); flex-direction: column; padding: 1rem 4%; }
      .nav.is-open { display: flex; }
      .activity-list li { grid-template-columns: 1fr; gap: .25rem; }
    }
  </style>
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
      <a href="index.php">Activities</a>
      <a href="../index.php#contact">Contact</a>
    </nav>
  </div>
</header>

<div class="page-title">
  <div class="wrap"><h1>Activities</h1></div>
</div>

<main>
  <div class="wrap">
    <?php if ($selected !== null): ?>
      <article class="detail">
        <p class="date"><?= $e($selected['date']) ?></p>
        <h2><?= $e($selected['title']) ?></h2>
        <p><?= $e($selected['description']) ?></p>
        <a class="back" href="index.php">Back to all activities</a>
      </article>
    <?php endif; ?>

    <ul class="activity-list">
      <?php foreach ($activities as $id => $activity): ?>
        <li>
          <span class="date"><?= $e($activity['date']) ?></span>
          <div>
            <h2><a href="index.php?action=view&amp;id=<?= (int) $id ?>"><?= $e($activity['title']) ?></a></h2>
            <p><?= $e($activity['description']) ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</main>

<footer class="site-footer">
  <div class="wrap">&copy; <?= date('Y') ?> <?= $e($orgName) ?></div>
</footer>

<script src="java.js"></script>
</body>
</html>
