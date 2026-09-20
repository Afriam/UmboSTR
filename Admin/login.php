<?php if ($error !== ''): ?>
  <div class="alert alert-error" role="alert"><?= $e($error) ?></div>
<?php endif; ?>

<form class="form" method="post" action="index.php?action=login">
  <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
  <div class="field">
    <label for="username">Username</label>
    <input id="username" name="username" type="text" autocomplete="username" required autofocus>
  </div>
  <div class="field">
    <label for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
  </div>
  <div class="actions">
    <button class="btn btn-primary" type="submit">Log in</button>
  </div>
</form>
