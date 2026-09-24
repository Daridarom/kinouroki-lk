/* Каталог фильмов: поиск, фильтр по качеству и ступени, сортировка.
   Состояние хранится в адресе (?q=&quality=&stage=&sort=), поэтому
   отфильтрованный список можно переслать коллеге ссылкой.
   Карточки уже отрисованы сервером — скрипт только прячет/переставляет их,
   так что без JavaScript страница тоже работает. */
(function () {
  var grid = document.getElementById('grid');
  if (!grid) return;
  var cards = Array.prototype.slice.call(grid.querySelectorAll('.film-card'));
  var q = document.getElementById('q');
  var quality = document.getElementById('quality');
  var sort = document.getElementById('sort');
  var stageSeg = document.getElementById('stage');
  var found = document.getElementById('found');
  var empty = document.getElementById('empty');
  var resetBtns = [document.getElementById('reset'), document.getElementById('reset2')];
  var state = { q: '', quality: '', stage: '', sort: 'new' };

  function norm(s) { return (s || '').toLowerCase().replace(/ё/g, 'е').trim(); }

  function readUrl() {
    var p = new URLSearchParams(location.search);
    state.q = p.get('q') || '';
    state.quality = p.get('quality') || '';
    state.stage = p.get('stage') || '';
    state.sort = p.get('sort') || 'new';
  }
  function writeUrl() {
    var p = new URLSearchParams();
    if (state.q) p.set('q', state.q);
    if (state.quality) p.set('quality', state.quality);
    if (state.stage) p.set('stage', state.stage);
    if (state.sort !== 'new') p.set('sort', state.sort);
    var s = p.toString();
    history.replaceState(null, '', location.pathname + (s ? '?' + s : ''));
  }
  function syncControls() {
    q.value = state.q;
    quality.value = state.quality;
    sort.value = state.sort;
    stageSeg.querySelectorAll('button').forEach(function (b) {
      b.setAttribute('aria-pressed', String(b.dataset.stage === state.stage));
    });
  }

  function apply() {
    var needle = norm(state.q);
    var shown = 0;
    cards.forEach(function (c) {
      var ok = (!needle || norm(c.dataset.q).indexOf(needle) >= 0) &&
               (!state.quality || c.dataset.quality === state.quality) &&
               (!state.stage || (' ' + c.dataset.stages + ' ').indexOf(' ' + state.stage + ' ') >= 0);
      c.classList.toggle('hidden', !ok);
      if (ok) shown++;
    });
    var sorted = cards.slice().sort(function (a, b) {
      if (state.sort === 'az') return a.dataset.title.localeCompare(b.dataset.title, 'ru');
      var d = (+a.dataset.order) - (+b.dataset.order);
      return state.sort === 'old' ? -d : d;
    });
    sorted.forEach(function (c) { grid.appendChild(c); });
    found.textContent = 'Показано ' + shown + ' из ' + cards.length;
    empty.classList.toggle('hidden', shown > 0);
    var dirty = state.q || state.quality || state.stage;
    resetBtns[0].hidden = !dirty;
    writeUrl();
  }

  var t;
  q.addEventListener('input', function () {
    clearTimeout(t);
    t = setTimeout(function () { state.q = q.value; apply(); }, 120);
  });
  quality.addEventListener('change', function () { state.quality = quality.value; apply(); });
  sort.addEventListener('change', function () { state.sort = sort.value; apply(); });
  stageSeg.addEventListener('click', function (e) {
    var b = e.target.closest('button');
    if (!b) return;
    state.stage = b.dataset.stage;
    syncControls(); apply();
  });
  resetBtns.forEach(function (b) {
    if (b) b.addEventListener('click', function () {
      state = { q: '', quality: '', stage: '', sort: state.sort };
      syncControls(); apply(); q.focus();
    });
  });

  // клик по чипу качества на карточке = фильтр по этому качеству
  grid.addEventListener('click', function (e) {
    var chip = e.target.closest('.poster .chip');
    if (!chip) return;
    e.preventDefault();
    state.quality = chip.closest('.film-card').dataset.quality;
    syncControls(); apply();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  readUrl(); syncControls(); apply();
})();
