/* StayNest - AJAX filtering, interest toggle, toasts */
(function () {
  'use strict';
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var LOGGED_IN = document.body.dataset.loggedIn === '1';

  function esc(s) {
    var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML;
  }
  function money(n) { return '\u20B9' + Number(n).toLocaleString('en-IN'); }
  var GENDER = { male: 'Boys', female: 'Girls', unisex: 'Unisex' };

  function toast(msg, type) {
    var area = document.getElementById('toast-area'); if (!area) return;
    var el = document.createElement('div');
    el.className = 'alert alert-' + (type || 'dark') + ' shadow py-2 px-3 mb-2';
    el.setAttribute('role', 'status'); el.textContent = msg;
    area.appendChild(el);
    setTimeout(function () { el.remove(); }, 2800);
  }
  window.StayNest = { toast: toast, esc: esc, money: money, GENDER: GENDER, csrf: CSRF };

  // POST toggle; resolves {interested:Boolean} or rejects with {status,message}
  function toggleInterest(id) {
    return fetch('api/toggle_interest.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': CSRF },
      body: 'property_id=' + encodeURIComponent(id)
    }).then(function (r) {
      return r.json().then(function (d) {
        if (!r.ok) throw { status: r.status, message: d.error || 'Something went wrong.' };
        return d;
      });
    });
  }
  window.StayNest.toggleInterest = toggleInterest;

  function handleAuthFail(err) {
    if (err && err.status === 401) {
      toast('Log in to shortlist PGs.', 'warning');
      setTimeout(function () {
        location.href = 'login.php?next=' + encodeURIComponent(location.pathname.split('/').pop() + location.search);
      }, 900);
    } else {
      toast((err && err.message) || 'Network error. Try again.', 'danger');
    }
  }

  /* ---------- Listing page ---------- */
  var listing = document.getElementById('listing');
  if (listing) {
    var form = document.getElementById('filters');
    var spinner = document.getElementById('spinner');
    var count = document.getElementById('result-count');
    var priceInput = document.getElementById('f-price');
    var priceLabel = document.getElementById('price-label');
    var maxPrice = priceInput.max;
    var timer = null, reqId = 0;

    function card(p) {
      var on = p.interested;
      return '<div class="col-12 col-sm-6 col-lg-4"><article class="pg-card">' +
        '<div class="pg-media"><img src="' + esc(p.image) + '" alt="' + esc(p.name) + '" loading="lazy">' +
        '<span class="badge sn-badge sn-badge-' + esc(p.gender) + '">' + esc(GENDER[p.gender]) + '</span>' +
        '<button type="button" class="heart-btn" data-id="' + p.id + '" aria-pressed="' + on + '" ' +
        'aria-label="' + (on ? 'Remove from shortlist' : 'Add to shortlist') + '">' +
        '<i class="bi ' + (on ? 'bi-heart-fill' : 'bi-heart') + '"></i></button></div>' +
        '<div class="pg-body"><h3><a href="property.php?id=' + p.id + '">' + esc(p.name) + '</a></h3>' +
        '<div class="pg-loc"><i class="bi bi-geo-alt"></i> ' + esc(p.area) + ', ' + esc(p.city) + '</div>' +
        '<div class="pg-foot"><span class="sn-price">' + money(p.price) + '<small>/month</small></span>' +
        '<span class="sn-rating"><i class="bi bi-star-fill"></i> ' + p.rating.toFixed(1) + '</span></div></div>' +
        '</article></div>';
    }

    function load() {
      var my = ++reqId;
      spinner.classList.remove('d-none');
      listing.setAttribute('aria-busy', 'true');
      listing.style.opacity = '.5';
      var params = new URLSearchParams(new FormData(form));
      if (params.get('max_price') === maxPrice) params.delete('max_price'); // no limit
      fetch('api/properties.php?' + params.toString())
        .then(function (r) { if (!r.ok) throw new Error('bad'); return r.json(); })
        .then(function (d) {
          if (my !== reqId) return; // ignore stale responses
          var list = d.properties;
          count.textContent = list.length + (list.length === 1 ? ' PG found' : ' PGs found');
          listing.innerHTML = list.length
            ? list.map(card).join('')
            : '<div class="col-12"><div class="sn-empty"><h3 class="h5">No PGs match these filters</h3>' +
              '<p class="text-muted mb-3">Try a higher budget or a different city.</p>' +
              '<button class="btn sn-btn" id="empty-reset" type="button">Clear filters</button></div></div>';
        })
        .catch(function () {
          if (my !== reqId) return;
          count.textContent = 'Could not load PGs';
          listing.innerHTML = '<div class="col-12"><div class="alert alert-danger">Could not load listings. Check your connection and change a filter to retry.</div></div>';
        })
        .finally(function () {
          if (my !== reqId) return;
          spinner.classList.add('d-none'); listing.style.opacity = ''; listing.removeAttribute('aria-busy');
        });
    }

    function schedule() { clearTimeout(timer); timer = setTimeout(load, 200); }
    form.addEventListener('change', schedule);
    priceInput.addEventListener('input', function () { priceLabel.textContent = money(priceInput.value); schedule(); });
    form.addEventListener('reset', function () {
      setTimeout(function () { priceLabel.textContent = money(priceInput.value); load(); }, 0);
    });
    form.addEventListener('submit', function (e) { e.preventDefault(); });

    listing.addEventListener('click', function (e) {
      if (e.target.id === 'empty-reset') { form.reset(); return; }
      var btn = e.target.closest('.heart-btn'); if (!btn) return;
      e.preventDefault();
      if (!LOGGED_IN) { handleAuthFail({ status: 401 }); return; }
      btn.disabled = true;
      toggleInterest(btn.dataset.id).then(function (d) {
        btn.setAttribute('aria-pressed', d.interested);
        btn.setAttribute('aria-label', d.interested ? 'Remove from shortlist' : 'Add to shortlist');
        btn.firstElementChild.className = 'bi ' + (d.interested ? 'bi-heart-fill' : 'bi-heart');
        btn.classList.remove('pop'); void btn.offsetWidth; btn.classList.add('pop');
        toast(d.interested ? 'Added to your shortlist' : 'Removed from your shortlist', 'success');
      }).catch(handleAuthFail).finally(function () { btn.disabled = false; });
    });

    priceLabel.textContent = money(priceInput.value);
    load();
  }

  /* ---------- Detail page button ---------- */
  var detailBtn = document.getElementById('interest-btn');
  if (detailBtn) {
    detailBtn.addEventListener('click', function () {
      if (!LOGGED_IN) { handleAuthFail({ status: 401 }); return; }
      detailBtn.disabled = true;
      toggleInterest(detailBtn.dataset.id).then(function (d) {
        detailBtn.classList.toggle('is-on', d.interested);
        detailBtn.setAttribute('aria-pressed', d.interested);
        detailBtn.querySelector('i').className = 'bi ' + (d.interested ? 'bi-heart-fill' : 'bi-heart');
        detailBtn.querySelector('span').textContent = d.interested ? 'Shortlisted' : 'Shortlist this PG';
        toast(d.interested ? 'Added to your shortlist' : 'Removed from your shortlist', 'success');
      }).catch(handleAuthFail).finally(function () { detailBtn.disabled = false; });
    });
  }
})();
