/* StudySpot - small helpers used across the site */

// 1. Star rating input on review.php
document.querySelectorAll('.star-input').forEach(function (widget) {
  var input = document.getElementById(widget.dataset.input);
  var stars = widget.querySelectorAll('span');
  stars.forEach(function (star, index) {
    star.addEventListener('click', function () {
      input.value = index + 1;
      stars.forEach(function (s, i) { s.classList.toggle('on', i <= index); });
      var out = document.getElementById('ratingValue');
      if (out) { out.textContent = (index + 1) + '.0'; }
    });
  });
});

// 2. People counter + live total on booking.php
var counter = document.getElementById('peopleCounter');
if (counter) {
  var field = document.getElementById('people');
  var out = document.getElementById('peopleOut');
  var totalOut = document.getElementById('totalOut');
  var unit = parseFloat(counter.dataset.price || '0');
  counter.querySelectorAll('button').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var next = parseInt(field.value, 10) + parseInt(btn.dataset.step, 10);
      if (next < 1) next = 1;
      if (next > 10) next = 10;
      field.value = next;
      out.textContent = next;
      if (totalOut) { totalOut.textContent = 'Rs. ' + (unit * next).toFixed(0); }
    });
  });
}

// 3. "Use My Location" button on the home page
var geoBtn = document.getElementById('useLocation');
if (geoBtn && navigator.geolocation) {
  geoBtn.addEventListener('click', function () {
    geoBtn.textContent = 'Finding you...';
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        window.location = 'map.php?lat=' + pos.coords.latitude.toFixed(5) +
                          '&lng=' + pos.coords.longitude.toFixed(5);
      },
      function () {
        geoBtn.textContent = 'Use My Location';
        alert('Location is blocked in your browser. Search by name instead.');
      }
    );
  });
}

// 4. Filters submit themselves when something changes
var filterForm = document.getElementById('filterForm');
if (filterForm) {
  filterForm.querySelectorAll('input').forEach(function (el) {
    el.addEventListener('change', function () { filterForm.submit(); });
  });
}
