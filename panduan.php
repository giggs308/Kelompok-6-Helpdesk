<?php
require_once 'config/config.php';

$pageTitle = "Panduan Penggunaan";
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-book me-2"></i>Panduan Penggunaan Helpdesk</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="text-primary"><i class="fas fa-ticket-alt me-2"></i>Untuk Pengguna</h5>
                        <div class="ms-4">
                            <h6>1. Membuat Tiket Baru</h6>
                            <ol>
                                <li>Klik menu <strong>Buat Tiket Baru</strong></li>
                                <li>Isi formulir yang tersedia dengan lengkap</li>
                                <li>Klik tombol <strong>Buat Tiket</strong></li>
                            </ol>
                            
                            <h6>2. Melihat Daftar Tiket</h6>
                            <ul>
                                <li>Semua tiket yang Anda buat dapat dilihat di menu <strong>Daftar Tiket Saya</strong></li>
                                <li>Anda dapat melihat status tiket (Open, Diproses, Selesai)</li>
                                <li>Klik judul tiket untuk melihat detail lengkap</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="text-primary"><i class="fas fa-user-shield me-2"></i>Untuk Admin</h5>
                        <div class="ms-4">
                            <h6>1. Mengelola Tiket</h6>
                            <ul>
                                <li>Semua tiket dapat dikelola di halaman <strong>Kelola Tiket</strong></li>
                                <li>Anda dapat mengubah status tiket sesuai perkembangan</li>
                                <li>Gunakan filter untuk memudahkan pencarian tiket</li>
                            </ul>
                            
                            <h6>2. Ekspor Data</h6>
                            <ul>
                                <li>Klik tombol <strong>Ekspor ke Excel</strong> untuk mendownload data tiket</li>
                                <li>Data akan didownload dalam format Excel (.xls)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Bantuan Tambahan</h6>
                        <p class="mb-0">Jika Anda mengalami kesulitan, silakan hubungi tim IT support di <a href="mailto:support@example.com">support@example.com</a> atau hubungi nomor (021) 12345678.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
