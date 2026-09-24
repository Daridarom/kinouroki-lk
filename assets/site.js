/* Общие скрипты: мобильное меню и выпадающее «Ещё». Без зависимостей. */
(function () {
  var header = document.getElementById('header');
  var burger = document.getElementById('burger');
  var more = document.getElementById('navMore');

  if (burger && header) {
    burger.addEventListener('click', function () {
      var open = header.classList.toggle('open');
      burger.setAttribute('aria-expanded', open);
      burger.textContent = open ? '✕' : '☰';
    });
  }
  if (more) {
    var btn = more.querySelector('button');
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = more.classList.toggle('open');
      btn.setAttribute('aria-expanded', open);
    });
    document.addEventListener('click', function () {
      more.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
    });
  }
})();
