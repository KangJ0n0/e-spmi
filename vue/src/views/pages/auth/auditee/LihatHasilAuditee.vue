<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Lihat Hasil Evaluasi</h1>
        <p class="text-sm text-gray-500">
          Cek jawaban yang sudah Anda kirim beserta hasil penilaian Auditor (KS/KTS).
        </p>
      </div>
      <router-link
        to="/auditee/jadwal-auditee"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- TAMPILAN 1: TABEL DAFTAR PERTANYAAN (read-only, semua status) -->
    <div v-if="!soalAktif" class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">
              Butir Standar
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Status
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="isLoading">
            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Memuat...</td>
          </tr>
          <tr v-else-if="listPertanyaan.length === 0">
            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
              Belum ada pertanyaan yang dikirim Auditor untuk jadwal ini.
            </td>
          </tr>
          <tr
            v-for="(item, index) in listPertanyaan"
            :key="item.id"
            v-else
            class="hover:bg-gray-50"
          >
            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ index + 1 }}</td>
            <td class="px-4 py-3 text-sm text-gray-800">
              <p class="font-medium whitespace-pre-line">
                {{ item.pertanyaan?.butir_pertanyaan }}
              </p>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <span
                v-if="!item.jawaban?.deskripsi_hasil"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500"
              >
                Belum Diisi
              </span>
              <span
                v-else-if="item.jawaban?.status_temuan"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700"
              >
                Sudah Dinilai ({{ item.jawaban.status_temuan }})
              </span>
              <span
                v-else
                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700"
              >
                Menunggu Penilaian Auditor
              </span>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <button
                v-if="item.jawaban?.deskripsi_hasil"
                @click="soalAktif = item"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-300"
              >
                Lihat Detail
              </button>
              <router-link
                v-else
                :to="{ path: '/auditee/instrumen-auditee', query: { id: jadwalId } }"
                class="text-xs font-medium text-blue-600 hover:text-blue-800"
              >
                Isi jawaban dulu
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TAMPILAN 2: DETAIL HASIL (read-only) - jawaban Auditee sendiri + hasil penilaian Auditor
         (KS/KTS) kalau sudah dinilai. Markup ini sengaja mirroring "Lihat Hasil" milik Auditor di
         NilaiInstrumenAuditor.vue (dipindah dari IsiInstrumenAuditee.vue 10 Sep, biar nggak numpuk
         di halaman yang sama dengan form Jawab Pertanyaan - dilaporkan user). -->
    <div
      v-else
      class="max-w-3xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <!-- Konteks: Instrumen 1 (butir soal) -->
      <div class="mb-6 bg-white p-4 border border-blue-100 rounded-md shadow-sm">
        <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[10px] shrink-0">1</span>
          Butir Soal
        </h3>
        <p class="text-sm text-gray-700 font-medium mb-1 whitespace-pre-line">{{ soalAktif.pertanyaan?.pertanyaan }}</p>
        <p class="text-sm text-gray-600 whitespace-pre-line">
          Butir: {{ soalAktif.pertanyaan?.butir_pertanyaan }}
        </p>
      </div>

      <!-- Jawaban Auditee sendiri (Instrumen 2), read-only -->
      <div class="mb-6 bg-blue-50 p-4 border border-l-4 border-l-blue-500 rounded-md">
        <h3 class="flex items-center gap-2 text-xs font-bold text-blue-800 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] shrink-0">2</span>
          Jawaban &amp; Bukti Dokumen Anda
        </h3>
        <DeskripsiHasilComponent :text="soalAktif.jawaban?.deskripsi_hasil" />
      </div>

      <!-- Belum dinilai Auditor -->
      <div
        v-if="!soalAktif.jawaban?.status_temuan"
        class="bg-white p-5 border border-dashed border-gray-300 rounded-md text-center"
      >
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700">
          Menunggu Penilaian Auditor
        </span>
        <p class="text-sm text-gray-500 mt-2">
          Jawaban Anda sudah terkirim, Auditor belum menentukan status KS/KTS-nya.
        </p>
      </div>

      <!-- Sudah dinilai Auditor: tampilkan hasil KS/KTS + rekomendasi/rencana tindak lanjut. -->
      <div v-else class="bg-white p-5 border border-gray-200 rounded-md">
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
          <span
            class="px-3 py-1 rounded-full text-sm font-bold"
            :class="
              soalAktif.jawaban?.status_temuan === 'KS'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700'
            "
          >
            {{ soalAktif.jawaban?.status_temuan }}
          </span>
          <h3 class="text-sm font-bold text-gray-700">Hasil Penilaian Auditor</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <template v-if="soalAktif.jawaban?.status_temuan === 'KS'">
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Faktor Pendukung
              </p>
              <p class="text-sm text-gray-700 whitespace-pre-line">
                {{ soalAktif.jawaban?.faktor_pendukung }}
              </p>
            </div>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Rencana Peningkatan
              </p>
              <p class="text-sm text-gray-700 whitespace-pre-line">
                {{ soalAktif.jawaban?.rencana_peningkatan }}
              </p>
            </div>
          </template>
          <template v-else>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Kategori Temuan
              </p>
              <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.kategori_temuan }}</p>
            </div>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Akar Penyebab / Faktor Penghambat
              </p>
              <p class="text-sm text-gray-700 whitespace-pre-line">
                {{ soalAktif.jawaban?.faktor_penghambat }}
              </p>
            </div>
            <div class="bg-gray-50 rounded-md p-3 sm:col-span-2">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Rencana Perbaikan
              </p>
              <p class="text-sm text-gray-700 whitespace-pre-line">
                {{ soalAktif.jawaban?.rencana_perbaikan }}
              </p>
            </div>
          </template>

          <div class="bg-gray-50 rounded-md p-3 sm:col-span-2">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">Rekomendasi</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">
              {{ soalAktif.jawaban?.rekomendasi }}
            </p>
          </div>
          <div class="bg-gray-50 rounded-md p-3">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
              Jadwal Penyelesaian
            </p>
            <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.jadwal_penyelesaian }}</p>
          </div>
          <div class="bg-gray-50 rounded-md p-3">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
              Pihak Bertanggung Jawab
            </p>
            <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.pihak_tanggung_jawab }}</p>
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
        <button
          type="button"
          @click="soalAktif = null"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100"
        >
          Kembali ke Daftar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'
import DeskripsiHasilComponent from '@/components/DeskripsiHasilComponent.vue'

const route = useRoute()
const jadwalId = route.query.id

const listPertanyaan = ref([])
const soalAktif = ref(null)
const isLoading = ref(false)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const fetchListPertanyaan = async () => {
  isLoading.value = true
  const res = await axiosClient.get(`/jadwal-audit/${jadwalId}/pertanyaan`)
  isLoading.value = false
  if (gagal(res)) return
  listPertanyaan.value = res.data
}

onMounted(() => {
  fetchListPertanyaan()
})
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
