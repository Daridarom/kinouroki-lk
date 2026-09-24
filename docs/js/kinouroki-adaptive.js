/* kinouroki-adaptive.js — улучшения ЛК «Киноуроки». Без зависимостей.
   1) мгновенные фильтры каталога (поверх обычной GET-формы);
   2) «ленивые» плееры на странице фильма;
   3) запасное открытие бокового меню, если боевой app.js недоступен. */
(function () {
  'use strict';

  /* ---------- 1. Фильтры каталога ---------- */
  var form = document.querySelector('[data-ka-filters]');
  var grid = document.querySelector('[data-ka-grid]');
  if (form && grid) {
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.film-card'));
    var q = form.querySelector('[name=q]');
    var quality = form.querySelector('[name=quality]');
    var sort = form.querySelector('[name=sort]');
    var found = form.querySelector('[data-ka-found]');
    var empty = document.querySelector('[data-ka-empty]');
    var catLinks = form.querySelectorAll('[data-ka-cat]');
    var activeCat = (form.querySelector('.btn-primary[data-ka-cat]') || {}).getAttribute ?
      form.querySelector('.btn-primary[data-ka-cat]').getAttribute('data-ka-cat') : '';
    cards.forEach(function (c, i) { c.dataset.kaOrder = i; });

    var norm = function (s) { return (s || '').toLowerCase().replace(/ё/g, 'е').trim(); };
    var apply = function () {
      var needle = norm(q.value), shown = 0;
      cards.forEach(function (c) {
        var ok = (!needle || norm(c.dataset.kaSearch).indexOf(needle) >= 0) &&
                 (!quality.value || c.dataset.kaQuality === quality.value) &&
                 (!activeCat || (' ' + c.dataset.kaCats + ' ').indexOf(' ' + activeCat + ' ') >= 0);
        c.hidden = !ok; if (ok) shown++;
      });
      var sorted = cards.slice().sort(function (a, b) {
        if (sort.value === 'az') return a.querySelector('.ka-title').textContent.localeCompare(b.querySelector('.ka-title').textContent, 'ru');
        var d = a.dataset.kaOrder - b.dataset.kaOrder; return sort.value === 'old' ? -d : d;
      });
      sorted.forEach(function (c) { grid.appendChild(c); });
      if (found) found.textContent = 'Показано ' + shown + ' из ' + cards.length;
      if (empty) empty.hidden = shown > 0;
      var p = new URLSearchParams();
      if (q.value) p.set('q', q.value);
      if (quality.value) p.set('quality', quality.value);
      if (sort.value && sort.value !== 'new') p.set('sort', sort.value);
      history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p : ''));
    };
    // Ссылки ступеней работают и как обычные адреса /films/category/{id};
    // на статичной копии (без сервера) фильтруем на месте.
    catLinks.forEach(function (a) {
      a.addEventListener('click', function (e) {
        if (!document.documentElement.hasAttribute('data-ka-static')) return;
        e.preventDefault();
        activeCat = a.getAttribute('data-ka-cat');
        catLinks.forEach(function (x) { x.classList.toggle('btn-primary', x === a); x.classList.toggle('btn-outline-primary', x !== a); });
        apply();
      });
    });
    var t;
    q.addEventListener('input', function () { clearTimeout(t); t = setTimeout(apply, 150); });
    quality.addEventListener('change', apply);
    sort.addEventListener('change', apply);
    form.addEventListener('submit', function (e) { e.preventDefault(); apply(); });
    var reset = document.querySelector('[data-ka-reset]');
    if (reset) reset.addEventListener('click', function () { q.value = ''; quality.value = ''; apply(); q.focus(); });
    // восстановить состояние из адреса (?q=&quality=&sort=)
    var sp = new URLSearchParams(location.search);
    if (sp.get('q')) q.value = sp.get('q');
    if (sp.get('quality')) quality.value = sp.get('quality');
    if (sp.get('sort')) sort.value = sp.get('sort');
    apply();
  }

  /* ---------- 2. Ленивые плееры ---------- */
  var mount = function (box, autoplay) {
    if (box.dataset.kaMounted) return;
    var src = box.dataset.kaSrc, el;
    if (/\.mp4(\?|$)/i.test(src)) {
      el = document.createElement('video'); el.controls = true; el.src = src; el.autoplay = !!autoplay;
    } else {
      el = document.createElement('iframe');
      el.src = src; el.allowFullscreen = true; el.title = 'Видео';
      el.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen';
    }
    box.innerHTML = ''; box.appendChild(el); box.dataset.kaMounted = '1';
  };
  document.querySelectorAll('.ka-player[data-ka-src]').forEach(function (box) {
    var b = document.createElement('button');
    b.type = 'button'; b.className = 'ka-player-cover'; b.setAttribute('aria-label', 'Смотреть');
    if (box.dataset.kaPoster) { var img = new Image(); img.src = box.dataset.kaPoster; img.alt = ''; b.appendChild(img); }
    b.addEventListener('click', function () { mount(box, true); });
    box.appendChild(b);
  });

  /* ---------- 2a. Фильтр практик: на телефоне свёрнут ---------- */
  document.querySelectorAll('.ka-filter-details').forEach(function (d) {
    if (window.matchMedia('(max-width: 767.98px)').matches) d.removeAttribute('open');
  });

  /* ---------- 3. Боковое меню: запасной вариант ---------- */
  var btn = document.getElementById('collapse-side-menu-btn');
  var menu = document.getElementById('side-menu');
  if (btn && menu) {
    setTimeout(function () {
      if (btn.dataset.kaBound) return;
      // если боевой app.js уже повесил обработчик, он сработает сам; этот — только aria
      btn.addEventListener('click', function () {
        btn.setAttribute('aria-expanded', String(btn.getAttribute('aria-expanded') !== 'true'));
      });
      btn.dataset.kaBound = '1';
    }, 0);
  }
})();
