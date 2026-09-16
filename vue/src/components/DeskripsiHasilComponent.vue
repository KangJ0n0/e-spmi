<template>
  <div>
    <div v-if="mainText" class="text-sm text-gray-700" v-html="formatTeksBernomor(mainText)"></div>
    <a
      v-if="linkBukti"
      :href="linkBukti"
      target="_blank"
      rel="noopener noreferrer"
      class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline break-all"
    >
      <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
      </svg>
      Buka Link Bukti Dokumen
    </a>
  </div>
</template>

<script setup>
// Dulu deskripsi_hasil (gabungan jawaban Auditee + link Gdrive, lihat JawabanController::
// storeAuditee()) ditampilkan mentah sebagai teks biasa di 2 halaman (IsiInstrumenAuditee.vue -
// tampilan "Lihat Hasil" milik Auditee sendiri, dan NilaiInstrumenAuditor.vue - konteks Instrumen
// 2 pas Auditor menilai). Link Gdrive-nya jadi cuma teks polos, harus di-copy-paste manual, nggak
// bisa langsung diklik. Komponen ini motong string di penanda "Link Bukti Dokumen: " yang selalu
// ditambahkan backend (format tetap, lihat storeAuditee()), lalu render sisa link-nya sebagai
// <a> asli (ikon + teks) yang buka tab baru - dipakai bareng di kedua halaman biar konsisten.
import { computed } from 'vue'
// Fitur baru (15 Sep 2026) - lihat catatan lengkap di formatTeksBernomor.js
import { formatTeksBernomor } from '@/utils/formatTeksBernomor'

const props = defineProps({
  text: {
    type: String,
    default: '',
  },
})

const MARKER = '\n\nLink Bukti Dokumen: '

const splitIndex = computed(() => (props.text ? props.text.indexOf(MARKER) : -1))

const mainText = computed(() =>
  splitIndex.value >= 0 ? props.text.slice(0, splitIndex.value) : props.text,
)

const linkBukti = computed(() =>
  splitIndex.value >= 0 ? props.text.slice(splitIndex.value + MARKER.length).trim() : '',
)
</script>
