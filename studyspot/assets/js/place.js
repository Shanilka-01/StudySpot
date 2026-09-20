/* place.js – gallery thumbnails + "See All" reviews. Owner: Member 3 */
(function () {
    'use strict';
    document.documentElement.classList.add('js');

    /* Gallery: click a thumbnail to show it in the big frame */
    var main = document.getElementById('gallery-main');
    document.querySelectorAll('.gallery-thumbs .thumb').forEach(function (btn, i) {
        btn.addEventListener('click', function () {
            if (!main) return;
            main.src = btn.getAttribute('data-full');
            main.alt = 'Photo ' + (i + 1);
            document.querySelectorAll('.gallery-thumbs .thumb').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
        });
    });

    /* Reviews: show the hidden ones */
    var more = document.querySelector('[data-see-all]');
    if (more) {
        more.addEventListener('click', function () {
            var open = document.querySelector('.reviews').classList.toggle('show-all');
            more.textContent = open ? 'Show less' : 'See All';
            more.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
})();
