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


  /* ---------- [KA-030][KA-032] Практики: мгновенный поиск, сортировка, «Показать ещё» ---------- */
  var prForm = document.querySelector('[data-ka-pr-form]');
  var prGrid = document.querySelector('[data-ka-pr-grid]');
  if (prForm && prGrid) {
    var PAGE = 24, limit = PAGE;
    var prItems = Array.prototype.slice.call(prGrid.querySelectorAll('[data-ka-pr]'));
    var prQ = prForm.querySelector('[name=q]'), prFilm = prForm.querySelector('[name=film]'), prSort = prForm.querySelector('[name=sort]');
    var prMore = document.querySelector('[data-ka-pr-more]'), prEmpty = document.querySelector('[data-ka-pr-empty]');
    var prFound = document.querySelector('[data-ka-pr-found]');
    var normQ = function (s) {
      return (s || '').toLowerCase().replace(/ё/g, 'е').replace(/[«»"'“”„()\[\].,!?:;—–-]+/g, ' ').replace(/\s+/g, ' ').trim();
    };
    var prApply = function (resetLimit) {
      if (resetLimit) limit = PAGE;
      var words = normQ(prQ.value).split(' ').filter(Boolean), match = [];
      prItems.forEach(function (el) {
        var ok = words.every(function (w) { return el.dataset.kaText.indexOf(w) >= 0; }) &&
                 (!prFilm.value || el.dataset.kaFilm === prFilm.value);
        if (ok) match.push(el); else el.hidden = true;
      });
      match.sort(function (a, b) {
        if (prSort.value === 'score') return b.dataset.kaExpert - a.dataset.kaExpert;
        if (prSort.value === 'title') return a.dataset.kaTitle.localeCompare(b.dataset.kaTitle, 'ru');
        var d = a.dataset.kaI - b.dataset.kaI; return prSort.value === 'old' ? -d : d;
      });
      match.forEach(function (el, i) { el.hidden = i >= limit; prGrid.appendChild(el); });
      if (prMore) { prMore.hidden = match.length <= limit; prMore.textContent = 'Показать ещё (' + Math.max(0, match.length - limit) + ')'; }
      if (prEmpty) prEmpty.hidden = match.length > 0;
      if (prFound) prFound.textContent = (words.length || prFilm.value ? 'Найдено: ' : 'В копии: ') + match.length + ' из ' + prItems.length + ' практик (обезличенные примеры)';
      var p = new URLSearchParams();
      if (prQ.value) p.set('q', prQ.value);
      if (prFilm.value) p.set('film', prFilm.value);
      if (prSort.value !== 'new') p.set('sort', prSort.value);
      history.replaceState(null, '', location.pathname + (p.toString() ? '?' + p : ''));
    };
    var prT;
    prQ.addEventListener('input', function () { clearTimeout(prT); prT = setTimeout(function () { prApply(true); }, 200); });
    prFilm.addEventListener('change', function () { prApply(true); });
    prSort.addEventListener('change', function () { prApply(true); });
    prForm.addEventListener('submit', function (e) { e.preventDefault(); prApply(true); });
    var prReset = prForm.querySelector('[data-ka-pr-reset]');
    if (prReset) prReset.addEventListener('click', function (e) { e.preventDefault(); prQ.value = ''; prFilm.value = ''; prSort.value = 'new'; prApply(true); });
    if (prMore) prMore.addEventListener('click', function () { limit += PAGE; prApply(false); });
    var sp2 = new URLSearchParams(location.search);
    if (sp2.get('q')) prQ.value = sp2.get('q');
    if (sp2.get('film')) prFilm.value = sp2.get('film');
    if (sp2.get('sort')) prSort.value = sp2.get('sort');
    prApply(true);
  }

  /* ---------- [KA-053] Постер не загрузился — убираем «битую картинку», остаётся название на фирменном фоне ---------- */
  document.querySelectorAll('.ka-poster img').forEach(function (img) {
    img.addEventListener('error', function () { img.remove(); });
  });

  /* ---------- [KA-024] Поиск по странице: документы, вопросы, вебинары ---------- */
  var live = document.querySelector('[data-ka-live-search]');
  if (live) {
    var scope = document.querySelector(live.getAttribute('data-ka-live-search'));
    var liveFound = document.querySelector('[data-ka-live-found]');
    var rows = Array.prototype.slice.call(scope.querySelectorAll('.list-group-item, .accordion-item, [data-ka-live-item]')); // [KA-045] + карточки киноуроков
    var nrm = function (s) { return (s || '').toLowerCase().replace(/ё/g, 'е'); };
    live.addEventListener('input', function () {
      var q = nrm(live.value.trim()), n = 0;
      rows.forEach(function (r) { var ok = !q || nrm(r.textContent).indexOf(q) >= 0; r.hidden = !ok; if (ok) n++; });
      scope.querySelectorAll('.list-group').forEach(function (g) {
        var any = Array.prototype.some.call(g.children, function (c) { return !c.hidden; });
        g.hidden = !any;
        var h = g.previousElementSibling; if (h && h.tagName === 'H4') h.hidden = !any;
      });
      if (liveFound) liveFound.textContent = q ? 'Найдено: ' + n : '';
    });
  }

  /* ---------- [KA-025] FAQ: открыть вопрос по адресу #faq-N и скопировать ссылку ---------- */
  if (/^#faq-\d+$/.test(location.hash)) {
    var item = document.querySelector(location.hash);
    var btn2 = item && item.querySelector('.accordion-button');
    if (btn2) { setTimeout(function () { btn2.click(); item.scrollIntoView({ block: 'start' }); }, 300); }
  }
  document.querySelectorAll('[data-ka-copy-link]').forEach(function (b) {
    b.addEventListener('click', function () {
      var url = location.href.split('#')[0] + b.getAttribute('data-ka-copy-link');
      var done = function () { b.textContent = 'Ссылка скопирована'; setTimeout(function () { b.textContent = 'Скопировать ссылку на вопрос'; }, 2000); };
      if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, function () { prompt('Ссылка на вопрос:', url); });
      else prompt('Ссылка на вопрос:', url);
    });
  });

  /* ---------- [KA-034] Мобильное меню: закрытие по пункту, по Esc и по тапу на затемнение ---------- */
  var sm = document.getElementById('side-menu'), smBtn = document.getElementById('collapse-side-menu-btn');
  if (sm && smBtn) {
    var shade = document.createElement('div'); shade.className = 'ka-shade'; document.body.appendChild(shade);
    var isMobile = function () { return window.matchMedia('(max-width: 800px)').matches; };
    var isOpen = function () { return isMobile() && /translateX\(0/.test(sm.style.transform || ''); };
    var sync = function () { var o = isOpen(); document.body.classList.toggle('ka-menu-open', o); smBtn.setAttribute('aria-expanded', String(o)); };
    var close = function () { if (isOpen()) smBtn.click(); };
    new MutationObserver(sync).observe(sm, { attributes: true, attributeFilter: ['style', 'class'] });
    shade.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    sm.addEventListener('click', function (e) { if (e.target.closest('a[href]')) close(); });
    sync();
  }

  /* ---------- [KA-035] Кнопка «Наверх» на длинных страницах ---------- */
  var up = document.createElement('button');
  up.type = 'button'; up.className = 'ka-to-top'; up.setAttribute('aria-label', 'Наверх'); up.textContent = '↑'; up.hidden = true;
  document.body.appendChild(up);
  up.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  window.addEventListener('scroll', function () { up.hidden = window.scrollY < 900; }, { passive: true });

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
