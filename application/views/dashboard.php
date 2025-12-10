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
        }

        .card-stat {
            border-left: 5px solid #0d6efd;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fab fa-whatsapp"></i> Inlislite Reminder</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Stats Card -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm card-stat h-100">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Status Hari Ini (<?php echo date('d M Y'); ?>)</h5>
                        <div class="d-flex align-items-center mt-3">
                            <div class="display-4 fw-bold text-primary me-3"><?php echo $count_due; ?></div>
                            <div>
                                <h6 class="mb-0">Anggota Terlambat</h6>
                                <small class="text-muted">Perlu diingatkan</small>
                            </div>
                        </div>

                        <hr>

                        <?php if ($count_due > 0): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Ada <strong><?php echo $count_due; ?></strong>
                                peminjam yang jatuh tempo.
                            </div>
                            <button id="btnProcess" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-paper-plane"></i> Kirim Pesan Reminder
                            </button>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Tidak ada yang terlambat hari ini.
                            </div>
                            <button class="btn btn-secondary w-100 btn-lg" disabled>Tidak Ada Pesan</button>
                        <?php endif; ?>

                        <div id="progressArea" class="mt-3 d-none">
                            <label>Mengirim pesan...</label>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 100%"></div>
                            </div>
                            <small class="text-muted text-center d-block mt-1">Mohon jangan tutup halaman ini.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log History -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($logs)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($logs as $log): ?>
                                    <li class="list-group-item text-monospace small"><?php echo $log; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted text-center my-4">Belum ada riwayat pengiriman.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Due List Table -->
        <?php if (!empty($due_list)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Daftar Peminjam Terlambat / Jatuh Tempo</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Anggota</th>
                                        <th>Judul Buku</th>
                                        <th>Tanggal Jatuh Tempo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($due_list as $item): ?>
                                        <tr>
                                            <td><?php echo $item->Fullname; ?></td>
                                            <td><?php echo $item->Title; ?></td>
                                            <td><span class="badge bg-danger"><?php echo $item->DueDate; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Modal Raw Response -->
    <div class="modal fade" id="responseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hasil Pengiriman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe id="responseFrame" style="width: 100%; height: 400px; border: none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="location.reload()">Tutup & Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('btnProcess').addEventListener('click', function () {
            if (!confirm('Apakah Anda yakin ingin mengirim pesan WhatsApp ke <?php echo $count_due; ?> anggota?')) return;

            // UI State
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sedang Memproses...';
            document.getElementById('progressArea').classList.remove('d-none');

            // Show Modal with Iframe
            var myModal = new bootstrap.Modal(document.getElementById('responseModal'), {
                backdrop: 'static',
                keyboard: false
            });

            var frame = document.getElementById('responseFrame');
            // Gunakan path absolut relatif terhadap root domain untuk menghindari masalah site_url
            // Jika folder project beda (misal /inlislite-reminder/), sesuaikan di sini.
            // Asumsi: di localhost/reminder-whatsapp/
            frame.src = '/reminder-whatsapp/index.php/reminder/process';

            myModal.show();

            // Listen for iframe load (simple completion check)
            frame.onload = function () {
                // Optional: You could read contents if same origin, but for now just showing it is fine
            };
        });
    </script>

</body>

</html>