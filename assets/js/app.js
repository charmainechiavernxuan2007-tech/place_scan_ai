(function () {
  var $ = function (s) { return document.querySelector(s); };
  var csrf = ($('meta[name=csrf]') || {}).content || '';
  function api(url, opts) {
    opts = opts || {}; opts.headers = Object.assign({ 'X-CSRF-Token': csrf }, opts.headers || {});
    return fetch(url, opts).then(function (r) { return r.json(); }).catch(function () { return { status: 'error', message: t('err_network') }; });
  }
  function msg(el, text, ok) { el.textContent = text; el.className = 'msg ' + (ok ? 'ok' : 'bad'); el.hidden = !text; }

  /* ---------- Scanner + registration (index.php) ---------- */
  if ($('#video')) {
    var video = $('#video'), canvas = $('#canvas'), ctx = canvas.getContext('2d'), stream = null, ratesData = null;
    var scanMsg = $('#scanMsg'), regMsg = $('#regMsg');

    $('#startCam').onclick = function () {
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return msg(scanMsg, t('err_cam'));
      navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 } }, audio: false })
        .then(function (s) { stream = s; video.srcObject = s; video.hidden = false; $('#guide').hidden = false; $('#capture').disabled = false; msg(scanMsg, '', 1); })
        .catch(function () { msg(scanMsg, t('err_cam')); });
    };
    $('#capture').onclick = function () { draw(video, video.videoWidth, video.videoHeight); };

    var drop = $('#drop'), fileIn = $('#file');
    drop.onclick = function () { fileIn.click(); };
    ['dragover', 'dragenter'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.remove('over'); }); });
    drop.addEventListener('drop', function (e) { e.preventDefault(); if (e.dataTransfer.files[0]) load(e.dataTransfer.files[0]); });
    fileIn.onchange = function () { if (fileIn.files[0]) load(fileIn.files[0]); };

    function load(f) {
      if (!/^image\/(jpeg|png|webp)$/.test(f.type)) return msg(scanMsg, 'JPG / PNG / WebP only.');
      var img = new Image(); img.onload = function () { draw(img, img.naturalWidth, img.naturalHeight); URL.revokeObjectURL(img.src); }; img.src = URL.createObjectURL(f);
    }
    function draw(src, w, h) {
      if (!w) return;
      var k = Math.min(1, 1280 / w); canvas.width = Math.round(w * k); canvas.height = Math.round(h * k);
      ctx.drawImage(src, 0, 0, canvas.width, canvas.height); canvas.hidden = false;
      msg(scanMsg, t('scanning'), 1);
      canvas.toBlob(function (b) {
        var fd = new FormData(); fd.append('image', b, 'plate.jpg');
        api('api/scan.php', { method: 'POST', body: fd }).then(function (r) {
          if (r.status !== 'success') return msg(scanMsg, r.message || t('err_network'));
          var d = r.data, bb = d.bbox, sx = canvas.width / d.image.w, sy = canvas.height / d.image.h;
          ctx.lineWidth = Math.max(3, canvas.width / 200); ctx.strokeStyle = '#3ddc97'; ctx.strokeRect(bb.x * sx, bb.y * sy, bb.w * sx, bb.h * sy);
          $('#plate').value = d.plate_number;
          $('#resPlate').textContent = d.plate_number; $('#resConf').textContent = Math.round(d.confidence * 100) + '%';
          $('#result').hidden = false; msg(scanMsg, '', 1);
        });
      }, 'image/jpeg', 0.9);
    }

    /* fee converter */
    var cur = $('#currency'), feeOut = $('#feeOut');
    function fee() {
      if (!ratesData || !cur.value) { feeOut.textContent = '—'; return; }
      var v = ratesData.base_fee * ratesData.rates[cur.value];
      feeOut.textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: cur.value }).format(v);
    }
    api('api/rates.php').then(function (r) {
      if (r.status !== 'success') return msg(regMsg, r.message);
      ratesData = r.data; if (r.data.stale) msg(regMsg, t('rates_stale')); fee();
    });
    cur.onchange = fee;

    $('#regForm').onsubmit = function (e) {
      e.preventDefault(); var body = {};
      new FormData(e.target).forEach(function (v, k) { body[k] = v; });
      document.querySelectorAll('.err').forEach(function (n) { n.textContent = ''; });
      api('api/register.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }).then(function (r) {
        if (r.status === 'success') {
          msg(regMsg, t('registered') + ' ' + new Intl.NumberFormat(undefined, { style: 'currency', currency: r.data.currency }).format(r.data.fee_converted), 1);
          e.target.reset(); $('#result').hidden = true; fee(); return;
        }
        Object.keys(r.fields || {}).forEach(function (k) { var n = $('[data-err=' + k + ']'); if (n) n.textContent = r.fields[k]; });
        msg(regMsg, r.message || t('err_network'));
      });
    };
  }

  /* ---------- Registry (vehicles.php) ---------- */
  if ($('#vtable')) {
    var page = 1, tb = $('#vtable tbody'), timer;
    function money(v, c) { try { return new Intl.NumberFormat(undefined, { style: 'currency', currency: c }).format(v); } catch (e) { return v + ' ' + c; } }
    function td(tr, text) { var c = document.createElement('td'); c.textContent = text; tr.appendChild(c); }
    function load() {
      var p = new URLSearchParams({ q: $('#q').value, type: $('#type').value, from: $('#from').value, to: $('#to').value, page: page });
      api('api/vehicles.php?' + p).then(function (r) {
        tb.textContent = '';
        if (r.status !== 'success') return msg($('#vMsg'), r.message);
        msg($('#vMsg'), r.data.items.length ? '' : t('none'), 0);
        r.data.items.forEach(function (v) {
          var tr = document.createElement('tr');
          td(tr, v.plate_number); td(tr, v.owner_name); td(tr, v.make + ' ' + v.model + ' · ' + v.color); td(tr, v.body_type);
          td(tr, money(v.base_fee, 'USD') + (v.currency !== 'USD' ? ' → ' + money(v.fee_converted, v.currency) : '')); td(tr, v.created_at.slice(0, 10));
          tb.appendChild(tr);
        });
        $('#pageInfo').textContent = t('page') + ' ' + r.data.page + ' / ' + r.data.pages;
        $('#prev').disabled = r.data.page <= 1; $('#next').disabled = r.data.page >= r.data.pages;
      });
    }
    ['#q', '#type', '#from', '#to'].forEach(function (s) { $(s).addEventListener('input', function () { page = 1; clearTimeout(timer); timer = setTimeout(load, 250); }); });
    $('#prev').onclick = function () { page--; load(); }; $('#next').onclick = function () { page++; load(); };
    load();
  }
})();
