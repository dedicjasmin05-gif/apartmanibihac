(function () {
  'use strict';
  var root = document.querySelector('[data-vodic]');
  // Filteri
  if (root) {
    var chips = root.querySelectorAll('.vd__chip');
    var items = root.querySelectorAll('.vp');
    chips.forEach(function (c) {
      c.addEventListener('click', function () {
        chips.forEach(function (x) { x.classList.toggle('is-on', x === c); });
        var f = c.getAttribute('data-f');
        items.forEach(function (it) { it.hidden = !!f && it.getAttribute('data-g') !== f; });
        if (window.__vdFit) { window.__vdFit(f); }
      });
    });
  }
  // Karta (OpenStreetMap)
  var mapEl = document.getElementById('vd-map');
  if (mapEl && window.L) {
    var pts = Array.prototype.slice.call(document.querySelectorAll('.vp[data-lat]'));
    if (!pts.length) { mapEl.remove(); }
    else {
      var map = L.map(mapEl, { scrollWheelZoom: false });
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '&copy; OpenStreetMap' }).addTo(map);
      var mk = [];
      pts.forEach(function (el, i) {
        var ll = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];
        if (isNaN(ll[0])) { return; }
        var ic = L.divIcon({ className: '', html: '<div class="vmark' + (i === 0 ? ' vmark--1' : '') + '"><span>' + (i + 1) + '</span></div>', iconSize: [30, 30], iconAnchor: [15, 30], popupAnchor: [0, -28] });
        var m = L.marker(ll, { icon: ic }).addTo(map).bindPopup('<a href="#' + el.id + '">' + el.dataset.ime.replace(/</g, '&lt;') + '</a>');
        m._g = el.getAttribute('data-g');
        mk.push(m);
      });
      var fit = function (f) {
        var v = mk.filter(function (m) { var on = !f || m._g === f; if (on) { m.addTo(map); } else { map.removeLayer(m); } return on; });
        if (v.length) { map.fitBounds(L.featureGroup(v).getBounds().pad(0.15), { maxZoom: 15 }); }
      };
      window.__vdFit = fit;
      fit('');
    }
  }
  // Galerije: strelice, točkice, lightbox
  document.querySelectorAll('[data-gal]').forEach(function (g) {
    var links = Array.prototype.slice.call(g.querySelectorAll('a[href]'));
    if (links.length > 1) {
      var dots = document.createElement('div'); dots.className = 'vp__dots';
      links.forEach(function (_, i) { var d = document.createElement('i'); if (!i) { d.className = 'on'; } dots.appendChild(d); });
      g.parentNode.insertBefore(dots, g.nextSibling);
      var go = function (dir) { g.scrollBy({ left: dir * g.clientWidth, behavior: 'smooth' }); };
      ['p', 'n'].forEach(function (k) {
        var b = document.createElement('button'); b.type = 'button'; b.className = 'vp__nav vp__nav--' + k; b.setAttribute('aria-label', k === 'p' ? 'Prethodna' : 'Sljedeća'); b.textContent = k === 'p' ? '‹' : '›';
        b.addEventListener('click', function () { go(k === 'p' ? -1 : 1); }); g.parentNode.appendChild(b);
      });
      g.addEventListener('scroll', function () {
        var i = Math.round(g.scrollLeft / g.clientWidth);
        dots.querySelectorAll('i').forEach(function (d, n) { d.className = n === i ? 'on' : ''; });
      }, { passive: true });
    }
    links.forEach(function (a, n) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        var idx = n, box = document.createElement('div');
        box.className = 'lb'; box.setAttribute('role', 'dialog'); box.setAttribute('aria-modal', 'true');
        box.innerHTML = '<img alt=""><button class="lb__p" aria-label="Prethodna">‹</button><button class="lb__n" aria-label="Sljedeća">›</button><button class="lb__x" aria-label="Zatvori">×</button><span class="lb__c"></span>';
        var show = function () { box.querySelector('img').src = links[idx].href; box.querySelector('.lb__c').textContent = (links[idx].dataset.c || '') + '  ' + (idx + 1) + ' / ' + links.length; };
        var close = function () { box.remove(); document.body.style.overflow = ''; document.removeEventListener('keydown', key); };
        var key = function (ev) { if (ev.key === 'Escape') { close(); } if (ev.key === 'ArrowRight') { idx = (idx + 1) % links.length; show(); } if (ev.key === 'ArrowLeft') { idx = (idx - 1 + links.length) % links.length; show(); } };
        box.querySelector('.lb__p').onclick = function () { idx = (idx - 1 + links.length) % links.length; show(); };
        box.querySelector('.lb__n').onclick = function () { idx = (idx + 1) % links.length; show(); };
        box.querySelector('.lb__x').onclick = close;
        box.addEventListener('click', function (ev) { if (ev.target === box) { close(); } });
        document.addEventListener('keydown', key);
        document.body.appendChild(box); document.body.style.overflow = 'hidden'; show();
      });
    });
  });
})();
