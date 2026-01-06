import './bootstrap';
import Swal from 'sweetalert2';

// 1. Buat Swal jadi Global Variable
// Supaya bisa dipanggil langsung di file Blade manapun (contoh: Swal.fire(...))
window.Swal = Swal;

// 2. Definisi Toast (Notifikasi Kecil di Pojok Kanan Atas)
// Digunakan untuk pesan "Berhasil disimpan" atau "Gagal"
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end', // Muncul di pojok kanan atas
    showConfirmButton: false,
    timer: 3000, // Hilang otomatis dalam 3 detik
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

// Masukkan Toast ke window agar bisa dipanggil global
window.Toast = Toast;

// 3. Fungsi Global untuk Konfirmasi (Tombol Manual/Bayar)
// Cara pakai di button: onclick="confirmAction('form-id', 'Pesan konfirmasinya apa')"
window.confirmAction = function(formId, message) {
    Swal.fire({
        title: 'Konfirmasi',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6', // Warna Biru
        cancelButtonColor: '#d33',     // Warna Merah
        confirmButtonText: 'Ya, Lanjutkan!',
        cancelButtonText: 'Batal',
        reverseButtons: true // Tombol Batal di kiri, Ya di kanan
    }).then((result) => {
        if (result.isConfirmed) {
            // Cari form berdasarkan ID lalu submit
            document.getElementById(formId).submit();
        }
    });
};

// 4. Fungsi Global untuk Delete (Tombol Hapus)
// Cara pakai: onclick="deleteConfirm('form-id')"
window.deleteConfirm = function(formId) {
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6e7881',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
};