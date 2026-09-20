document.addEventListener('DOMContentLoaded', function () {
  // Mobile navigation toggle
  var button = document.querySelector('[data-nav-toggle]');
  var nav = document.querySelector('[data-nav]');
  if (button && nav) {
    button.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      button.setAttribute('aria-expanded', String(open));
    });
  }

  // Birthday can't be in the future
  var today = new Date().toISOString().slice(0, 10);
  document.querySelectorAll('input[type="date"]').forEach(function (input) { input.max = today; });

  // Static hosting only: send the answers straight to the Google Form from the browser
  var form = document.querySelector('form[data-google-post]');
  if (!form) return;

  var entries = JSON.parse(form.getAttribute('data-entries') || '{}');
  var status = form.querySelector('[data-status]');

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (!form.checkValidity()) { form.reportValidity(); return; }
    if (form.elements['website'] && form.elements['website'].value) return; // bot

    var body = new URLSearchParams();
    Object.keys(entries).forEach(function (key) {
      var field = form.elements[key];
      if (!field) return;
      var value = field.value.trim();
      if (key === 'birthday') {
        var parts = value.split('-');
        body.append('entry.' + entries[key] + '_year', parts[0]);
        body.append('entry.' + entries[key] + '_month', String(parseInt(parts[1], 10)));
        body.append('entry.' + entries[key] + '_day', String(parseInt(parts[2], 10)));
      } else {
        body.append('entry.' + entries[key], value);
      }
    });
    body.append('fvv', '1');
    body.append('pageHistory', '0');

    var submit = form.querySelector('button[type="submit"]');
    submit.disabled = true;

    // Google does not allow reading the reply from another site, so "no-cors" is used.
    fetch(form.getAttribute('data-google-post'), { method: 'POST', mode: 'no-cors', body: body })
      .then(function () {
        status.hidden = false;
        status.className = 'alert alert-ok';
        status.textContent = 'Thank you! Your registration was sent.';
        form.reset();
        submit.disabled = false;
      })
      .catch(function () {
        status.hidden = false;
        status.className = 'alert alert-error';
        status.textContent = 'Could not send. Check your internet connection and try again.';
        submit.disabled = false;
      });
  });
});
