/* Страница фильма: вкладки и «ленивый» плеер.
   Плеер Rutube/YouTube грузится только по клику — страница открывается
   мгновенно и не тянет чужие скрипты, пока их не попросили. */
(function () {
  // --- вкладки (доступные: стрелки влево/вправо, aria-selected) ---
  var tabs = Array.prototype.slice.call(document.querySelectorAll('[role="tab"]'));
  function select(tab) {
    tabs.forEach(function (t) {
      var on = t === tab;
      t.setAttribute('aria-selected', on);
      t.tabIndex = on ? 0 : -1;
      document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
    });
    history.replaceState(null, '', '#' + tab.id.slice(2));
  }
  tabs.forEach(function (t, i) {
    t.addEventListener('click', function () { select(t); });
    t.addEventListener('keydown', function (e) {
      var d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
      if (!d) return;
      var n = tabs[(i + d + tabs.length) % tabs.length];
      n.focus(); select(n);
    });
  });
  var fromHash = tabs.filter(function (t) { return '#' + t.id.slice(2) === location.hash; })[0];
  if (fromHash) select(fromHash);

  // --- плеер ---
  var player = document.getElementById('player');
  if (!player) return;
  var current = player.dataset.film ? 'film' : 'teaser';

  function embed(kind, autoplay) {
    var src = kind === 'film' ? player.dataset.film : player.dataset.teaser;
    if (!src) return;
    var el;
    if (/\.mp4$/i.test(src)) {
      el = document.createElement('video');
      el.controls = true; el.src = src; if (autoplay) el.autoplay = true;
    } else {
      el = document.createElement('iframe');
      el.src = src + (autoplay && src.indexOf('youtube') > 0 ? (src.indexOf('?') > 0 ? '&' : '?') + 'autoplay=1' : '');
      el.allow = 'autoplay; clipboard-write; encrypted-media; picture-in-picture; fullscreen';
      el.allowFullscreen = true;
      el.title = kind === 'film' ? 'Фильм' : 'Трейлер';
    }
    player.innerHTML = '';
    player.appendChild(el);
    current = kind;
  }

  var cover = document.getElementById('playerCover');
  if (cover) cover.addEventListener('click', function () { embed(current, true); });

  document.querySelectorAll('.player-tabs [data-src]').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('.player-tabs [data-src]').forEach(function (x) {
        var on = x === b;
        x.setAttribute('aria-pressed', on);
        x.classList.toggle('btn-primary', on);
        x.classList.toggle('btn-ghost', !on);
      });
      embed(b.dataset.src, true);
    });
  });
})();
