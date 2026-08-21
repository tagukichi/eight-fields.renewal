/*!
 * EIGHT FIELDS — renewal front-end behaviour
 * Vanilla JS, no dependencies. Safe to load with `defer`.
 */
(function () {
  'use strict';

  var doc = document;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------------------------------------------------------------- header */
  function initHeader() {
    var header = doc.querySelector('[data-header]');
    if (!header) return;

    var stuck = false;
    function onScroll() {
      var next = window.scrollY > 24;
      if (next !== stuck) {
        stuck = next;
        header.classList.toggle('is-stuck', stuck);
      }
    }
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ------------------------------------------------------- mobile drawer */
  function initDrawer() {
    var burger = doc.querySelector('[data-burger]');
    var drawer = doc.querySelector('[data-drawer]');
    if (!burger || !drawer) return;

    function setOpen(open) {
      burger.classList.toggle('is-open', open);
      burger.setAttribute('aria-expanded', String(open));
      drawer.classList.toggle('is-open', open);
      drawer.setAttribute('aria-hidden', String(!open));
      doc.body.classList.toggle('is-locked', open);
    }

    burger.addEventListener('click', function () {
      setOpen(!drawer.classList.contains('is-open'));
    });

    drawer.addEventListener('click', function (e) {
      if (e.target.closest('a')) setOpen(false);
    });

    doc.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
        setOpen(false);
        burger.focus();
      }
    });

    // Reset when resizing back up to the desktop nav.
    var mq = window.matchMedia('(min-width: 1081px)');
    (mq.addEventListener ? mq.addEventListener.bind(mq, 'change') : mq.addListener.bind(mq))(
      function (e) { if (e.matches) setOpen(false); }
    );
  }

  /* ------------------------------------------------------- scroll reveal */
  function initReveal() {
    var items = doc.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) {
      Array.prototype.forEach.call(items, function (el) { el.classList.add('is-visible'); });
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        io.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });

    Array.prototype.forEach.call(items, function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------ count-up stats */
  function initCounters() {
    var items = doc.querySelectorAll('[data-count]');
    if (!items.length) return;

    function run(el) {
      var target = parseFloat(el.getAttribute('data-count'));
      if (isNaN(target)) return;
      var decimals = (el.getAttribute('data-count').split('.')[1] || '').length;
      var fmt = function (n) {
        return decimals ? n.toFixed(decimals)
                        : Math.round(n).toLocaleString('ja-JP');
      };
      if (reduceMotion) { el.textContent = fmt(target); return; }

      var start = null;
      var dur = 1400;
      function step(ts) {
        if (start === null) start = ts;
        var p = Math.min((ts - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = fmt(target * eased);
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    }

    if (!('IntersectionObserver' in window)) {
      Array.prototype.forEach.call(items, run);
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        run(entry.target);
        io.unobserve(entry.target);
      });
    }, { threshold: 0.6 });
    Array.prototype.forEach.call(items, function (el) { io.observe(el); });
  }

  /* ------------------------------------------------------------ accordion */
  function initFaq() {
    var buttons = doc.querySelectorAll('[data-faq-q]');
    Array.prototype.forEach.call(buttons, function (btn) {
      btn.addEventListener('click', function () {
        var panel = doc.getElementById(btn.getAttribute('aria-controls'));
        if (!panel) return;
        var open = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!open));
        panel.classList.toggle('is-open', !open);
        panel.hidden = open;
      });
    });
  }

  /* --------------------------------------------------------- news filter */
  function initTabs() {
    var groups = doc.querySelectorAll('[data-tabs]');
    Array.prototype.forEach.call(groups, function (group) {
      var targetSel = group.getAttribute('data-tabs');
      var list = doc.querySelector(targetSel);
      if (!list) return;

      group.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-filter]');
        if (!tab) return;
        var key = tab.getAttribute('data-filter');

        Array.prototype.forEach.call(group.querySelectorAll('[data-filter]'), function (t) {
          var on = t === tab;
          t.classList.toggle('is-active', on);
          t.setAttribute('aria-selected', String(on));
        });

        var shown = 0;
        Array.prototype.forEach.call(list.children, function (item) {
          var match = key === 'all' || item.getAttribute('data-cat') === key;
          item.hidden = !match;
          if (match) shown++;
        });

        var empty = doc.querySelector('[data-tabs-empty]');
        if (empty) empty.hidden = shown > 0;
      });
    });
  }

  /* ------------------------------------------------ floating CTA / to-top */
  function initFloating() {
    var bar = doc.querySelector('[data-fixedbar]');
    var top = doc.querySelector('[data-totop]');
    if (!bar && !top) return;

    var footer = doc.querySelector('[data-footer]');
    function onScroll() {
      var past = window.scrollY > 480;
      // Hide the bar once the real footer CTA is on screen.
      var atFoot = footer && footer.getBoundingClientRect().top < window.innerHeight - 40;
      if (bar) bar.classList.toggle('is-shown', past && !atFoot);
      if (top) top.classList.toggle('is-shown', past);
    }
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    if (top) {
      top.addEventListener('click', function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
      });
    }
  }

  /* ------------------------------------------------- contact form (demo) */
  function initForm() {
    var form = doc.querySelector('[data-demo-form]');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var note = doc.querySelector('[data-demo-note]');
      if (note) {
        note.hidden = false;
        note.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
      }
    });
  }

  function init() {
    initHeader();
    initDrawer();
    initReveal();
    initCounters();
    initFaq();
    initTabs();
    initFloating();
    initForm();
  }

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
