<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $e($org['fullName']) ?></title>
  <link rel="icon" href="images/logo.jpg">
  <style>
    /* Colors taken from the UMBO logo */
    :root {
      --brown-900: #2b1608;   /* beads around the seal */
      --brown-700: #4a2810;
      --gold: #e2a520;        /* laurel wreath and sun */
      --gold-deep: #b9791a;
      --teal: #25606b;        /* Kalinga map */
      --teal-tint: #eaf2f3;
      --ink: #241a12;
      --paper: #fdfcfa;
      --line: #dccfc0;
      --white: #ffffff;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif;
      color: var(--ink);
      background: var(--paper);
      line-height: 1.65;
    }
    a { color: inherit; }
    :focus-visible { outline: 3px solid var(--gold-deep); outline-offset: 3px; }
    .wrap { width: min(1080px, 92%); margin: 0 auto; }

    /* Header */
    .site-header { background: var(--white); border-bottom: 3px solid var(--gold); position: sticky; top: 0; z-index: 10; }
    .site-header .wrap { display: flex; align-items: center; justify-content: space-between; min-height: 68px; }
    .brand { display: flex; align-items: center; gap: .75rem; font-weight: 800; color: var(--brown-700); text-decoration: none; }
    .brand img { width: 46px; height: 46px; border-radius: 50%; }
    .nav { display: flex; gap: 1.5rem; }
    .nav a { color: var(--brown-700); text-decoration: none; font-weight: 600; padding: .25rem 0; border-bottom: 3px solid transparent; }
    .nav a:hover { border-color: var(--gold); }
    .nav a.nav-cta { background: var(--gold); color: var(--brown-900); padding: .35rem 1rem; border-radius: 4px; border-bottom: 0; }
    .nav a.nav-cta:hover { background: var(--gold-deep); color: var(--white); }
    .nav a.admin-link { border: 2px solid var(--brown-700); padding: .2rem .8rem; border-radius: 4px; }
    .nav a.admin-link:hover { background: var(--brown-700); color: var(--white); }
    .nav-toggle { display: none; background: none; border: 2px solid var(--brown-700); color: var(--brown-700); padding: .35rem .8rem; border-radius: 4px; font: inherit; font-weight: 600; cursor: pointer; }

    /* Hero */
    .hero { background: var(--brown-900); color: var(--white); padding: clamp(2.5rem, 7vw, 5.5rem) 0; border-bottom: 8px solid var(--gold); }
    .hero .wrap { display: grid; grid-template-columns: 1.3fr 1fr; gap: 3rem; align-items: center; }
    .hero h1 { font-size: clamp(2rem, 5.5vw, 3.6rem); line-height: 1.08; font-weight: 800; }
    .hero .place { margin-top: 1.25rem; font-size: 1.2rem; color: #ead9bf; }
    .hero .logo-seal { width: min(340px, 80%); aspect-ratio: 1; justify-self: center; border-radius: 50%; background: var(--white); border: 6px solid var(--gold); box-shadow: 0 0 0 6px var(--brown-700); overflow: hidden; }
    .hero .logo-seal img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .actions { margin-top: 2rem; display: flex; gap: .75rem; flex-wrap: wrap; }
    .btn { display: inline-block; padding: .75rem 1.4rem; border-radius: 4px; font-weight: 700; text-decoration: none; border: 2px solid var(--gold); }
    .btn-primary { background: var(--gold); color: var(--brown-900); }
    .btn-ghost { color: var(--white); }
    .btn:hover { background: var(--gold-deep); border-color: var(--gold-deep); color: var(--white); }

    /* Sections */
    section { padding: clamp(2.5rem, 6vw, 4.5rem) 0; }
    h2 { font-size: clamp(1.6rem, 4vw, 2.2rem); color: var(--brown-700); margin-bottom: 1rem; }
    .lead { max-width: 65ch; font-size: 1.1rem; }
    .two-col { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; }
    .panel { background: var(--white); border-left: 6px solid var(--teal); padding: 1.5rem; }
    .panel h3 { color: var(--teal); margin-bottom: .5rem; }
    .band { background: var(--teal-tint); }

    .objectives { list-style: none; display: grid; gap: .75rem; max-width: 65ch; }
    .objectives li { padding-left: 1.5rem; position: relative; }
    .objectives li::before { content: ""; position: absolute; left: 0; top: .7em; width: .7rem; height: .7rem; background: var(--gold); }

    .group-title { margin: 1.5rem 0 1rem; font-size: 1.25rem; color: var(--teal); }
    .people { list-style: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
    .person { background: var(--white); border: 1px solid var(--line); border-top: 5px solid var(--gold); padding: 1.25rem 1rem; }
    .avatar { width: 56px; height: 56px; border-radius: 50%; background: var(--brown-700); color: var(--gold); display: grid; place-items: center; font-weight: 800; font-size: 1.3rem; margin-bottom: .75rem; }
    .person .name { font-weight: 700; }
    .person .position { color: var(--teal); font-size: .95rem; }

    .activity-list { list-style: none; display: grid; gap: 1rem; margin-top: 1.5rem; }
    .activity-list li { display: grid; grid-template-columns: 9rem 1fr; gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--line); }
    .activity-list .date { font-weight: 700; color: var(--gold-deep); }
    .activity-list h3 { margin-bottom: .25rem; color: var(--brown-700); }
    .more { display: inline-block; margin-top: 1.5rem; font-weight: 700; color: var(--teal); }

    .contact dl { display: grid; grid-template-columns: 7rem 1fr; gap: .5rem 1rem; }
    .contact dt { font-weight: 700; color: var(--brown-700); }
    .contact a { color: var(--teal); }

    .site-footer { background: var(--brown-900); color: #d8c6ab; padding: 1.5rem 0; font-size: .9rem; border-top: 4px solid var(--gold); }

    @media (max-width: 720px) {
      .nav-toggle { display: block; }
      .nav { display: none; position: absolute; top: 68px; left: 0; right: 0; background: var(--white); border-bottom: 3px solid var(--gold); flex-direction: column; padding: 1rem 4%; gap: .75rem; }
      .nav.is-open { display: flex; }
      .hero .wrap { grid-template-columns: 1fr; gap: 2rem; }
      .hero .logo-seal { order: -1; width: 220px; }
      .two-col { grid-template-columns: 1fr; }
      .activity-list li { grid-template-columns: 1fr; gap: .25rem; }
    }
    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
    }
  </style>
</head>
<body>

<header class="site-header">
  <div class="wrap">
    <a class="brand" href="index.php">
      <img src="images/logo.jpg" alt="UMBO STR logo">
      <span><?= $e($org['name']) ?></span>
    </a>
    <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="main-nav">Menu</button>
    <nav class="nav" id="main-nav" data-nav aria-label="Main">
      <a href="#about">About</a>
      <a href="#mission">Mission &amp; Vision</a>
      <a href="#members">Members</a>
      <a href="index.php?page=activities">Activities</a>
      <a href="#contact">Contact</a>
      <a class="nav-cta" href="index.php?page=register">Register</a>
      <a class="admin-link" href="index.php?page=admin">Admin login</a>
    </nav>
  </div>
</header>

<main>
  <div class="hero">
    <div class="wrap">
      <div>
        <h1><?= $e($org['fullName']) ?></h1>
        <p class="place"><?= $e($org['location']) ?>. Established <?= $e($org['founded']) ?>.</p>
        <div class="actions">
          <a class="btn btn-primary" href="index.php?page=register">Register now</a>
          <a class="btn btn-ghost" href="index.php?page=activities">See our activities</a>
        </div>
      </div>
      <div class="logo-seal">
        <img src="images/logo.jpg" alt="Seal of the United Mangali Brotherhood Organization: a bridge over the Kalinga map, framed by a laurel wreath and hands">
      </div>
    </div>
  </div>

  <section id="about">
    <div class="wrap">
      <h2>About us</h2>
      <p class="lead"><?= $e($org['about']) ?></p>
    </div>
  </section>

  <section id="mission" class="band">
    <div class="wrap two-col">
      <div class="panel">
        <h3>Mission</h3>
        <p><?= $e($org['mission']) ?></p>
      </div>
      <div class="panel">
        <h3>Vision</h3>
        <p><?= $e($org['vision']) ?></p>
      </div>
    </div>
  </section>

  <section id="objectives">
    <div class="wrap">
      <h2>Objectives</h2>
      <ul class="objectives">
        <?php foreach ($org['objectives'] as $objective): ?>
          <li><?= $e($objective) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <section id="members" class="band">
    <div class="wrap">
      <h2>Officers &amp; Members</h2>
      <?php foreach ($peopleByGroup as $group => $people): ?>
        <h3 class="group-title"><?= $e($group) ?></h3>
        <ul class="people">
          <?php foreach ($people as $person): ?>
            <li class="person">
              <div class="avatar" aria-hidden="true"><?= $e(mb_strtoupper(mb_substr(ltrim($person['name'], '[ '), 0, 1))) ?></div>
              <p class="name"><?= $e($person['name']) ?></p>
              <p class="position"><?= $e($person['position']) ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="activities">
    <div class="wrap">
      <h2>Activities</h2>
      <ul class="activity-list">
        <?php foreach ($activities as $id => $activity): ?>
          <li>
            <span class="date"><?= $e($activity['date']) ?></span>
            <div>
              <h3><?= $e($activity['title']) ?></h3>
              <p><?= $e($activity['description']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="more" href="index.php?page=activities">View all activities</a>
    </div>
  </section>

  <section id="contact" class="band contact">
    <div class="wrap">
      <h2>Contact</h2>
      <dl>
        <dt>Location</dt><dd><?= $e($org['location']) ?></dd>
        <dt>Address</dt><dd><?= $e($org['contact']['address']) ?></dd>
        <dt>Email</dt><dd><?= $e($org['contact']['email']) ?></dd>
        <dt>Phone</dt><dd><?= $e($org['contact']['phone']) ?></dd>
        <dt>Facebook</dt><dd><a href="<?= $e($org['contact']['facebook']) ?>" target="_blank" rel="noopener">Visit our Facebook page</a></dd>
      </dl>
    </div>
  </section>
</main>

<footer class="site-footer">
  <div class="wrap">&copy; <?= date('Y') ?> <?= $e($org['fullName']) ?></div>
</footer>

<script src="java.js"></script>
</body>
</html>
