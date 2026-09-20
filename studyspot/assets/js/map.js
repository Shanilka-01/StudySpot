/* map.js – Leaflet map for map.php. Owner: Member 4
   Data comes from window.STUDYSPOT_MAP (printed by map.php). */
(function () {
    'use strict';
    var el = document.getElementById('map');
    var data = window.STUDYSPOT_MAP;
    if (!el || !data) return;

    if (typeof L === 'undefined') {
        el.innerHTML = '<p class="map-msg">The map could not be loaded (no internet connection?). The list on the right still works.</p>';
        return;
    }

    var map = L.map(el, { zoomControl: true }).setView([data.center.lat, data.center.lng], 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    function pin(color) {
        return L.divIcon({
            className: 'pin',
            html: '<svg width="32" height="42" viewBox="0 0 32 42" aria-hidden="true">' +
                  '<path d="M16 1C8 1 2 7 2 15c0 10.5 14 25 14 25s14-14.5 14-25C30 7 24 1 16 1z" fill="' + color + '" stroke="#fff" stroke-width="2"/>' +
                  '<circle cx="16" cy="15" r="5.5" fill="#fff"/></svg>',
            iconSize: [32, 42], iconAnchor: [16, 40], popupAnchor: [0, -36]
        });
    }

    function popupFor(p) {
        var box = document.createElement('div');
        box.className = 'map-popup';
        var a = document.createElement('a');
        a.href = p.url; a.textContent = p.name; a.className = 'map-popup-title';
        var s = document.createElement('div');
        s.textContent = p.area + ' · ★ ' + Number(p.rating).toFixed(1) + ' · ' + p.price;
        box.appendChild(a); box.appendChild(s);
        return box;
    }

    var markers = {}, nearby = [];
    data.places.forEach(function (p) {
        var m = L.marker([p.lat, p.lng], { icon: pin('#22a53b'), title: p.name }).addTo(map).bindPopup(popupFor(p));
        markers[p.id] = m;
        if (data.nearbyIds.indexOf(p.id) !== -1) nearby.push([p.lat, p.lng]);
    });

    if (data.user) {
        L.marker([data.user.lat, data.user.lng], { icon: pin('#2563eb'), title: 'You are here' }).addTo(map).bindPopup('You are here');
        nearby.push([data.user.lat, data.user.lng]);
    }
    if (nearby.length > 1) map.fitBounds(nearby, { padding: [50, 50], maxZoom: 15 });

    /* Clicking a card in the side list highlights its marker (Ctrl/Cmd-click still opens the page) */
    document.querySelectorAll('.nearby-card').forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            var m = markers[card.getAttribute('data-place')];
            if (m) m.setZIndexOffset(1000);
        });
        card.addEventListener('mouseleave', function () {
            var m = markers[card.getAttribute('data-place')];
            if (m) m.setZIndexOffset(0);
        });
    });
})();
