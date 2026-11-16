/* ABC Futsal - interaksi front-end (vanilla JS, secukupnya) */
(function () {
  "use strict";
  var $ = function (s, c) {
    return (c || document).querySelector(s);
  };
  var $$ = function (s, c) {
    return Array.prototype.slice.call((c || document).querySelectorAll(s));
  };

  /* ----- 1. Sidebar (buka/tutup) ----- */
  var tombol = $(".side-toggle");
  if (tombol) {
    tombol.addEventListener("click", function () {
      if (window.matchMedia("(max-width: 991px)").matches)
        document.body.classList.toggle("side-open");
      else document.body.classList.toggle("side-hidden");
    });
  }
  var overlay = $(".side-overlay");
  if (overlay)
    overlay.addEventListener("click", function () {
      document.body.classList.remove("side-open");
    });

  /* ----- 2. Alert hilang sendiri setelah 6 detik ----- */
  $$(".alert[data-autohide]").forEach(function (a) {
    setTimeout(function () {
      if (window.bootstrap) bootstrap.Alert.getOrCreateInstance(a).close();
    }, 6000);
  });

  /* ----- 3. Konfirmasi SweetAlert2: <a data-confirm="..."> atau <form data-confirm="..."> ----- */
  var formTerkonfirmasi = new WeakSet();
  function peringatan(pesan) {
    if (!window.Swal) {
      console.error("SweetAlert2 gagal dimuat; pesan tidak dapat ditampilkan.");
      return;
    }
    window.Swal.fire({
      title: "Periksa data",
      text: pesan,
      icon: "warning",
      confirmButtonText: "Mengerti",
    });
  }

  function konfirmasi(pesan) {
    if (!window.Swal) {
      console.error("SweetAlert2 gagal dimuat; aksi konfirmasi dibatalkan.");
      return Promise.resolve(false);
    }
    return window.Swal.fire({
      title: "Konfirmasi",
      text: pesan,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, lanjutkan",
      cancelButtonText: "Batal",
      reverseButtons: true,
      focusCancel: true,
    }).then(function (hasil) {
      return hasil.isConfirmed;
    });
  }

  document.addEventListener("click", function (e) {
    var el = e.target.closest("a[data-confirm]");
    if (!el) return;
    e.preventDefault();
    konfirmasi(el.getAttribute("data-confirm")).then(function (setuju) {
      if (setuju) window.location.href = el.href;
    });
  });
  document.addEventListener("submit", function (e) {
    var form = e.target;
    var pesan = form.getAttribute("data-confirm");
    if (!pesan) return;
    if (formTerkonfirmasi.has(form)) {
      formTerkonfirmasi.delete(form);
      return;
    }

    e.preventDefault();
    var tombolSubmit = e.submitter;
    konfirmasi(pesan).then(function (setuju) {
      if (!setuju || !form.reportValidity()) return;
      if (typeof form.requestSubmit !== "function") {
        console.error("Browser tidak mendukung form.requestSubmit().");
        return;
      }
      formTerkonfirmasi.add(form);
      if (tombolSubmit) form.requestSubmit(tombolSubmit);
      else form.requestSubmit();
    });
  });

  /* ----- 4. Cari di tabel: <input data-cari-tabel="#idTabel"> ----- */
  $$("input[data-cari-tabel]").forEach(function (input) {
    var tabel = $(input.getAttribute("data-cari-tabel"));
    if (!tabel) return;
    input.addEventListener("input", function () {
      var kata = input.value.toLowerCase().trim();
      $$("tbody tr", tabel).forEach(function (tr) {
        if (tr.querySelector(".empty")) return;
        tr.style.display =
          tr.textContent.toLowerCase().indexOf(kata) > -1 ? "" : "none";
      });
    });
  });

  /* ----- 5. Form booking kasir: cek jadwal, hitung total, atur nominal bayar ----- */
  var form = document.getElementById("form-booking");
  if (form && window.DATA_BOOKING) {
    var HARGA = window.DATA_BOOKING.harga; // { id_lapangan: { id_waktu: harga } }
    var DP_PERSEN = window.DATA_BOOKING.dpPersen; // contoh: 30
    var url = window.DATA_BOOKING.urlCek;

    var tgl = form.elements["tanggal_main"];
    var lap = form.elements["id_lapangan"];
    var wkt = form.elements["id_waktu"];
    var tipe = form.elements["tipe_bayar"];
    var nominal = form.elements["jumlah_bayar"];
    var totalEl = document.getElementById("total-harga");
    var hintEl = document.getElementById("hint-bayar");
    var sisaEl = document.getElementById("sisa-bayar");
    var total = 0;
    var nomorCek = 0;

    var rp = function (n) {
      return (
        "Rp " +
        Math.round(n)
          .toString()
          .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
      );
    };

    // a. Hitung total dari harga slot yang dipilih
    function hitungTotal() {
      total =
        HARGA[lap.value] && HARGA[lap.value][wkt.value]
          ? HARGA[lap.value][wkt.value]
          : 0;
      totalEl.textContent = rp(total);
      aturNominal(false);
    }

    // b. Atur nominal sesuai pilihan DP / Lunas
    //    paksa = true -> nominal diisi ulang otomatis (DP minimal / total)
    function aturNominal(paksa) {
      var minDp = Math.ceil((total * DP_PERSEN) / 100);
      if (tipe.value === "Lunas") {
        nominal.value = total || "";
        nominal.readOnly = true;
        hintEl.textContent = "Lunas: nominal otomatis sama dengan total.";
      } else {
        nominal.readOnly = false;
        nominal.min = minDp;
        nominal.max = Math.max(total - 1, 0);
        if (paksa || !nominal.value || parseInt(nominal.value, 10) < minDp)
          nominal.value = minDp || "";
        hintEl.textContent = total
          ? "DP minimal " +
            DP_PERSEN +
            "% = " +
            rp(minDp) +
            ", dan harus kurang dari total."
          : "";
      }
      hitungSisa();
    }

    // Sisa = total - nominal yang diketik (tidak mengubah isi kolom nominal)
    function hitungSisa() {
      var bayar = parseInt(nominal.value, 10) || 0;
      sisaEl.textContent = rp(Math.max(total - bayar, 0));
    }

    // c. Tanya server jam mana yang sudah penuh / lewat (AJAX)
    function cekJadwal() {
      if (!tgl.value || !lap.value) {
        hitungTotal();
        return;
      }
      var saya = ++nomorCek;
      fetch(
        url +
          "?tanggal=" +
          encodeURIComponent(tgl.value) +
          "&lapangan=" +
          encodeURIComponent(lap.value),
      )
        .then(function (r) {
          return r.ok ? r.json() : {};
        })
        .then(function (status) {
          if (saya !== nomorCek) return;
          $$("option", wkt).forEach(function (o) {
            if (!o.value) return;
            var asli = o.getAttribute("data-label") || o.textContent.trim();
            o.setAttribute("data-label", asli);
            var s = status[o.value] || "kosong";
            o.disabled = s !== "kosong";
            var ket = {
              terisi: " — sudah dibooking",
              lewat: " — sudah lewat",
              perbaikan: " — perbaikan",
              tanpa_harga: " — harga belum diatur",
            };
            o.textContent = asli + (ket[s] || "");
          });
          if (
            wkt.options[wkt.selectedIndex] &&
            wkt.options[wkt.selectedIndex].disabled
          )
            wkt.value = "";
          hitungTotal();
        })
        .catch(function () {
          hitungTotal();
        }); // kalau gagal, validasi server tetap berjalan
    }

    tgl.addEventListener("change", cekJadwal);
    lap.addEventListener("change", cekJadwal);
    wkt.addEventListener("change", hitungTotal);
    tipe.addEventListener("change", function () {
      aturNominal(true);
    });
    nominal.addEventListener("input", hitungSisa);

    // Validasi ringan sebelum kirim (validasi utama tetap di server)
    form.addEventListener("submit", function (e) {
      var bayar = parseInt(nominal.value, 10) || 0;
      var minDp = Math.ceil((total * DP_PERSEN) / 100);
      if (!total) {
        e.preventDefault();
        peringatan("Pilih lapangan dan jam yang tersedia dulu.");
        return;
      }
      if (tipe.value === "DP" && (bayar < minDp || bayar >= total)) {
        e.preventDefault();
        peringatan(
          "Nominal DP minimal " +
            rp(minDp) +
            " dan harus kurang dari total " +
            rp(total) +
            ".",
        );
      }
    });

    cekJadwal();
  }

  /* ----- 6. Form bayar pelunasan / refund: hitung sisa langsung ----- */
  $$("[data-total-tampil]").forEach(function (el) {
    var sumber = document.getElementById(el.getAttribute("data-total-tampil"));
    if (!sumber) return;
    var hitung = function () {
      var maks = parseInt(sumber.getAttribute("data-maks"), 10) || 0;
      var v = parseInt(sumber.value, 10) || 0;
      el.textContent =
        "Rp " +
        Math.max(maks - v, 0)
          .toString()
          .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    };
    sumber.addEventListener("input", hitung);
    hitung();
  });
})();
