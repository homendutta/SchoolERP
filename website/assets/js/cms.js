/*
 * cms.js — ERP/CMS integration for the existing static website.
 *
 * Progressive enhancement only: it fetches PUBLISHED content from the read-only
 * /cms/public/* API and hydrates any element carrying a `data-cms` attribute, and
 * wires any `form[data-cms-form]` to submit into the ERP. If the API is
 * unreachable, the existing static markup is left untouched — the site never
 * breaks. No framework, no dependencies; the Bootstrap design is preserved.
 *
 *   <div data-cms="notices"></div>         → notice board list
 *   <div data-cms="news"></div>            → news cards
 *   <div data-cms="events"></div>          → event cards
 *   <div data-cms="gallery"></div>         → photo albums (lightbox-friendly)
 *   <div data-cms="videos"></div>          → responsive video embeds
 *   <div data-cms="downloads"></div>       → downloads list
 *   <div data-cms="staff"></div>           → staff directory
 *   <ul  data-cms="notice-ticker"></ul>    → homepage headline ticker
 *   <form data-cms-form="contact">         → contact submission
 *   <form data-cms-form="enquiry">         → admission enquiry
 */
(function () {
  'use strict';

  var CFG = window.CMS_CONFIG || { baseUrl: '/api/v1', schoolId: 1 };

  function api(path) {
    var sep = path.indexOf('?') > -1 ? '&' : '?';
    return fetch(CFG.baseUrl + '/cms/public' + path + sep + 'school_id=' + CFG.schoolId, {
      headers: { Accept: 'application/json' },
    })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (j) {
        return j && j.data !== undefined ? j.data : j;
      });
  }

  function post(path, body) {
    return fetch(CFG.baseUrl + '/cms/public' + path, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    }).then(function (r) {
      return r.json().then(function (j) {
        return { ok: r.ok, body: j };
      });
    });
  }

  function esc(s) {
    if (s === null || s === undefined) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function fmtDate(d) {
    if (!d) return '';
    var dt = new Date(d);
    if (isNaN(dt.getTime())) return esc(d);
    return dt.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
  }

  function setBusy(node, busy) {
    node.setAttribute('aria-busy', busy ? 'true' : 'false');
  }

  function empty(node, message) {
    node.innerHTML = '<p class="text-muted small mb-0">' + esc(message) + '</p>';
  }

  /* -------------------------------- hydrators -------------------------------- */
  var HYDRATORS = {
    notices: function (node) {
      setBusy(node, true);
      api('/notices')
        .then(function (items) {
          setBusy(node, false);
          if (!items || !items.length) return empty(node, 'No current notices.');
          node.innerHTML =
            '<ul class="list-group cms-notice-list">' +
            items
              .map(function (n) {
                var badge = n.featured
                  ? '<span class="badge bg-danger ms-2">Featured</span>'
                  : '';
                var file = n.attachment
                  ? ' <a href="' +
                    esc(n.attachment) +
                    '" class="ms-2" target="_blank" rel="noopener">Attachment</a>'
                  : '';
                return (
                  '<li class="list-group-item d-flex justify-content-between align-items-start flex-wrap">' +
                  '<div><span class="fw-semibold">' +
                  esc(n.title) +
                  '</span>' +
                  badge +
                  file +
                  '</div>' +
                  '<small class="text-muted">' +
                  fmtDate(n.publish_date) +
                  '</small></li>'
                );
              })
              .join('') +
            '</ul>';
        })
        .catch(function () {
          setBusy(node, false);
        });
    },

    'notice-ticker': function (node) {
      // Replaces the homepage headline ticker items, preserving the existing
      // `.ticker-item` markup + animation. Falls back to the static items on error.
      api('/notices?limit=8')
        .then(function (items) {
          if (!items || !items.length) return;
          node.innerHTML = items
            .map(function (n) {
              return (
                '<div class="ticker-item">' +
                '<span class="news-badge bg-danger text-white">NOTICE</span>' +
                '<span class="news-date"><i class="far fa-calendar"></i> ' +
                fmtDate(n.publish_date) +
                '</span>' +
                '<a href="/pages/noticeList">' +
                esc(n.title) +
                '</a></div>'
              );
            })
            .join('');
        })
        .catch(function () {});
    },

    news: function (node) {
      setBusy(node, true);
      api('/news')
        .then(function (items) {
          setBusy(node, false);
          if (!items || !items.length) return empty(node, 'No news yet.');
          node.className = (node.className + ' row g-4').trim();
          node.innerHTML = items
            .map(function (n) {
              var img = n.image
                ? '<img src="' +
                  esc(n.image) +
                  '" class="card-img-top cms-card-img" alt="' +
                  esc(n.title) +
                  '" loading="lazy">'
                : '';
              return (
                '<div class="col-12 col-sm-6 col-lg-4"><article class="card h-100 shadow-sm cms-card">' +
                img +
                '<div class="card-body"><h3 class="h6 card-title">' +
                esc(n.title) +
                '</h3>' +
                '<p class="small text-muted mb-2">' +
                fmtDate(n.publish_date) +
                '</p>' +
                '<p class="card-text small">' +
                esc(n.excerpt || '') +
                '</p></div></article></div>'
              );
            })
            .join('');
        })
        .catch(function () {
          setBusy(node, false);
        });
    },

    events: function (node) {
      setBusy(node, true);
      api('/events')
        .then(function (items) {
          setBusy(node, false);
          if (!items || !items.length) return empty(node, 'No upcoming events.');
          node.className = (node.className + ' row g-4').trim();
          node.innerHTML = items
            .map(function (e) {
              var img = e.image
                ? '<img src="' +
                  esc(e.image) +
                  '" class="card-img-top cms-card-img" alt="' +
                  esc(e.title) +
                  '" loading="lazy">'
                : '';
              var when = fmtDate(e.event_date) + (e.start_time ? ' · ' + esc(e.start_time) : '');
              return (
                '<div class="col-12 col-sm-6 col-lg-4"><article class="card h-100 shadow-sm cms-card">' +
                img +
                '<div class="card-body"><h3 class="h6 card-title">' +
                esc(e.title) +
                '</h3>' +
                '<p class="small text-muted mb-1"><i class="fa-regular fa-calendar me-1"></i>' +
                when +
                '</p>' +
                (e.venue
                  ? '<p class="small text-muted mb-2"><i class="fa-solid fa-location-dot me-1"></i>' +
                    esc(e.venue) +
                    '</p>'
                  : '') +
                '<p class="card-text small">' +
                esc(e.description || '') +
                '</p></div></article></div>'
              );
            })
            .join('');
        })
        .catch(function () {
          setBusy(node, false);
        });
    },

    gallery: function (node) {
      setBusy(node, true);
      api('/gallery')
        .then(function (albums) {
          setBusy(node, false);
          if (!albums || !albums.length) return empty(node, 'No photos yet.');
          node.innerHTML = albums
            .map(function (a) {
              var imgs = (a.images || [])
                .map(function (img) {
                  return (
                    '<a href="' +
                    esc(img.url) +
                    '" class="col-6 col-md-4 col-lg-3 cms-gallery-cell" target="_blank" rel="noopener">' +
                    '<img src="' +
                    esc(img.url) +
                    '" class="img-fluid rounded" alt="' +
                    esc(img.caption || a.title) +
                    '" loading="lazy"></a>'
                  );
                })
                .join('');
              return (
                '<section class="cms-album mb-4"><h3 class="h5">' +
                esc(a.title) +
                '</h3>' +
                (a.description ? '<p class="small text-muted">' + esc(a.description) + '</p>' : '') +
                '<div class="row g-2">' +
                imgs +
                '</div></section>'
              );
            })
            .join('');
        })
        .catch(function () {
          setBusy(node, false);
        });
    },

    videos: function (node) {
      setBusy(node, true);
      api('/videos')
        .then(function (items) {
          setBusy(node, false);
          if (!items || !items.length) return empty(node, 'No videos yet.');
          node.className = (node.className + ' row g-4').trim();
          node.innerHTML = items
            .map(function (v) {
              var embed = toEmbed(v);
              return (
                '<div class="col-12 col-md-6 col-lg-4"><div class="cms-video card shadow-sm">' +
                '<div class="ratio ratio-16x9">' +
                embed +
                '</div>' +
                '<div class="card-body py-2"><h3 class="h6 mb-0">' +
                esc(v.title) +
                '</h3></div></div></div>'
              );
            })
            .join('');
        })
        .catch(function () {
          setBusy(node, false);
        });
    },

    downloads: function (node) {
      setBusy(node, true);
      api('/downloads')
        .then(function (items) {
          setBusy(node, false);
          if (!items || !items.length) return empty(node, 'No downloads yet.');
          node.innerHTML =
            '<ul class="list-group cms-downloads">' +
            items
              .map(function (d) {
                var link = d.file
                  ? '<a href="' +
                    esc(d.file) +
                    '" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener"><i class="fa-solid fa-download me-1"></i>Download</a>'
                  : '';
                return (
                  '<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">' +
                  '<span><span class="fw-semibold">' +
                  esc(d.title) +
                  '</span>' +
                  (d.category
                    ? ' <span class="badge bg-light text-dark">' + esc(d.category) + '</span>'
                    : '') +
                  '</span>' +
                  link +
                  '</li>'
                );
              })
              .join('') +
            '</ul>';
        })
        .catch(function () {
          setBusy(node, false);
        });
    },

    staff: function (node) {
      setBusy(node, true);
      api('/staff')
        .then(function (items) {
          setBusy(node, false);
          if (!items || !items.length) return empty(node, 'Directory coming soon.');
          node.className = (node.className + ' row g-4').trim();
          node.innerHTML = items
            .map(function (s) {
              var photo = s.photo
                ? '<img src="' +
                  esc(s.photo) +
                  '" class="rounded-circle cms-staff-photo mb-2" alt="' +
                  esc(s.name) +
                  '" loading="lazy">'
                : '<div class="cms-staff-photo cms-staff-placeholder mb-2" aria-hidden="true"><i class="fa-solid fa-user"></i></div>';
              return (
                '<div class="col-6 col-md-4 col-lg-3 text-center"><div class="card h-100 border-0 shadow-sm p-3">' +
                photo +
                '<h3 class="h6 mb-0">' +
                esc(s.name) +
                '</h3>' +
                '<p class="small text-muted mb-0">' +
                esc(s.designation || '') +
                '</p>' +
                (s.department
                  ? '<p class="small text-muted mb-0">' + esc(s.department) + '</p>'
                  : '') +
                '</div></div>'
              );
            })
            .join('');
        })
        .catch(function () {
          setBusy(node, false);
        });
    },
  };

  function toEmbed(v) {
    var url = v.video_url || '';
    var yt = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]{11})/);
    if (v.provider === 'youtube' && yt) {
      return (
        '<iframe src="https://www.youtube.com/embed/' +
        yt[1] +
        '" title="' +
        esc(v.title) +
        '" allowfullscreen loading="lazy"></iframe>'
      );
    }
    var vm = url.match(/vimeo\.com\/(\d+)/);
    if (v.provider === 'vimeo' && vm) {
      return (
        '<iframe src="https://player.vimeo.com/video/' +
        vm[1] +
        '" title="' +
        esc(v.title) +
        '" allowfullscreen loading="lazy"></iframe>'
      );
    }
    if (v.file) {
      return '<video src="' + esc(v.file) + '" controls preload="none"></video>';
    }
    return (
      '<a class="d-flex align-items-center justify-content-center h-100" href="' +
      esc(url) +
      '" target="_blank" rel="noopener">Watch video</a>'
    );
  }

  /* -------------------------------- forms -------------------------------- */
  function bindForm(form) {
    var kind = form.getAttribute('data-cms-form'); // contact | enquiry
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var status = form.querySelector('[data-cms-status]');
      var data = {};
      Array.prototype.forEach.call(form.elements, function (el) {
        if (el.name) data[el.name] = el.value;
      });
      data.school_id = CFG.schoolId;
      var endpoint = kind === 'enquiry' ? '/enquiries' : '/forms';
      if (kind !== 'enquiry' && !data.type) data.type = 'contact';

      if (status) {
        status.textContent = 'Sending…';
        status.className = 'cms-form-status text-muted small mt-2';
      }
      post(endpoint, data)
        .then(function (res) {
          if (!status) return;
          if (res.ok) {
            form.reset();
            status.textContent = (res.body && res.body.message) || 'Thank you — received.';
            status.className = 'cms-form-status text-success small mt-2';
          } else {
            status.textContent = (res.body && res.body.message) || 'Please check the form and retry.';
            status.className = 'cms-form-status text-danger small mt-2';
          }
        })
        .catch(function () {
          if (status) {
            status.textContent = 'Network error. Please try again later.';
            status.className = 'cms-form-status text-danger small mt-2';
          }
        });
    });
  }

  /* -------------------------------- boot -------------------------------- */
  function boot() {
    document.querySelectorAll('[data-cms]').forEach(function (node) {
      var h = HYDRATORS[node.getAttribute('data-cms')];
      if (h) h(node);
    });
    document.querySelectorAll('form[data-cms-form]').forEach(bindForm);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
