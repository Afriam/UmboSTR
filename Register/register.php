<p class="lead">Fill in this form to join the organization. New members register with the consultation and approval of the UMBO officers. When you press Register, your answers are sent to our membership registration form automatically.</p>

<?php if ($notice !== ''): ?>
  <div class="alert alert-error" role="alert"><?= $e($notice) ?></div>
<?php endif; ?>

<form class="form" method="post" action="<?= $e($formAction) ?>"<?= $formAttrs ?>>
  <input type="hidden" name="csrf" value="<?= $e($csrf) ?>">
  <div class="hp" aria-hidden="true">
    <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
  </div>

  <?php foreach ($fields as $field): ?>
    <div class="field">
      <label for="f-<?= $e($field['key']) ?>"><?= $e($field['label']) ?><?= $e($field['suffix']) ?></label>
      <?php if ($field['type'] === 'textarea'): ?>
        <textarea id="f-<?= $e($field['key']) ?>" name="<?= $e($field['key']) ?>" maxlength="<?= (int) $field['max'] ?>"<?= $field['required'] ? ' required' : '' ?>><?= $e($field['value']) ?></textarea>
      <?php else: ?>
        <input id="f-<?= $e($field['key']) ?>" name="<?= $e($field['key']) ?>" type="<?= $e($field['type']) ?>" value="<?= $e($field['value']) ?>" maxlength="<?= (int) $field['max'] ?>" autocomplete="<?= $e($field['autocomplete']) ?>"<?= $field['required'] ? ' required' : '' ?>>
      <?php endif; ?>
      <?php if ($field['error'] !== ''): ?>
        <p class="error"><?= $e($field['error']) ?></p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div class="actions">
    <button class="btn btn-primary" type="submit">Register</button>
  </div>
  <p class="alert alert-ok" data-status hidden role="status">Thank you! Your registration was sent.</p>
</form>
