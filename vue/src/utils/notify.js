// Helper notifikasi terpusat (10 Sep 2026) — satu-satunya tempat yang boleh manggil
// `toast(...)` dari vue3-toastify buat notifikasi manual di komponen. Dibuat karena sebelumnya
// notifikasi "manual" (di luar toast otomatis dari interceptor axios.js) nyebar jadi 3 gaya beda
// (alert() bawaan browser, toast.success() langsung, dan campuran) di banyak file — bikin
// tampilan & durasi nggak konsisten antar halaman. Semua pemanggilan manual sekarang wajib lewat
// notifySuccess/notifyError di file ini, BUKAN import `toast` langsung ke komponen.
//
// Catatan: toast SUKSES/ERROR otomatis dari response API (baca `response.data.message` /
// `error.response.data.message`) tetap ditangani interceptor di axios.js seperti biasa — helper
// ini cuma buat kasus manual di komponen (validasi form sebelum submit, konfirmasi aksi lokal,
// dll) yang dulu pakai alert()/toast.success() langsung.
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

export const notifySuccess = (pesan) => {
  toast.success(pesan, { theme: 'auto', autoClose: 3000 })
}

export const notifyError = (pesan) => {
  toast.error(pesan, { theme: 'auto', autoClose: 4000 })
}
