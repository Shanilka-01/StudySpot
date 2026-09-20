/* review.js – rating number + 500-character counter. Owner: Member 3 */
(function () {
    'use strict';
    var value = document.getElementById('rating-value');
    document.querySelectorAll('.star-input input').forEach(function (r) {
        r.addEventListener('change', function () { if (value) value.textContent = Number(r.value).toFixed(1); });
    });

    var ta = document.getElementById('text'), count = document.getElementById('char-count');
    if (ta && count) {
        var update = function () { count.textContent = ta.value.length; };
        ta.addEventListener('input', update);
        update();
    }
})();
