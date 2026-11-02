(function () {
  "use strict";
  var $ = function (s, c) {
    return (c || document).querySelector(s);
  };
  var $$ = function (s, c) {
    return Array.prototype.slice.call((c || document).querySelectorAll(s));
  };
  function store(k, v) {
    try {
      if (v === undefined) return localStorage.getItem(k);
      localStorage.setItem(k, v);
    } catch (e) {
      return null;
    }
  }

  /* ----- Dark mode ----- */
  function setTheme(t) {
    document.documentElement.setAttribute("data-theme", t);
    $$(".theme-toggle use").forEach(function (u) {
      u.setAttribute("href", t === "dark" ? "#i-sun" : "#i-moon");
    });
  }
  setTheme(document.documentElement.getAttribute("data-theme") || "light");
  $$(".theme-toggle").forEach(function (b) {
    b.addEventListener("click", function () {
      var t =
        document.documentElement.getAttribute("data-theme") === "dark"
          ? "light"
          : "dark";
      setTheme(t);
      store("abc-theme", t);
    });
  });

  /* ----- Sidebar ----- */
  var mobile = function () {
    return window.matchMedia("(max-width: 860px)").matches;
  };
  var burger = $(".side-toggle");
  if (burger) {
    burger.addEventListener("click", function () {
      if (mobile()) document.body.classList.toggle("side-open");
      else document.body.classList.toggle("side-hidden");
    });
  }
  var ov = $(".side-overlay");
  if (ov)
    ov.addEventListener("click", function () {
      document.body.classList.remove("side-open");
    });
  $$(".menu-toggle").forEach(function (a) {
    a.addEventListener("click", function () {
      a.parentNode.classList.toggle("open");
    });
  });

  /* ----- Toast (flash message) ----- */
  function dismissToast(t) {
    if (!t || t.classList.contains("out")) return;
    t.classList.add("out");
    setTimeout(function () {
      var stack = t.parentNode;
      t.remove();
      if (stack && !stack.children.length) stack.remove();
    }, 300);
  }
  $$("[data-toast]").forEach(function (t, i) {
    var x = $(".toast-x", t);
    if (x)
      x.addEventListener("click", function () {
        dismissToast(t);
      });
    var timer;
    function start() {
      timer = setTimeout(function () {
        dismissToast(t);
      }, 6000 + i * 600);
    }
    t.addEventListener("mouseenter", function () {
      clearTimeout(timer);
    });
    t.addEventListener("mouseleave", start);
    start();
  });

  /* ----- Modal (buka / tutup / fokus) ----- */
  var lastFocus = null;
  var FOCUSABLE =
    'a[href],button:not([disabled]),input:not([type=hidden]):not([disabled]),select:not([disabled]),textarea:not([disabled])';
  function openModal(m) {
    if (!m) return;
    lastFocus = document.activeElement;
    m.classList.add("show");
    document.body.classList.add("modal-open");
    var f = $("[data-autofocus]", m) || $(FOCUSABLE, m);
    setTimeout(function () {
      if (f) f.focus();
    }, 30);
  }
  function closeModal(m) {
    if (!m || !m.classList.contains("show")) return;
    m.classList.remove("show");
    if (!$(".modal.show")) document.body.classList.remove("modal-open");
    if (m._onclose) {
      var cb = m._onclose;
      m._onclose = null;
      cb();
    }
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }
  var downOnBackdrop = false;
  document.addEventListener("mousedown", function (e) {
    downOnBackdrop = e.target.classList && e.target.classList.contains("modal");
  });
  document.addEventListener("click", function (e) {
    var o = e.target.closest("[data-modal-open]");
    if (o) {
      e.preventDefault();
      openModal(document.getElementById(o.getAttribute("data-modal-open")));
      return;
    }
    var c = e.target.closest("[data-modal-close]");
    if (c) {
      closeModal(c.closest(".modal"));
      return;
    }
    if (downOnBackdrop && e.target.classList.contains("modal"))
      closeModal(e.target);
  });
  document.addEventListener("keydown", function (e) {
    var open = $$(".modal.show");
    if (!open.length) return;
    var m = open[open.length - 1];
    if (e.key === "Escape") closeModal(m);
    if (e.key === "Tab") {
      var els = $$(FOCUSABLE, m).filter(function (el) {
        return el.offsetParent !== null;
      });
      if (!els.length) return;
      var first = els[0],
        last = els[els.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  });

  /* ----- Konfirmasi (pengganti window.confirm) ----- */
  var cfModal;
  var ICONS = { danger: "trash", warn: "alert", ok: "check" };
  function buildConfirm() {
    if (cfModal) return cfModal;
    cfModal = document.createElement("div");
    cfModal.className = "modal";
    cfModal.id = "confirmModal";
    cfModal.setAttribute("role", "alertdialog");
    cfModal.setAttribute("aria-modal", "true");
    cfModal.setAttribute("aria-labelledby", "cfTitle");
    cfModal.setAttribute("aria-describedby", "cfMsg");
    cfModal.innerHTML =
      '<div class="modal-box confirm-box"><div class="modal-body cf-body">' +
      '<div class="cf-icon"><svg class="ic"><use href="#i-alert"/></svg></div>' +
      '<h3 id="cfTitle"></h3><p class="cf-msg" id="cfMsg"></p>' +
      '<div class="cf-actions"><button type="button" class="btn btn-light" data-modal-close data-autofocus>Batal</button>' +
      '<button type="button" class="btn" id="cfOk"></button></div></div></div>';
    document.body.appendChild(cfModal);
    return cfModal;
  }
  function konfirmasi(o) {
    return new Promise(function (resolve) {
      var m = buildConfirm();
      var type = o.type || "danger";
      var icon = o.icon || ICONS[type] || "alert";
      var ic = $(".cf-icon", m);
      ic.className = "cf-icon " + type;
      $("use", ic).setAttribute("href", "#i-" + icon);
      $("#cfTitle", m).textContent = o.title || "Yakin?";
      $("#cfMsg", m).textContent = o.message || "";
      var ok = $("#cfOk", m);
      ok.textContent = o.ok || "Ya, lanjut";
      ok.className =
        "btn " +
        (type === "ok" ? "btn-ok" : type === "warn" ? "btn-warn" : "btn-danger");
      ok.onclick = function () {
        m._onclose = null;
        closeModal(m);
        resolve(true);
      };
      m._onclose = function () {
        resolve(false);
      };
      openModal(m);
    });
  }
  function cfOpts(el) {
    return {
      message: el.getAttribute("data-confirm"),
      title: el.getAttribute("data-confirm-title"),
      ok: el.getAttribute("data-confirm-ok"),
      type: el.getAttribute("data-confirm-type"),
      icon: el.getAttribute("data-confirm-icon"),
    };
  }
  document.addEventListener("click", function (e) {
    var el = e.target.closest("[data-confirm]");
    if (!el || el.tagName === "FORM") return;
    e.preventDefault();
    konfirmasi(cfOpts(el)).then(function (ok) {
      if (!ok) return;
      if (el.tagName === "A") window.location.href = el.href;
      else if (el.form) {
        el.form.__ok = true;
        el.form.requestSubmit(el);
      }
    });
  });
  document.addEventListener("submit", function (e) {
    var f = e.target;
    if (f.hasAttribute && f.hasAttribute("data-confirm") && !f.__ok) {
      e.preventDefault();
      konfirmasi(cfOpts(f)).then(function (ok) {
        if (ok) {
          f.__ok = true;
          f.requestSubmit();
        }
      });
    }
  });

  /* ----- Cegah submit ganda (penting buat pembayaran) ----- */
  document.addEventListener("submit", function (e) {
    var f = e.target;
    if (e.defaultPrevented || !f.method || f.method.toLowerCase() !== "post")
      return;
    if (f.__sent) {
      e.preventDefault();
      return;
    }
    f.__sent = true;
    setTimeout(function () {
      $$("button[type=submit]", f).forEach(function (b) {
        b.disabled = true;
      });
    }, 0);
  });
  window.addEventListener("pageshow", function (e) {
    if (!e.persisted) return;
    $$("form").forEach(function (f) {
      f.__sent = false;
      f.__ok = false;
      $$("button[type=submit]", f).forEach(function (b) {
        b.disabled = false;
      });
    });
  });

  window.ABC = { confirm: konfirmasi, openModal: openModal, closeModal: closeModal };

  /* ----- Helper rupiah ----- */
  function rupiah(n) {
    return (
      "Rp " +
      Math.round(n)
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
    );
  }
  function angka(v) {
    return parseInt(String(v || "").replace(/\D/g, ""), 10) || 0;
  }
  function formatInput(el) {
    var n = angka(el.value);
    el.value = n ? String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ".") : "";
  }

  /* ----- Tabel: cari + halaman ----- */
  $$("table.js-table").forEach(function (t) {
    var body = t.tBodies[0];
    var rows = Array.prototype.slice.call(body.rows);
    if (!rows.length) return;
    var wrap = t.closest(".table-wrap") || t.parentNode;
    var size = 10,
      page = 1,
      q = "";

    var bar = document.createElement("div");
    bar.className = "table-bar";
    bar.innerHTML =
      "<label>Tampilkan <select><option>10</option><option>25</option><option>50</option></select> data</label>" +
      '<label class="search"><svg class="ic"><use href="#i-search"/></svg><input type="search" placeholder="Cari data..."></label>';
    wrap.parentNode.insertBefore(bar, wrap);
    var foot = document.createElement("div");
    foot.className = "table-foot";
    foot.innerHTML = '<span class="info"></span><div class="pager"></div>';
    wrap.parentNode.appendChild(foot);

    var empty = document.createElement("tr");
    empty.innerHTML =
      '<td class="empty" colspan="' +
      t.tHead.rows[0].cells.length +
      '">Tidak ada data yang cocok 🤷</td>';
    empty.style.display = "none";
    body.appendChild(empty);

    function draw() {
      var hit = rows.filter(function (r) {
        return r.textContent.toLowerCase().indexOf(q) > -1;
      });
      var pages = Math.max(1, Math.ceil(hit.length / size));
      if (page > pages) page = pages;
      rows.forEach(function (r) {
        r.style.display = "none";
      });
      var start = (page - 1) * size;
      hit.slice(start, start + size).forEach(function (r, i) {
        r.style.display = "";
        var n = r.querySelector(".no");
        if (n) n.textContent = start + i + 1;
      });
      empty.style.display = hit.length ? "none" : "";
      $(".info", foot).textContent = hit.length
        ? "Menampilkan " +
          (start + 1) +
          "–" +
          Math.min(start + size, hit.length) +
          " dari " +
          hit.length +
          " data"
        : "0 data";
      var pg = $(".pager", foot);
      pg.innerHTML = "";
      function btn(label, p, on, dis) {
        var b = document.createElement("button");
        b.type = "button";
        b.textContent = label;
        if (on) b.className = "on";
        if (dis) b.disabled = true;
        b.addEventListener("click", function () {
          page = p;
          draw();
        });
        pg.appendChild(b);
      }
      if (pages > 1) {
        btn("‹", page - 1, false, page === 1);
        for (var p = 1; p <= pages; p++) {
          if (p === 1 || p === pages || Math.abs(p - page) <= 1)
            btn(p, p, p === page, false);
          else if (Math.abs(p - page) === 2) {
            var s = document.createElement("span");
            s.textContent = "…";
            s.style.padding = "6px 4px";
            pg.appendChild(s);
          }
        }
        btn("›", page + 1, false, page === pages);
      }
    }
    $("select", bar).addEventListener("change", function () {
      size = parseInt(this.value, 10);
      page = 1;
      draw();
    });
    $("input", bar).addEventListener("input", function () {
      q = this.value.toLowerCase().trim();
      page = 1;
      draw();
    });
    draw();
  });

  /* ----- Form booking: cek jadwal + hitung total + pembayaran ----- */
  var fb = document.getElementById("form-booking");
  if (fb) {
    var tgl = $("[name=tanggal_main]", fb),
      lap = $("[name=id_lapangan]", fb),
      wkt = $("[name=id_waktu]", fb);
    var totalEl = document.getElementById("total-estimasi"),
      bayarEl = document.getElementById("total-bayar"),
      sisaEl = document.getElementById("total-sisa");
    var dpIn = document.getElementById("jumlah_dp"),
      grpDp = document.getElementById("grp-dp"),
      grpMetode = document.getElementById("grp-metode"),
      dpHint = document.getElementById("dp-hint");
    var staff = fb.getAttribute("data-staff") === "1";
    var persen = parseInt(fb.getAttribute("data-dp-persen"), 10) || 30;
    var token = 0;
    var dpDirty = dpIn.value !== "";
    if (dpDirty) formatInput(dpIn);

    function total() {
      var lo = lap.options[lap.selectedIndex],
        wo = wkt.options[wkt.selectedIndex];
      var h = lo && lo.dataset.harga ? parseFloat(lo.dataset.harga) : 0;
      var d = wo && wo.dataset.durasi ? parseFloat(wo.dataset.durasi) : 0;
      return Math.round(h * d);
    }
    function tipe() {
      var r = $("[name=tipe_bayar]:checked", fb);
      return r ? r.value : "";
    }
    function minDp(t) {
      return Math.min(t, Math.ceil((t * persen) / 100 / 1000) * 1000);
    }
    function hitung() {
      var t = total(),
        tp = tipe();
      grpDp.style.display = tp === "dp" ? "" : "none";
      grpMetode.style.display = tp === "nanti" ? "none" : "";
      if (tp === "dp" && !dpDirty && t && !staff) dpIn.value = String(minDp(t)).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      var bayar = tp === "lunas" ? t : tp === "dp" ? angka(dpIn.value) : 0;
      if (tp === "dp")
        dpHint.textContent = !t
          ? "Pilih lapangan dan jam dulu."
          : staff
            ? "Maksimal " + rupiah(Math.max(0, t - 1)) + " (kalau bayar penuh pilih \"Bayar lunas\")."
            : "Minimal " + rupiah(minDp(t)) + " (" + persen + "% dari total).";
      totalEl.textContent = rupiah(t);
      bayarEl.textContent = rupiah(bayar);
      var sisa = Math.max(0, t - bayar);
      sisaEl.textContent = rupiah(sisa);
      sisaEl.classList.toggle("has", sisa > 0);
    }
    function cek() {
      hitung();
      if (!tgl.value || !lap.value) return;
      var my = ++token;
      fetch(
        "cek_jadwal.php?tanggal=" +
          encodeURIComponent(tgl.value) +
          "&lapangan=" +
          encodeURIComponent(lap.value),
        { credentials: "same-origin" },
      )
        .then(function (r) {
          return r.ok ? r.json() : {};
        })
        .then(function (m) {
          if (my !== token) return;
          $$("option", wkt).forEach(function (o) {
            if (!o.value) return;
            var asli = o.getAttribute("data-label") || o.textContent.trim();
            o.setAttribute("data-label", asli);
            var st = m[o.value];
            o.disabled = !!st;
            o.textContent =
              st === "penuh"
                ? asli + " — penuh"
                : st === "lewat"
                  ? asli + " — sudah lewat"
                  : asli;
          });
          if (
            wkt.options[wkt.selectedIndex] &&
            wkt.options[wkt.selectedIndex].disabled
          )
            wkt.value = "";
          hitung();
        })
        .catch(function () {
          /* validasi server tetap jalan */
        });
    }
    [tgl, lap].forEach(function (el) {
      el.addEventListener("change", cek);
    });
    wkt.addEventListener("change", hitung);
    $$("[name=tipe_bayar]", fb).forEach(function (r) {
      r.addEventListener("change", function () {
        if (tipe() === "dp" && staff && !dpDirty) dpIn.value = "";
        hitung();
        if (tipe() === "dp") dpIn.focus();
      });
    });
    dpIn.addEventListener("input", function () {
      dpDirty = true;
      formatInput(dpIn);
      hitung();
    });
    cek();
  }

  /* ----- Modal pembayaran & batal-dengan-DP (kasir/admin) ----- */
  var mb = document.getElementById("modalBayar");
  if (mb) {
    var fBayar = document.getElementById("formBayar"),
      inJml = document.getElementById("mb-jumlah"),
      hintEl = document.getElementById("mb-hint"),
      okBtn = $("button[type=submit]", fBayar);
    var sisaNow = 0;
    function cekJumlah() {
      var j = angka(inJml.value);
      var bad = j < 1 || j > sisaNow;
      okBtn.disabled = bad;
      hintEl.classList.toggle("err", j > sisaNow);
      hintEl.textContent =
        j > sisaNow
          ? "Melebihi sisa tagihan (" + rupiah(sisaNow) + ")."
          : j >= 1
            ? j === sisaNow
              ? "Pembayaran ini melunasi booking."
              : "Sisa setelah bayar: " + rupiah(sisaNow - j)
            : "";
    }
    document.addEventListener("click", function (e) {
      var b = e.target.closest("[data-bayar]");
      if (!b) return;
      var t = parseInt(b.dataset.total, 10),
        d = parseInt(b.dataset.dibayar, 10);
      sisaNow = t - d;
      fBayar.action = b.dataset.action;
      fBayar.__sent = false;
      document.getElementById("mb-kode").textContent = b.dataset.kode;
      document.getElementById("mb-nama").textContent = b.dataset.nama;
      document.getElementById("mb-total").textContent = rupiah(t);
      document.getElementById("mb-dibayar").textContent = rupiah(d);
      document.getElementById("mb-sisa").textContent = rupiah(sisaNow);
      inJml.value = String(sisaNow).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      cekJumlah();
      openModal(mb);
      setTimeout(function () {
        inJml.select();
      }, 60);
    });
    inJml.addEventListener("input", function () {
      formatInput(inJml);
      cekJumlah();
    });
    document.getElementById("mb-lunasi").addEventListener("click", function () {
      inJml.value = String(sisaNow).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      cekJumlah();
      inJml.focus();
    });
  }
  var mc = document.getElementById("modalCancel");
  if (mc) {
    document.addEventListener("click", function (e) {
      var b = e.target.closest("[data-cancel-dp]");
      if (!b) return;
      var f = document.getElementById("formCancel");
      f.action = b.dataset.action;
      f.__sent = false;
      $$("button[type=submit]", f).forEach(function (x) {
        x.disabled = false;
      });
      $("[name=dp][value=hangus]", f).checked = true;
      document.getElementById("mc-kode").textContent = b.dataset.kode;
      document.getElementById("mc-dp").textContent = rupiah(
        parseInt(b.dataset.dibayar, 10),
      );
      openModal(mc);
    });
  }
})();
