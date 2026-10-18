/* ABC Futsal - interaksi front-end (tanpa library, jalan offline) */
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

  /* ----- Alert ----- */
  $$(".alert").forEach(function (a) {
    var x = $(".alert-x", a);
    if (x)
      x.addEventListener("click", function () {
        a.remove();
      });
    if (a.hasAttribute("data-autohide"))
      setTimeout(function () {
        a.style.transition = "opacity .5s";
        a.style.opacity = "0";
        setTimeout(function () {
          a.remove();
        }, 500);
      }, 6000);
  });

  /* ----- Konfirmasi ----- */
  document.addEventListener("click", function (e) {
    var el = e.target.closest("[data-confirm]");
    if (el && !window.confirm(el.getAttribute("data-confirm")))
      e.preventDefault();
  });

  /* ----- Modal ----- */
  $$("[data-modal-open]").forEach(function (b) {
    b.addEventListener("click", function (e) {
      e.preventDefault();
      var m = document.getElementById(b.getAttribute("data-modal-open"));
      if (m) m.classList.add("show");
    });
  });
  $$(".modal").forEach(function (m) {
    m.addEventListener("click", function (e) {
      if (e.target === m || e.target.closest("[data-modal-close]"))
        m.classList.remove("show");
    });
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape")
      $$(".modal.show").forEach(function (m) {
        m.classList.remove("show");
      });
  });

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

  /* ----- Form booking: cek jadwal + hitung total ----- */
  var fb = document.getElementById("form-booking");
  if (fb) {
    var tgl = $("[name=tanggal_main]", fb),
      lap = $("[name=id_lapangan]", fb),
      wkt = $("[name=id_waktu]", fb);
    var totalEl = document.getElementById("total-estimasi");
    var kecuali = fb.getAttribute("data-kecuali") || "0";
    var token = 0;

    function rupiah(n) {
      return (
        "Rp " +
        Math.round(n)
          .toString()
          .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
      );
    }
    function hitung() {
      var lo = lap.options[lap.selectedIndex],
        wo = wkt.options[wkt.selectedIndex];
      var h = lo && lo.dataset.harga ? parseFloat(lo.dataset.harga) : 0;
      var d = wo && wo.dataset.durasi ? parseFloat(wo.dataset.durasi) : 0;
      if (totalEl) totalEl.textContent = rupiah(h * d);
    }
    function cek() {
      hitung();
      if (!tgl.value || !lap.value) return;
      var my = ++token;
      fetch(
        "cek_jadwal.php?tanggal=" +
          encodeURIComponent(tgl.value) +
          "&lapangan=" +
          encodeURIComponent(lap.value) +
          "&kecuali=" +
          encodeURIComponent(kecuali),
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
    cek();
  }
})();
