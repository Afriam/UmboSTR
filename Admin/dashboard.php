<?php if ($flash): ?>
  <div class="alert <?= $flash['type'] === 'ok' ? 'alert-ok' : 'alert-error' ?>" role="status"><?= $e($flash['message']) ?></div>
<?php endif; ?>

<nav class="tabs" aria-label="Sections">
  <?php foreach ($sections as $key => $label): ?>
    <a href="index.php?action=dashboard&amp;section=<?= $e($key) ?>"<?= $key === $section ? ' aria-current="page"' : '' ?>><?= $e($label) ?></a>
  <?php endforeach; ?>
</nav>

<form class="form admin-form" method="post" action="index.php?action=save&amp;section=<?= $e($section) ?>">
  <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">

<?php if ($section === 'info'): ?>
  <div class="field"><label for="name">Short name (shown in the header)</label><input id="name" name="name" value="<?= $e($data['name']) ?>" maxlength="120"></div>
  <div class="field"><label for="fullName">Full name</label><input id="fullName" name="fullName" value="<?= $e($data['fullName']) ?>" maxlength="200"></div>
  <div class="field"><label for="location">Location</label><input id="location" name="location" value="<?= $e($data['location']) ?>" maxlength="150"></div>
  <div class="field"><label for="founded">Year established</label><input id="founded" name="founded" value="<?= $e($data['founded']) ?>" maxlength="20"></div>
  <div class="field"><label for="about">About us</label><textarea id="about" name="about" maxlength="3000"><?= $e($data['about']) ?></textarea></div>
  <div class="field"><label for="mission">Mission</label><textarea id="mission" name="mission" maxlength="2000"><?= $e($data['mission']) ?></textarea></div>
  <div class="field"><label for="vision">Vision</label><textarea id="vision" name="vision" maxlength="2000"><?= $e($data['vision']) ?></textarea></div>

<?php elseif ($section === 'contact'): ?>
  <div class="field"><label for="email">Email</label><input id="email" name="email" value="<?= $e($data['contact']['email']) ?>" maxlength="150"></div>
  <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="<?= $e($data['contact']['phone']) ?>" maxlength="50"></div>
  <div class="field"><label for="address">Address</label><input id="address" name="address" value="<?= $e($data['contact']['address']) ?>" maxlength="250"></div>
  <div class="field"><label for="facebook">Facebook link</label><input id="facebook" name="facebook" type="url" value="<?= $e($data['contact']['facebook']) ?>" maxlength="300"></div>

<?php elseif ($section === 'objectives'): ?>
  <div class="field">
    <label for="objectives">Objectives</label>
    <textarea id="objectives" name="objectives" rows="8"><?= $e(implode("\n", $data['objectives'])) ?></textarea>
    <p class="hint">One objective per line.</p>
  </div>

<?php elseif ($section === 'people'): ?>
  <div class="rows" data-rows>
    <?php foreach ($data['people'] as $person): ?>
      <div class="row-card" data-row>
        <div class="field"><label>Name</label><input name="people[name][]" value="<?= $e($person['name']) ?>" maxlength="120"></div>
        <div class="field"><label>Position</label><input name="people[position][]" value="<?= $e($person['position']) ?>" maxlength="120"></div>
        <div class="field"><label>Group</label>
          <select name="people[group][]">
            <option value="Officers"<?= ($person['group'] ?? '') === 'Officers' ? ' selected' : '' ?>>Officers</option>
            <option value="Members"<?= ($person['group'] ?? '') !== 'Officers' ? ' selected' : '' ?>>Members</option>
          </select>
        </div>
        <button class="btn btn-danger" type="button" data-remove>Remove</button>
      </div>
    <?php endforeach; ?>
  </div>
  <template data-template>
    <div class="row-card" data-row>
      <div class="field"><label>Name</label><input name="people[name][]" maxlength="120"></div>
      <div class="field"><label>Position</label><input name="people[position][]" maxlength="120"></div>
      <div class="field"><label>Group</label>
        <select name="people[group][]"><option value="Officers">Officers</option><option value="Members" selected>Members</option></select>
      </div>
      <button class="btn btn-danger" type="button" data-remove>Remove</button>
    </div>
  </template>
  <div class="actions"><button class="btn" type="button" data-add>Add person</button></div>

<?php elseif ($section === 'activities'): ?>
  <div class="rows" data-rows>
    <?php foreach ($data['activities'] as $activity): ?>
      <div class="row-card wide" data-row>
        <div class="field"><label>Title</label><input name="activities[title][]" value="<?= $e($activity['title']) ?>" maxlength="150"></div>
        <div class="field"><label>Date</label><input name="activities[date][]" value="<?= $e($activity['date']) ?>" maxlength="60"></div>
        <button class="btn btn-danger" type="button" data-remove>Remove</button>
        <div class="field full"><label>Description</label><textarea name="activities[description][]" maxlength="2000"><?= $e($activity['description']) ?></textarea></div>
      </div>
    <?php endforeach; ?>
  </div>
  <template data-template>
    <div class="row-card wide" data-row>
      <div class="field"><label>Title</label><input name="activities[title][]" maxlength="150"></div>
      <div class="field"><label>Date</label><input name="activities[date][]" maxlength="60"></div>
      <button class="btn btn-danger" type="button" data-remove>Remove</button>
      <div class="field full"><label>Description</label><textarea name="activities[description][]" maxlength="2000"></textarea></div>
    </div>
  </template>
  <div class="actions"><button class="btn" type="button" data-add>Add activity</button></div>

<?php elseif ($section === 'password'): ?>
  <div class="field"><label for="current">Current password</label><input id="current" name="current" type="password" autocomplete="current-password" required></div>
  <div class="field"><label for="new">New password</label><input id="new" name="new" type="password" autocomplete="new-password" minlength="8" required></div>
  <div class="field"><label for="confirm">Type the new password again</label><input id="confirm" name="confirm" type="password" autocomplete="new-password" minlength="8" required></div>
<?php endif; ?>

  <div class="actions">
    <button class="btn btn-primary" type="submit">Save changes</button>
  </div>
</form>
