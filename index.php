<?php
// Memulai session untuk menyimpan data sementara di dalam array
session_start();

// Inisialisasi struktur data array jika session belum ada (ditambahkan parameter tanggal hari ini)
if (!isset($_SESSION['tasks'])) {
    $tanggalHariIni = date('Y-m-d');
    $_SESSION['tasks'] = [
        ["id" => 1, "title" => "Belajar PHP", "status" => "belum", "tanggal" => $tanggalHariIni],
        ["id" => 2, "title" => "Kerjakan tugas UX", "status" => "selesai", "tanggal" => $tanggalHariIni]
    ];
}

// 1. Logika Tambah Tugas (Create) - Menyimpan tanggal inputan user atau otomatis hari ini
if (isset($_POST['tambah'])) {
    $titleBaru = trim($_POST['title']);
    $tanggalBaru = !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');

    if (!empty($titleBaru)) {
        $newId = count($_SESSION['tasks']) > 0 ? max(array_column($_SESSION['tasks'], 'id')) + 1 : 1;
        
        $_SESSION['tasks'][] = [
            "id" => $newId,
            "title" => htmlspecialchars($titleBaru),
            "status" => "belum",
            "tanggal" => $tanggalBaru
        ];
    }
    header("Location: index.php");
    exit;
}

// 2. Logika Edit Tugas (Update Data Teks & Tanggal)
if (isset($_POST['edit'])) {
    $index = $_POST['index'];
    $titleBaru = trim($_POST['title']);
    $tanggalBaru = $_POST['tanggal'];

    if (!empty($titleBaru) && !empty($tanggalBaru) && isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['title'] = htmlspecialchars($titleBaru);
        $_SESSION['tasks'][$index]['tanggal'] = $tanggalBaru;
    }
    header("Location: index.php");
    exit;
}

// 3. Logika Ubah Status Selesai / Belum (Toggle Status)
if (isset($_GET['toggle'])) {
    $index = $_GET['toggle'];
    if (isset($_SESSION['tasks'][$index])) {
        $_SESSION['tasks'][$index]['status'] = ($_SESSION['tasks'][$index]['status'] == "belum") ? "selesai" : "belum";
    }
    header("Location: index.php");
    exit;
}

// 4. Logika Hapus Tugas (Delete)
if (isset($_GET['delete'])) {
    $index = $_GET['delete'];
    if (isset($_SESSION['tasks'][$index])) {
        unset($_SESSION['tasks'][$index]);
        $_SESSION['tasks'] = array_values($_SESSION['tasks']); // Merapikan indeks array
    }
    header("Location: index.php");
    exit;
}

/**
 * Fungsi Reusable untuk merender daftar tugas dan format Tanggal (Hari, Bulan, Tahun)
 */
function tampilkanDaftar($tasks) {
    if (empty($tasks)) {
        echo '<div class="alert alert-info text-center">Belum ada daftar tugas.</div>';
        return;
    }

    echo '<ul class="list-group shadow-sm">';
    foreach ($tasks as $index => $task) {
        $isSelesai = ($task['status'] == "selesai");
        $styleCoret = $isSelesai ? 'text-decoration: line-through; color: #6c757d;' : '';
        $badge = $isSelesai 
            ? '<span class="badge bg-success">Selesai</span>' 
            : '<span class="badge bg-secondary">Belum Selesai</span>';

        // Format Tanggal menjadi: Hari, Tanggal Bulan Tahun (Bahasa Indonesia)
        $tglInput = isset($task['tanggal']) ? $task['tanggal'] : date('Y-m-d');
        $timestamp = strtotime($tglInput);
        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w', $timestamp)];
        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][(int)date('m', $timestamp)];
        $formatTanggalLengkap = $namaHari . ", " . date('d', $timestamp) . " " . $namaBulan . " " . date('Y', $timestamp);

        echo '<li class="list-group-item d-flex justify-content-between align-items-center py-3">';
        
        // Bagian Kiri: Tombol centang, Nama Tugas, dan Keterangan Tanggal
        echo '<div class="d-flex align-items-center">';
        echo '<a href="index.php?toggle=' . $index . '" class="text-decoration-none me-3 fs-5">';
        echo $isSelesai ? '✅' : '⬜';
        echo '</a>';
        echo '<div>';
        echo '<span style="' . $styleCoret . '" class="fs-5 d-block">' . htmlspecialchars($task['title']) . '</span>';
        echo '<small class="text-muted">📅 ' . $formatTanggalLengkap . '</small>';
        echo '</div>';
        echo '</div>';

        // Bagian Kanan: Badge, Tombol Edit, dan Tombol Delete
        echo '<div class="d-flex align-items-center gap-2">';
        echo $badge;
        echo '<button type="button" class="btn btn-warning btn-sm text-white" data-bs-toggle="modal" data-bs-target="#editModal' . $index . '">Edit</button>';
        echo '<a href="index.php?delete=' . $index . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Yakin ingin menghapus tugas ini?\')">Delete</a>';
        echo '</div>';

        echo '</li>';

        // Modal Edit untuk setiap item tugas (Termasuk Input Tanggal)
        echo '
        <div class="modal fade" id="editModal' . $index . '" tabindex="-1" aria-labelledby="editModalLabel' . $index . '" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="index.php" method="POST">
                <div class="modal-header">
                  <h5 class="modal-title" id="editModalLabel' . $index . '">Edit Tugas</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                  <input type="hidden" name="index" value="' . $index . '">
                  <div class="mb-3">
                    <label for="title" class="form-label">Nama Tugas</label>
                    <input type="text" class="form-control" name="title" value="' . htmlspecialchars($task['title']) . '" required autocomplete="off">
                  </div>
                  <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" value="' . $tglInput . '" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
              </form>
            </div>
          </div>
        </div>';
    }
    echo '</ul>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi To-Do List - Tanggal & CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center bg-dark text-white rounded-top">
                        <h2 class="mb-0">Daftar Tugas Harian</h2>
                        <p class="mb-0 text-white-50"></p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Form Tambah Tugas & Tanggal -->
                        <form action="index.php" method="POST" class="mb-4 p-3 border rounded bg-white shadow-sm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Tugas</label>
                                <input type="text" name="title" class="form-control" placeholder="Masukkan nama tugas baru..." required autocomplete="off">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <button type="submit" name="tambah" class="btn btn-success px-4">Tambah Tugas</button>
                        </form>

                        <!-- Menampilkan Daftar Tugas -->
                        <h4 class="mb-3">Daftar Tugas</h4>
                        <?php 
                            tampilkanDaftar($_SESSION['tasks']); 
                        ?>
                    </div>
                </div>

                <footer class="text-center text-muted">
                    <small>&copy; 2026 - By Firsty Aprilia Sari Gurium</small>
                </footer>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

