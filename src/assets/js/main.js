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
      var toggle = e.target.closest('[data-drawer-toggle]');
      if (toggle) {
        var panel = doc.getElementById(toggle.getAttribute('aria-controls'));
        if (!panel) return;
        var expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        panel.hidden = expanded;
        // the panel animates via grid-template-rows, so it must be visible
        // before the class lands or the first frame is skipped
        if (!expanded) {
          requestAnimationFrame(function () { panel.classList.add('is-open'); });
        } else {
          panel.classList.remove('is-open');
        }
        return;
      }
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
  /* GSAP drives the motion when it is available; the IntersectionObserver
     path below stays as the fallback so the site still reveals without it. */

  function hasGsap() {
    return !!(window.gsap && window.ScrollTrigger) && !reduceMotion;
  }

  function revealFallback(items) {
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

  function initReveal() {
    var items = doc.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    if (!hasGsap()) {
      revealFallback(items);
      return;
    }

    var gsap = window.gsap;
    gsap.registerPlugin(window.ScrollTrigger);
    doc.documentElement.classList.add('ef-gsap');

    // The CSS transition would race GSAP's inline styles, so hand over cleanly.
    Array.prototype.forEach.call(items, function (el) { el.style.transition = 'none'; });

    var handled = [];

    // Grids animate as one stagger rather than card-by-card on their own
    // triggers — it reads as a deliberate sequence instead of a scatter.
    gsap.utils.toArray('.ef-grid, .ef-steps, .ef-contactways').forEach(function (group) {
      var kids = group.querySelectorAll('[data-reveal]');
      if (!kids.length) return;
      Array.prototype.push.apply(handled, kids);
      gsap.to(kids, {
        opacity: 1, y: 0, duration: 0.85, ease: 'power2.out', stagger: 0.1,
        scrollTrigger: { trigger: group, start: 'top 85%', once: true }
      });
    });

    Array.prototype.forEach.call(items, function (el) {
      if (handled.indexOf(el) !== -1) return;
      gsap.to(el, {
        opacity: 1, y: 0, duration: 0.9, ease: 'power2.out',
        delay: (parseFloat(el.getAttribute('data-reveal-delay')) || 0) * 0.08,
        scrollTrigger: { trigger: el, start: 'top 88%', once: true }
      });
    });
  }

  /* --------------------------------------------------------- hero motion */
  function initHeroMotion() {
    var hero = doc.querySelector('.ef-hero');
    if (!hero || !hasGsap()) return;

    var gsap = window.gsap;

    var tl = gsap.timeline({ defaults: { ease: 'power3.out', duration: 0.9 } });

    tl.from('.ef-hero__tag', { y: 18, opacity: 0, duration: 0.6 })
      .from('.ef-hero__title', { y: 32, opacity: 0 }, '-=0.30')
      // the amber underline sweeps in behind the headline
      .fromTo('.ef-hero__title .ef-mark',
        { '--ef-mark': 0 },
        { '--ef-mark': 1, duration: 0.7, ease: 'power2.inOut' }, '-=0.45')
      .from('.ef-hero__text', { y: 20, opacity: 0, duration: 0.7 }, '-=0.60')
      .from('.ef-hero__cta > *', { y: 18, opacity: 0, stagger: 0.1, duration: 0.6 }, '-=0.45')
      .from('.ef-hero__shot', {
        y: 48, opacity: 0, scale: 0.94, stagger: 0.12, duration: 1.1, clearProps: 'scale'
      }, '-=1.05')
      .from('.ef-hero__stat', { y: 16, opacity: 0, stagger: 0.08, duration: 0.6 }, '-=0.65');

    // Depth on scroll — desktop only, where the tiles are absolutely placed.
    window.ScrollTrigger.matchMedia({
      '(min-width: 1001px)': function () {
        [['.ef-hero__shot--a', -13], ['.ef-hero__shot--b', 6], ['.ef-hero__shot--c', -22]]
          .forEach(function (pair) {
            gsap.to(pair[0], {
              yPercent: pair[1], ease: 'none',
              scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.6 }
            });
          });
        gsap.to('.ef-hero__media img', {
          yPercent: 8, ease: 'none',
          scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: 0.6 }
        });
      }
    });
  }

  /* ---------------------------------------------------- sub-page hero fade */
  function initPageHeroMotion() {
    var hero = doc.querySelector('.ef-phero');
    if (!hero || !hasGsap()) return;
    var gsap = window.gsap;
    gsap.timeline({ defaults: { ease: 'power3.out' } })
      .from(hero.querySelectorAll('.ef-phero__en, .ef-phero__title, .ef-phero__text'),
        { y: 24, opacity: 0, duration: 0.8, stagger: 0.12 })
      .from(hero.querySelectorAll('.ef-phero__media img'),
        { scale: 1.08, duration: 1.6, ease: 'power2.out' }, 0);
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
    var top = doc.querySelector('[data-totop]');
    if (!top) return;

    function onScroll() {
      top.classList.toggle('is-shown', window.scrollY > 480);
    }
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    {
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
    initHeroMotion();
    initPageHeroMotion();
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
