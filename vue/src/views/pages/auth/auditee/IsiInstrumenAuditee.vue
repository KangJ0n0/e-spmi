<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Evaluasi Diri (Auditee)</h1>
        <p class="text-sm text-gray-500">
          Isi jawaban dan lampirkan link bukti dokumen (Google Drive) untuk tiap butir
          pertanyaan.
        </p>
      </div>
      <router-link
        to="/auditee/jadwal-auditee"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- TAMPILAN 1: TABEL DAFTAR PERTANYAAN -->
    <div v-if="!modeIsiForm && !modeLihatHasil" class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">
              Butir Standar &amp; Dokumen
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
              <p class="font-medium mb-1 whitespace-pre-line">
                {{ item.pertanyaan?.butir_pertanyaan }}
              </p>
              <p
                v-if="item.pertanyaan?.dokumen_cek"
                class="text-xs text-gray-500 bg-gray-100 p-1 rounded inline-block"
              >
                Bukti: {{ item.pertanyaan.dokumen_cek }}
              </p>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <span
                v-if="!item.jawaban?.deskripsi_hasil"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700"
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
                v-if="!item.jawaban?.deskripsi_hasil"
                @click="bukaForm(item)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-medium shadow-sm"
              >
                Isi Jawaban
              </button>
              <button
                v-else
                @click="bukaLihatHasil(item)"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-300"
              >
                Lihat Hasil
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TAMPILAN 2: FORM PENGISIAN AUDITEE -->
    <div
      v-else-if="modeIsiForm"
      class="max-w-3xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <!-- Box Info Pertanyaan -->
      <div class="mb-6 bg-white p-5 border border-l-4 border-l-blue-500 rounded-md shadow-sm">
        <h3 class="text-sm font-bold text-blue-800 mb-2">Pernyataan Standar:</h3>
        <p class="text-sm text-gray-700 font-medium mb-3">{{ soalAktif.pertanyaan?.pertanyaan }}</p>

        <h3 class="text-sm font-bold text-blue-800 mb-1">Butir Pertanyaan:</h3>
        <p class="text-sm text-gray-700 mb-3 whitespace-pre-line">
          {{ soalAktif.pertanyaan?.butir_pertanyaan }}
        </p>

        <h3 class="text-sm font-bold text-blue-800 mb-1">Dokumen yang Harus Disiapkan:</h3>
        <p class="text-sm text-gray-700 whitespace-pre-line">
          {{ soalAktif.pertanyaan?.dokumen_cek }}
        </p>
      </div>

      <!-- Form Input Auditee -->
      <form @submit.prevent="submitJawaban">
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1"
              >Jawaban / Kondisi Saat Ini <span class="text-red-500">*</span></label
            >
            <p class="text-xs text-gray-500 mb-2">
              Jelaskan kondisi riil pemenuhan standar ini di unit Anda.
            </p>
            <textarea
              v-model="form.jawaban"
              rows="5"
              placeholder="Contoh: Kami telah melaksanakan perkuliahan 14 kali pertemuan sesuai RPS..."
              class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
              required
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1"
              >Link Bukti Dokumen (Google Drive) <span class="text-red-500">*</span></label
            >
            <p class="text-xs text-gray-500 mb-2">
              Masukkan link Google Drive / Cloud yang berisi dokumen bukti yang diminta.
            </p>
            <input
              type="url"
              v-model="form.link_bukti"
              placeholder="https://drive.google.com/drive/folders/..."
              class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="batalIsi"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100 text-gray-700"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-5 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Kirim ke Auditor' }}
          </button>
        </div>
      </form>
    </div>

    <!-- TAMPILAN 3: LIHAT HASIL (read-only) - jawaban Auditee sendiri + hasil penilaian Auditor
         (KS/KTS) kalau sudah dinilai. -->
    <div
      v-else-if="modeLihatHasil"
      class="max-w-3xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <!-- Konteks: Instrumen 1 (butir soal) -->
      <div class="mb-6 bg-white p-4 border border-blue-100 rounded-md shadow-sm">
        <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[10px] shrink-0">1</span>
          Butir Soal
        </h3>
        <p class="text-sm text-gray-700 font-medium mb-1">{{ soalAktif.pertanyaan?.pertanyaan }}</p>
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
        <p class="text-sm text-gray-700 whitespace-pre-line">
          {{ soalAktif.jawaban?.deskripsi_hasil }}
        </p>
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
          @click="tutupLihatHasil"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'

const route = useRoute()
const jadwalId = route.query.id

const listPertanyaan = ref([])
const modeIsiForm = ref(false)
const modeLihatHasil = ref(false)
const soalAktif = ref(null)
const isLoading = ref(false)
const isSubmitting = ref(false)

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const form = reactive({
  jadwal_spmi_id: jadwalId,
  pertanyaan_id: '',
  jawaban: '',
  link_bukti: '',
})

const fetchListPertanyaan = async () => {
  isLoading.value = true
  const res = await axiosClient.get(`/jadwal-audit/${jadwalId}/pertanyaan`)
  isLoading.value = false
  if (gagal(res)) return
  listPertanyaan.value = res.data
}

const bukaForm = (item) => {
  soalAktif.value = item
  form.pertanyaan_id = item.pertanyaan_id
  form.jawaban = ''
  form.link_bukti = ''
  modeIsiForm.value = true
}

const batalIsi = () => {
  modeIsiForm.value = false
  soalAktif.value = null
}

// "Lihat Hasil" - buka tampilan read-only jawaban sendiri + status KS/KTS (kalau sudah dinilai
// Auditor). Dipanggil dari tombol yang dulunya disabled "Terkunci".
const bukaLihatHasil = (item) => {
  soalAktif.value = item
  modeLihatHasil.value = true
}

const tutupLihatHasil = () => {
  modeLihatHasil.value = false
  soalAktif.value = null
}

const submitJawaban = async () => {
  isSubmitting.value = true
  const res = await axiosClient.post('/auditee/jawaban/store', form)
  isSubmitting.value = false
  if (gagal(res)) return

  alert('Jawaban dan bukti dokumen berhasil dikirim ke Auditor!')
  modeIsiForm.value = false
  await fetchListPertanyaan()
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
