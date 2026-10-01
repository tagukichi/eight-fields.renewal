/**
 * PDF viewer.
 *
 * A browser's own PDF plugin is a desktop thing. On a phone an embedded PDF
 * either shows its first page and refuses to scroll (iOS) or is replaced by a
 * download prompt (most Android browsers), which is why the embed used to be
 * dropped below the desktop breakpoint — a working button beats a dead grey
 * rectangle.
 *
 * Drawing the pages ourselves removes the question: every page is rendered to
 * a canvas, so the file reads the same on a phone as on a desktop, at whatever
 * width the layout gives it.
 *
 * If anything here fails — the library will not load, the file is not a PDF,
 * the network drops — the block falls back to the browser's own embed and the
 * buttons underneath, so the page is never left with nothing.
 */
(function () {
  'use strict';

  var blocks = document.querySelectorAll('[data-ef-pdf]');
  if (!blocks.length || !window.EF_PDFJS) {
    return;
  }

  // Written this way so a browser too old for dynamic import fails at run time,
  // where it can be caught, rather than at parse time.
  var load;
  try {
    load = new Function('url', 'return import(url);');
  } catch (err) {
    return;
  }

  var library = null;

  // A dynamic import resolves a bare path as a module specifier, not as a URL,
  // so every configured path is made absolute first.
  function absolute(path) {
    try {
      return new URL(path, document.baseURI).href;
    } catch (err) {
      return path;
    }
  }

  function pdfjs() {
    if (!library) {
      library = load(absolute(window.EF_PDFJS.lib)).then(function (mod) {
        mod.GlobalWorkerOptions.workerSrc = absolute(window.EF_PDFJS.worker);
        return mod;
      });
    }
    return library;
  }

  /**
   * Fall back to the browser's own embed.
   *
   * The markup carries no <object> of its own — one there would download the
   * file a second time — so it is built here, only when it is needed.
   */
  function fallback(block) {
    block.classList.remove('is-ready', 'is-loading');
    block.classList.add('is-fallback');

    var pages = block.querySelector('[data-ef-pdf-pages]');
    if (!pages || pages.querySelector('object')) {
      return;
    }

    var url = block.getAttribute('data-ef-pdf');
    var frame = document.createElement('object');
    frame.setAttribute('data', url + '#view=FitH');
    frame.setAttribute('type', 'application/pdf');
    frame.className = 'ef-pdf__object';

    pages.textContent = '';
    pages.appendChild(frame);
  }

  function renderPage(page, pages, width) {
    var base = page.getViewport({ scale: 1 });
    var scale = width / base.width;
    // Twice the CSS pixels keeps text crisp on a phone without asking a
    // mid-range one to paint a 4x canvas.
    var ratio = Math.min(window.devicePixelRatio || 1, 2);
    var view = page.getViewport({ scale: scale * ratio });

    var canvas = document.createElement('canvas');
    canvas.className = 'ef-pdf__page';
    canvas.width = Math.floor(view.width);
    canvas.height = Math.floor(view.height);
    canvas.style.width = '100%';
    canvas.style.aspectRatio = base.width + ' / ' + base.height;
    pages.appendChild(canvas);

    return page.render({
      canvasContext: canvas.getContext('2d', { alpha: false }),
      viewport: view
    }).promise;
  }

  function render(block) {
    var pages = block.querySelector('[data-ef-pdf-pages]');
    var url = block.getAttribute('data-ef-pdf');
    if (!pages || !url) {
      return;
    }

    var width = Math.round(pages.clientWidth);
    if (width < 40) {
      return;
    }

    block.classList.add('is-loading');

    pdfjs()
      .then(function (mod) {
        return mod.getDocument({
          url: absolute(url),
          cMapUrl: absolute(window.EF_PDFJS.cmaps),
          cMapPacked: true,
          standardFontDataUrl: absolute(window.EF_PDFJS.fonts)
        }).promise;
      })
      .then(function (doc) {
        pages.textContent = '';

        var chain = Promise.resolve();
        for (var n = 1; n <= doc.numPages; n++) {
          chain = chain.then(
            (function (number) {
              return function () {
                return doc.getPage(number).then(function (page) {
                  return renderPage(page, pages, width);
                });
              };
            })(n)
          );
        }

        return chain.then(function () {
          block.dataset.efPdfWidth = String(width);
          block.dataset.efPdfPages = String(doc.numPages);
          block.classList.remove('is-loading');
          block.classList.add('is-ready');
        });
      })
      .catch(function () {
        fallback(block);
      });
  }

  Array.prototype.forEach.call(blocks, function (block) {
    block.classList.add('ef-pdf--js');
    render(block);
  });

  // Re-drawing on every resize step would be wasteful; a canvas stretches well
  // enough in the meantime, and only a real change of width needs new pixels.
  var timer = null;
  window.addEventListener('resize', function () {
    window.clearTimeout(timer);
    timer = window.setTimeout(function () {
      Array.prototype.forEach.call(blocks, function (block) {
        if (!block.classList.contains('is-ready')) {
          return;
        }
        var pages = block.querySelector('[data-ef-pdf-pages]');
        var was = parseInt(block.dataset.efPdfWidth || '0', 10);
        if (pages && Math.abs(pages.clientWidth - was) > 60) {
          render(block);
        }
      });
    }, 250);
  });
})();
