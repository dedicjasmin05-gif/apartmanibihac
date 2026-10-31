(function () {
  'use strict';
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Mobilni izbornik
  var t = document.querySelector('.nav-toggle');
  var m = document.getElementById('mob-nav');
  if (t && m) {
    t.addEventListener('click', function () {
      var open = t.getAttribute('aria-expanded') === 'true';
      t.setAttribute('aria-expanded', String(!open));
      m.hidden = open;
    });
  }

  // Hero: izmjena fotografija
  var slides = document.querySelectorAll('[data-slides] .hero__img');
  if (slides.length > 1 && !reduce) {
    var i = 0;
    setInterval(function () {
      if (document.hidden) { return; }
      slides[i].classList.remove('is-on');
      i = (i + 1) % slides.length;
      slides[i].classList.add('is-on');
    }, 6500);
  }

  // Odlazak ne može biti prije dolaska
  document.querySelectorAll('form').forEach(function (f) {
    var a = f.querySelector('input[name="dolazak"]');
    var d = f.querySelector('input[name="odlazak"]');
    if (!a || !d) { return; }
    a.addEventListener('change', function () {
      if (!a.value) { return; }
      var n = new Date(a.value); n.setDate(n.getDate() + 1);
      var min = n.toISOString().slice(0, 10);
      d.min = min;
      if (!d.value || d.value < min) { d.value = min; }
    });
  });

  // Upit na stranici objekta: preuzmi datume iz pretrage (?dolazak=&odlazak=&osobe=)
  var p = new URLSearchParams(location.search);
  var form = document.querySelector('.abu-form');
  if (form) {
    ['dolazak', 'odlazak', 'osobe'].forEach(function (k) {
      var v = p.get(k);
      var el = form.querySelector('[name="' + k + '"]');
      if (v && el) { el.value = v; }
    });
    // Klik na slobodan dan u kalendaru upisuje dolazak, drugi klik odlazak
    var pick = 0;
    document.querySelectorAll('.abu-kal .abu-s').forEach(function (day) {
      day.style.cursor = 'pointer';
      day.setAttribute('role', 'button');
      day.setAttribute('tabindex', '0');
      var choose = function () {
        var dv = day.getAttribute('data-d');
        var a = form.querySelector('[name="dolazak"]');
        var o = form.querySelector('[name="odlazak"]');
        if (pick === 0 || (a.value && dv <= a.value)) { a.value = dv; o.value = ''; pick = 1; }
        else { o.value = dv; pick = 0; form.querySelector('[name="ime"]').focus({ preventScroll: false }); }
      };
      day.addEventListener('click', choose);
      day.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); choose(); } });
    });
  }

  // Galerija (lightbox)
  var gal = document.querySelector('[data-gallery]');
  if (gal) {
    var links = Array.prototype.slice.call(gal.querySelectorAll('a[href]'));
    var box = null, idx = 0;
    var show = function () {
      box.querySelector('img').src = links[idx].href;
      box.querySelector('.lb__c').textContent = (idx + 1) + ' / ' + links.length;
    };
    var open = function (n) {
      idx = n;
      if (!box) {
        box = document.createElement('div');
        box.className = 'lb';
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');
        box.setAttribute('aria-label', 'Fotografije');
        box.innerHTML = '<img alt=""><button class="lb__p" aria-label="Prethodna">‹</button><button class="lb__n" aria-label="Sljedeća">›</button><button class="lb__x" aria-label="Zatvori">×</button><span class="lb__c"></span>';
        document.body.appendChild(box);
        box.querySelector('.lb__p').onclick = function () { idx = (idx - 1 + links.length) % links.length; show(); };
        box.querySelector('.lb__n').onclick = function () { idx = (idx + 1) % links.length; show(); };
        box.querySelector('.lb__x').onclick = close;
        box.addEventListener('click', function (e) { if (e.target === box) { close(); } });
      }
      box.hidden = false;
      document.body.style.overflow = 'hidden';
      show();
      box.querySelector('.lb__x').focus();
    };
    var close = function () { box.hidden = true; document.body.style.overflow = ''; };
    document.addEventListener('keydown', function (e) {
      if (!box || box.hidden) { return; }
      if (e.key === 'Escape') { close(); }
      if (e.key === 'ArrowRight') { idx = (idx + 1) % links.length; show(); }
      if (e.key === 'ArrowLeft') { idx = (idx - 1 + links.length) % links.length; show(); }
    });
    links.forEach(function (l, n) { l.addEventListener('click', function (e) { e.preventDefault(); open(n); }); });
    var all = gal.querySelector('[data-gallery-open]');
    if (all) { all.addEventListener('click', function () { open(0); }); }
  }
})();
