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

  // Add / remove rows in the People and Activities editors
  var rows = document.querySelector('[data-rows]');
  var template = document.querySelector('[data-template]');
  var add = document.querySelector('[data-add]');

  if (add && rows && template) {
    add.addEventListener('click', function () {
      rows.appendChild(template.content.cloneNode(true));
      var last = rows.lastElementChild;
      var first = last && last.querySelector('input');
      if (first) first.focus();
    });
  }
  if (rows) {
    rows.addEventListener('click', function (event) {
      var remove = event.target.closest('[data-remove]');
      if (remove) remove.closest('[data-row]').remove();
    });
  }
});
