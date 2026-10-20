(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---- sidebar mobile + overlay ---- */
  var sidebar = $('#sidebar'), overlay = $('#overlay'), burger = $('#burger');
  function toggleMenu(force) {
    if (!sidebar) return;
    var open = typeof force === 'boolean' ? force : !sidebar.classList.contains('open');
    sidebar.classList.toggle('open', open);
    if (overlay) overlay.classList.toggle('show', open);
  }
  if (burger) burger.addEventListener('click', function () { toggleMenu(); });
  if (overlay) overlay.addEventListener('click', function () { toggleMenu(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') toggleMenu(false); });

  /* ---- angka naik (count-up) ---- */
  function countUp(el) {
    var target = parseInt(el.getAttribute('data-count'), 10);
    if (isNaN(target)) return;
    var dur = 1100, start = null;
    el.textContent = '0';
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased).toLocaleString('id-ID');
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  $$('[data-count]').forEach(countUp);

  /* ---- efek ripple pada tombol ---- */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('.btn') : null;
    if (!btn) return;
    var r = btn.getBoundingClientRect(), size = Math.max(r.width, r.height);
    var s = document.createElement('span');
    s.className = 'ripple';
    s.style.width = s.style.height = size + 'px';
    s.style.left = (e.clientX - r.left - size / 2) + 'px';
    s.style.top = (e.clientY - r.top - size / 2) + 'px';
    btn.appendChild(s);
    setTimeout(function () { s.remove(); }, 650);
  });

  /* ---- notifikasi hilang otomatis ---- */
  $$('.alert-success').forEach(function (a) {
    setTimeout(function () { a.classList.add('hide'); setTimeout(function () { a.remove(); }, 600); }, 4000);
  });

  /* ---- kelopak bunga jatuh di halaman login/register ---- */
  var auth = $('.auth-body');
  if (auth && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var icons = ['🌸', '🌷', '🌺', '💮', '🌼'];
    for (var i = 0; i < 14; i++) {
      var p = document.createElement('span');
      p.className = 'petal';
      p.textContent = icons[i % icons.length];
      p.style.left = Math.random() * 100 + '%';
      p.style.fontSize = (14 + Math.random() * 18) + 'px';
      p.style.animationDuration = (9 + Math.random() * 10) + 's';
      p.style.animationDelay = (-Math.random() * 15) + 's';
      auth.appendChild(p);
    }
  }

  /* ---- tampil/sembunyikan password ---- */
  $$('input[type=password]').forEach(function (inp) {
    var wrap = document.createElement('div');
    wrap.className = 'pass-wrap';
    inp.parentNode.insertBefore(wrap, inp);
    wrap.appendChild(inp);
    var b = document.createElement('button');
    b.type = 'button'; b.className = 'pass-toggle'; b.title = 'Tampilkan password'; b.textContent = '👁';
    b.addEventListener('click', function () {
      var show = inp.type === 'password';
      inp.type = show ? 'text' : 'password';
      b.textContent = show ? '🙈' : '👁';
    });
    wrap.appendChild(b);
  });

  /* ---- preview gambar produk (klik, pilih, atau seret) ---- */
  var prev = $('#preview'), file = $('input[type=file][name=gambar]');
  function showPreview(f) {
    if (!f || !prev) return;
    var rd = new FileReader();
    rd.onload = function (ev) { prev.innerHTML = '<img src="' + ev.target.result + '" alt="">'; };
    rd.readAsDataURL(f);
  }
  if (file) file.addEventListener('change', function () { showPreview(file.files[0]); });
  if (prev && file) {
    prev.style.cursor = 'pointer';
    prev.addEventListener('click', function () { file.click(); });
    ['dragenter', 'dragover'].forEach(function (ev) {
      prev.addEventListener(ev, function (e) { e.preventDefault(); prev.classList.add('drag'); });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      prev.addEventListener(ev, function (e) { e.preventDefault(); prev.classList.remove('drag'); });
    });
    prev.addEventListener('drop', function (e) {
      if (e.dataTransfer.files.length) {
        try { file.files = e.dataTransfer.files; } catch (err) {}
        showPreview(e.dataTransfer.files[0]);
      }
    });
  }
})();
