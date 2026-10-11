<template>
  <!-- Label jadwal di atas judul halaman (12 Okt 2026, permintaan LPMU/user): judul = nama jadwal
       + area (mis. "Audit Mutu Internal Elektro"), di bawahnya tanggal pelaksanaan. Semester tidak
       ditampilkan. Dipakai bareng di semua halaman yang tadinya harus memilih jadwal dulu, biar
       pengguna tahu sedang berada di jadwal mana. -->
  <div v-if="jadwal" class="mb-3">
    <p class="text-base font-semibold text-gray-800">
      {{ [jadwal.nama_jadwal, jadwal.area_audit].filter(Boolean).join(' ') }}
    </p>
    <p v-if="jadwal.tanggal_awal && jadwal.tanggal_akhir" class="text-sm text-gray-500">
      Pelaksanaan {{ formatTanggal(jadwal.tanggal_awal) }} sampai
      {{ formatTanggal(jadwal.tanggal_akhir) }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  jadwal: {
    type: Object,
    default: null,
  },
})

// Tanggal dari backend bentuknya "YYYY-MM-DD" (kadang ISO penuh) -> dipotong 10 karakter pertama
// lalu dibentuk sebagai tanggal lokal, supaya tidak bergeser sehari karena zona waktu.
const formatTanggal = (nilai) => {
  const [y, m, d] = String(nilai).slice(0, 10).split('-').map(Number)
  if (!y || !m || !d) return String(nilai)
  return new Date(y, m - 1, d).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}
</script>
