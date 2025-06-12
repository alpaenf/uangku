<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UangKu - Manajemen Keuangan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      color: #212529;
    }

    .dark-mode {
      background-color: #001f3f !important;
      color: white !important;
    }

    .dark-mode .list-group-item {
      background-color: #003366;
      border-color: #002b5c;
      color: white;
    }

    .dark-mode .list-group-item-success {
      background-color: #006d77 !important;
    }

    .dark-mode .list-group-item-danger {
      background-color: #b30000 !important;
    }

    .dark-mode h2,
    .dark-mode h4,
    .dark-mode p,
    .dark-mode em,
    .dark-mode h5 {
      color: white !important;
    }

    .btn {
      font-weight: 600;
    }

    .btn-icon {
      width: 2.2rem;
      height: 2.2rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      border-radius: 50%;
      font-size: 1rem;
    }

    .btn-edit {
      background-color: #0d6efd;
      color: white;
      border: none;
    }

    .btn-delete {
      background-color: #dc3545;
      color: white;
      border: none;
    }

    .btn-edit:hover {
      background-color: #0b5ed7;
    }

    .btn-delete:hover {
      background-color: #bb2d3b;
    }
  </style>
</head>

<body>
  <div class="container py-4">
    <h2 class="text-center fw-bold">UangKu</h2>
    <h4 class="text-center mb-4">Saldo: <span id="saldo">Rp0</span></h4>

    <p id="totalBulanan" class="text-end text-muted fst-italic"></p>
    <div id="tanggalContainer"></div>

    <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
      <a href="tambah.html" class="btn btn-primary">+ Tambah Transaksi</a>
      <button id="toggleMode" class="btn btn-secondary">Toggle Mode</button>
      <button id="exportCSV" class="btn btn-success">Export CSV</button>
      <button id="resetData" class="btn btn-danger">Reset Data</button>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const saldoEl = document.getElementById("saldo");
      const tanggalContainer = document.getElementById("tanggalContainer");
      const totalBulananEl = document.getElementById("totalBulanan");
      const toggleModeBtn = document.getElementById("toggleMode");

      let data = JSON.parse(localStorage.getItem("uangku_data")) || [];
      let darkMode = localStorage.getItem("uangku_darkmode") === "true";

      function toggleMode() {
        darkMode = !darkMode;
        localStorage.setItem("uangku_darkmode", darkMode);
        document.body.classList.toggle("dark-mode", darkMode);
      }

      toggleModeBtn.addEventListener("click", toggleMode);
      document.body.classList.toggle("dark-mode", darkMode);

      function formatRupiah(angka) {
        return 'Rp' + angka.toLocaleString('id-ID');
      }

      function formatTanggal(tanggalString) {
        const tanggal = new Date(tanggalString);
        if (isNaN(tanggal)) return "Tidak diketahui";
        return tanggal.toLocaleDateString("id-ID", { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
      }

      function formatWaktu(tanggalString) {
        const tanggal = new Date(tanggalString);
        if (isNaN(tanggal)) return "";
        return tanggal.toLocaleTimeString("id-ID", { hour: '2-digit', minute: '2-digit' });
      }

      function render() {
        tanggalContainer.innerHTML = "";
        let saldo = 0;
        const perTanggal = {};

        data.forEach(item => {
          const tanggal = new Date(item.tanggal).toISOString().split("T")[0];
          if (!perTanggal[tanggal]) perTanggal[tanggal] = [];
          perTanggal[tanggal].push(item);
        });

        Object.keys(perTanggal).sort((a, b) => new Date(b) - new Date(a)).forEach(tanggal => {
          const group = perTanggal[tanggal];
          let totalTanggal = 0;

          const section = document.createElement("div");
          section.className = "mb-3";
          const title = document.createElement("h5");
          title.textContent = formatTanggal(tanggal);
          section.appendChild(title);

          const listGroup = document.createElement("ul");
          listGroup.className = "list-group";

          group.forEach(item => {
            const li = document.createElement("li");
            li.className = "list-group-item d-flex justify-content-between align-items-center " +
              (item.jenis === "masuk" ? "list-group-item-success" : "list-group-item-danger");

            const info = document.createElement("div");
            info.innerHTML = `<div class="fw-semibold">${item.keterangan}</div><small>${formatTanggal(item.tanggal)} - ${formatWaktu(item.tanggal)}</small>`;

            const controls = document.createElement("div");
            controls.innerHTML = `
              <span class="fw-bold me-2 d-inline-block">${formatRupiah(item.nominal)}</span>
              <button class="btn btn-edit btn-icon me-1" onclick="editItem(${data.indexOf(item)})" title="Edit">
                <i class="bi bi-pencil-fill"></i>
              </button>
              <button class="btn btn-delete btn-icon" onclick="hapusItem(${data.indexOf(item)})" title="Hapus">
                <i class="bi bi-trash-fill"></i>
              </button>
            `;

            li.appendChild(info);
            li.appendChild(controls);
            listGroup.appendChild(li);

            saldo += item.jenis === "masuk" ? item.nominal : -item.nominal;
            totalTanggal += item.jenis === "masuk" ? item.nominal : -item.nominal;
          });

          section.appendChild(listGroup);

          const totalInfo = document.createElement("p");
          totalInfo.className = "text-end text-muted fst-italic mb-1";
          totalInfo.textContent = `Total tanggal ${formatTanggal(tanggal)}: ${formatRupiah(totalTanggal)}`;
          section.appendChild(totalInfo);

          tanggalContainer.appendChild(section);
        });

        saldoEl.textContent = formatRupiah(saldo);

        const bulanIni = new Date().toISOString().slice(0, 7);
        const totalBulanIni = data
          .filter(item => item.tanggal?.startsWith(bulanIni))
          .reduce((acc, item) => acc + (item.jenis === "masuk" ? item.nominal : -item.nominal), 0);

        totalBulananEl.textContent = `Total bulan ini: ${formatRupiah(totalBulanIni)}`;
      }

      render();

      window.editItem = function (index) {
        const item = data[index];
        const keterangan = prompt("Edit Keterangan:", item.keterangan);
        if (keterangan === null) return;
        const nominal = parseInt(prompt("Edit Nominal:", item.nominal), 10);
        if (isNaN(nominal)) return alert("Nominal tidak valid.");
        data[index] = { ...item, keterangan, nominal };
        localStorage.setItem("uangku_data", JSON.stringify(data));
        render();
      }

      window.hapusItem = function (index) {
        if (confirm("Yakin ingin menghapus transaksi ini?")) {
          data.splice(index, 1);
          localStorage.setItem("uangku_data", JSON.stringify(data));
          render();
        }
      }

      document.getElementById("exportCSV").addEventListener("click", function () {
        const rows = [["Tanggal", "Jenis", "Jumlah", "Keterangan"]];
        data.forEach(item => {
          rows.push([
            new Date(item.tanggal).toLocaleString("id-ID"),
            item.jenis,
            item.nominal,
            item.keterangan.replace(/"/g, '""')
          ]);
        });

        const csvContent = rows.map(e => e.join(",")).join("\n");
        const blob = new Blob(["\uFEFF" + csvContent], { type: "text/csv;charset=utf-8;" });
        const url = URL.createObjectURL(blob);

        const link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", "uangku_data.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });

      document.getElementById("resetData").addEventListener("click", function () {
        if (confirm("Yakin ingin menghapus semua data?")) {
          localStorage.removeItem("uangku_data");
          location.reload();
        }
      });
    });
  </script>
</body>

</html>
