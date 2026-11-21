/* React component: Shortlist page (no build step - uses React UMD + createElement) */
(function () {
  'use strict';
  var h = React.createElement;
  var useState = React.useState, useEffect = React.useEffect;
  var SN = window.StayNest;

  function PropertyCard(props) {
    var p = props.property;
    return h('div', { className: 'col-12 col-sm-6 col-lg-4' },
      h('article', { className: 'pg-card' },
        h('div', { className: 'pg-media' },
          h('img', { src: p.image, alt: p.name, loading: 'lazy' }),
          h('span', { className: 'badge sn-badge sn-badge-' + p.gender }, SN.GENDER[p.gender]),
          h('button', {
            type: 'button', className: 'heart-btn', disabled: props.busy,
            'aria-label': 'Remove ' + p.name + ' from shortlist',
            onClick: function () { props.onRemove(p.id); }
          }, h('i', { className: 'bi bi-heart-fill' }))
        ),
        h('div', { className: 'pg-body' },
          h('h3', null, h('a', { href: 'property.php?id=' + p.id }, p.name)),
          h('div', { className: 'pg-loc' }, h('i', { className: 'bi bi-geo-alt' }), ' ' + p.area + ', ' + p.city),
          h('div', { className: 'pg-foot' },
            h('span', { className: 'sn-price' }, SN.money(p.price), h('small', null, '/month')),
            h('span', { className: 'sn-rating' }, h('i', { className: 'bi bi-star-fill' }), ' ' + p.rating.toFixed(1))
          )
        )
      )
    );
  }

  function Shortlist() {
    var s = useState(null), items = s[0], setItems = s[1];
    var e = useState(''), error = e[0], setError = e[1];
    var b = useState(0), busyId = b[0], setBusyId = b[1];

    useEffect(function () {
      fetch('api/shortlist.php')
        .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
        .then(function (d) { setItems(d.properties); })
        .catch(function () { setError('Could not load your shortlist. Refresh to try again.'); });
    }, []);

    function remove(id) {
      setBusyId(id);
      SN.toggleInterest(id).then(function () {
        setItems(function (cur) { return cur.filter(function (p) { return p.id !== id; }); });
        SN.toast('Removed from your shortlist', 'success');
      }).catch(function (err) {
        SN.toast((err && err.message) || 'Network error. Try again.', 'danger');
      }).finally(function () { setBusyId(0); });
    }

    if (error) return h('div', { className: 'alert alert-danger' }, error);
    if (items === null) return h('div', { className: 'spinner-border text-secondary', role: 'status' });
    if (items.length === 0) {
      return h('div', { className: 'sn-empty' },
        h('h2', { className: 'h5' }, 'Your shortlist is empty'),
        h('p', { className: 'text-muted' }, 'Tap the heart on any PG to save it here.'),
        h('a', { className: 'btn sn-btn', href: 'index.php' }, 'Browse PGs'));
    }
    return h('div', null,
      h('p', { className: 'text-muted', 'aria-live': 'polite' }, items.length + (items.length === 1 ? ' PG saved' : ' PGs saved')),
      h('div', { className: 'row g-4' }, items.map(function (p) {
        return h(PropertyCard, { key: p.id, property: p, busy: busyId === p.id, onRemove: remove });
      })));
  }

  ReactDOM.createRoot(document.getElementById('shortlist-root')).render(h(Shortlist));
})();
