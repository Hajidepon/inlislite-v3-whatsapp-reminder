<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inlislite WhatsApp Reminder</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            overflow-x: hidden;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
        }

        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .content-area {
            padding: 20px;
        }

        .card-stat {
            border-left: 5px solid #0d6efd;
        }
    </style>
</head>

<body>

    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-3 d-flex flex-column">
            <h4 class="mb-4 text-center mt-2">
                <i class="fab fa-whatsapp"></i> Inlislite<br><small style="font-size: 0.6em;">WA Reminder</small>
            </h4>
            <hr class="text-white-50">

            <ul class="nav nav-pills flex-column mb-auto" id="menuTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active w-100 text-start" id="dashboard-tab" data-bs-toggle="tab"
                        data-bs-target="#dashboard-pane" type="button" role="tab">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link w-100 text-start" id="settings-tab" data-bs-toggle="tab"
                        data-bs-target="#settings-pane" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i> Pengaturan
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link w-100 text-start" id="help-tab" data-bs-toggle="tab"
                        data-bs-target="#help-pane" type="button" role="tab">
                        <i class="fas fa-question-circle me-2"></i> Bantuan
                    </button>
                </li>
            </ul>

            <hr class="text-white-50">
            <div class="small text-white-50 text-center">
                &copy; <?php echo date('Y'); ?> Reminder System
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 content-area">
            <div class="tab-content" id="myTabContent">

                <!-- Tab: Dashboard -->
                <div class="tab-pane fade show active" id="dashboard-pane" role="tabpanel">
                    <h3 class="mb-4 text-dark border-bottom pb-2">Selamat Datang, Pustakawan</h3>

                    <!-- Top Stats -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card shadow-sm card-stat">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="text-muted">Jatuh Tempo Hari Ini (<?php echo date('d M Y'); ?>)</h5>
                                        <h3><?php echo $count_due; ?> Anggota</h3>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($count_due > 0): ?>
                                            <button id="btnProcess" class="btn btn-success btn-lg">
                                                <i class="fas fa-paper-plane me-2"></i> Kirim Reminder
                                            </button>
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle"></i>
                                                Perlu diproses</div>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-lg" disabled>
                                                <i class="fas fa-check me-2"></i> Aman
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Progress Area (Hidden by default) -->
                                <div id="progressArea" class="card-footer bg-light d-none">
                                    <label class="small fw-bold">Sedang Mengirim...</label>
                                    <div class="progress mt-1" style="height: 10px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Quick Actions -->
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Aksi Cepat</h5>
                                    <button class="btn btn-outline-dark w-100 mb-2" data-bs-toggle="modal"
                                        data-bs-target="#logModal">
                                        <i class="fas fa-history me-2"></i> Lihat Riwayat Log
                                    </button>
                                    <a href="https://web.whatsapp.com" target="_blank"
                                        class="btn btn-outline-success w-100">
                                        <i class="fab fa-whatsapp me-2"></i> Buka WhatsApp Web
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Daftar Peminjam Jatuh Tempo</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>No. WhatsApp</th>
                                        <th>Judul Buku</th>
                                        <th>Tgl Pinjam</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($due_list)): ?>
                                        <?php foreach ($due_list as $item): ?>
                                            <?php
                                            // Calculate Status
                                            $dueDate = new DateTime($item->DueDate);
                                            $todayObj = new DateTime(date('Y-m-d')); // Use todayObj to avoid conflict
                                            $interval = $todayObj->diff($dueDate);
                                            $days = $interval->days;
                                            $isOverdue = $interval->invert; // 1 if today > dueDate
                                            ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo $item->Fullname; ?></td>
                                                <td>
                                                    <span class="badge bg-secondary text-monospace">
                                                        <i class="fab fa-whatsapp"></i> <?php echo $item->NoHp; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $item->Title; ?></td>
                                                <td><small><?php echo $item->LoanDate; ?></small></td>
                                                <td><span
                                                        class="badge bg-light text-dark border"><?php echo $item->DueDate; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($isOverdue): ?>
                                                        <!-- Telat -->
                                                        <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Telat
                                                            <?php echo $days; ?> Hari</span>
                                                    <?php elseif ($days == 0): ?>
                                                        <!-- Hari Ini -->
                                                        <span class="badge bg-warning text-dark"><i
                                                                class="fas fa-exclamation-triangle"></i> Jatuh Tempo Hari Ini</span>
                                                    <?php else: ?>
                                                        <!-- Akan Datang (H-7) -->
                                                        <span class="badge bg-info text-dark"><i class="fas fa-clock"></i>
                                                            H-<?php echo $days; ?> Jatuh Tempo</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success"></i>
                                                Tidak ada peminjam jatuh tempo hari ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Settings -->
                <div class="tab-pane fade" id="settings-pane" role="tabpanel">
                    <h3 class="mb-4 text-dark border-bottom pb-2">Pengaturan</h3>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="<?php echo site_url('dashboard/save_settings'); ?>" method="post">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Token WhatsApp (Fonnte)</label>
                                    <input type="text" name="token" class="form-control"
                                        placeholder="Masukkan Token Fonnte..."
                                        value="<?php echo getenv('FONNTE_TOKEN'); ?>" required>
                                    <div class="form-text">Dapatkan token dari dashboard Fonnte.</div>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan
                                    Token</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab: Help -->
                <div class="tab-pane fade" id="help-pane" role="tabpanel">
                    <h3 class="mb-4 text-dark border-bottom pb-2">Panduan Penggunaan</h3>
                    <div class="accordion" id="accordionHelp">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#c1">
                                    1. Persiapan Awal
                                </button>
                            </h2>
                            <div id="c1" class="accordion-collapse collapse show" data-bs-parent="#accordionHelp">
                                <div class="accordion-body">
                                    Pastikan server lokal (XAMPP) sudah berjalan. Pastikan juga HP yang terhubung ke
                                    Fonnte dalam keadaan aktif dan memiliki koneksi internet.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#c2">
                                    2. Cara Mengirim Reminder
                                </button>
                            </h2>
                            <div id="c2" class="accordion-collapse collapse" data-bs-parent="#accordionHelp">
                                <div class="accordion-body">
                                    1. Masuk ke halaman <strong>Dashboard</strong>.<br>
                                    2. Cek apakah ada notifikasi "Perlu diproses".<br>
                                    3. Klik tombol hijau <strong>"Kirim Reminder"</strong>.<br>
                                    4. Tunggu hingga proses selesai (progress bar penuh).
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#c3">
                                    3. Arti Status Warna
                                </button>
                            </h2>
                            <div id="c3" class="accordion-collapse collapse" data-bs-parent="#accordionHelp">
                                <div class="accordion-body">
                                    <ul>
                                        <li><span class="badge bg-danger">Merah</span> : Anggota telat mengembalikan
                                            buku (Denda).</li>
                                        <li><span class="badge bg-warning text-dark">Kuning</span> : Anggota harus
                                            mengembalikan buku HARI INI.</li>
                                        <li><span class="badge bg-info text-dark">Biru</span> : Belum jatuh tempo
                                            (Reminder H-7).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>

    <!-- Modal Logs -->
    <div class="modal fade" id="logModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-history me-2"></i> Riwayat Aktivitas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 15%">Waktu</th>
                                    <th style="width: 10%">Status</th>
                                    <th>Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($logs)): ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><small class="text-muted"><?php echo $log['time']; ?></small></td>
                                            <td>
                                                <!-- Clickable Status Badge -->
                                                <button type="button"
                                                    class="btn badge bg-<?php echo $log['status']; ?> border-0"
                                                    data-bs-toggle="popover" data-bs-trigger="focus" title="Detail Log"
                                                    data-bs-content="<?php echo htmlspecialchars($log['detail']); ?>">
                                                    <i class="fas fa-<?php echo $log['icon']; ?> me-1"></i>
                                                    <?php echo $log['label']; ?> <i class="fas fa-caret-down ms-1 x-small"></i>
                                                </button>
                                            </td>
                                            <td><?php echo $log['message']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">Belum ada riwayat aktivitas.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <a href="<?php echo site_url('dashboard/clear_logs'); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('⚠️ Yakin ingin menghapus SELURUH riwayat log? Data yang dihapus tidak bisa dikembalikan.');">
                        <i class="fas fa-trash me-1"></i> Bersihkan Log
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Raw Response -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proses Pengiriman...</h5>
                    <!-- Hide close button to force wait -->
                </div>
                <div class="modal-body p-0">
                    <iframe id="responseFrame" style="width: 100%; height: 400px; border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary w-100" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Selesai & Refresh Halaman
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var btnProcess = document.getElementById('btnProcess');
        frame.src = '/reminder-whatsapp/index.php/reminder/process';

        myModal.show();
            });
        }
    </script>

</body>

</html>