<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Evaluasi Diri (Auditee)</h1>
        <p class="text-sm text-gray-500">
          Isi capaian riil dan lampirkan bukti dokumen sesuai instrumen.
        </p>
      </div>
      <router-link
        to="/auditee/jadwal"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- TAMPILAN 1: TABEL DAFTAR INSTRUMEN (SOAL) -->
    <div v-if="!modeIsiForm" class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">
              Butir Standar & Dokumen
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Status Pengisian
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, index) in listPertanyaan" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ index + 1 }}</td>
            <td class="px-4 py-3 text-sm text-gray-800">
              <p class="font-medium mb-1">{{ item.pertanyaan.butir_pertanyaan }}</p>
              <p class="text-xs text-gray-500 bg-gray-100 p-1 rounded inline-block">
                Bukti: {{ item.pertanyaan.dokumen_cek }}
              </p>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <span
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="
                  item.status_jawaban_auditee === 'sudah'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
                "
              >
                {{ item.status_jawaban_auditee === 'sudah' ? 'Selesai Diisi' : 'Belum Diisi' }}
              </span>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <button
                v-if="item.status_jawaban_auditee !== 'sudah'"
                @click="bukaForm(item)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-medium shadow-sm"
              >
                Isi Evaluasi
              </button>
              <button
                v-else
                class="bg-gray-300 text-gray-600 px-3 py-1.5 rounded-md text-xs font-medium cursor-not-allowed"
                disabled
              >
                Menunggu Auditor
              </button>
            </td>
          </tr>
          <tr v-if="listPertanyaan.length === 0">
            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
              Belum ada instrumen yang dikirim untuk Anda.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TAMPILAN 2: FORM PENGISIAN AUDITEE (INSTRUMEN 1) -->
    <div
      v-else
      class="max-w-3xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <!-- Box Info Pertanyaan -->
      <div class="mb-6 bg-white p-5 border border-l-4 border-l-blue-500 rounded-md shadow-sm">
        <h3 class="text-sm font-bold text-blue-800 mb-2">Pernyataan Standar:</h3>
        <p class="text-sm text-gray-700 font-medium mb-3">{{ soalAktif.pertanyaan.pertanyaan }}</p>

        <h3 class="text-sm font-bold text-blue-800 mb-1">Butir Pertanyaan:</h3>
        <p class="text-sm text-gray-700 mb-3 whitespace-pre-line">
          {{ soalAktif.pertanyaan.butir_pertanyaan }}
        </p>

        <h3 class="text-sm font-bold text-blue-800 mb-1">Dokumen yang Harus Disiapkan:</h3>
        <p class="text-sm text-gray-700 whitespace-pre-line">
          {{ soalAktif.pertanyaan.dokumen_cek }}
        </p>
      </div>

      <!-- Form Input Auditee -->
      <form @submit.prevent="submitEvaluasiAuditee">
        <div class="space-y-5">
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1"
              >Kondisi / Capaian Saat Ini <span class="text-red-500">*</span></label
            >
            <p class="text-xs text-gray-500 mb-2">
              Jelaskan secara singkat bagaimana kondisi riil pemenuhan standar ini di unit Anda.
            </p>
            <textarea
              v-model="form.capaian_auditee"
              rows="4"
              placeholder="Contoh: Kami telah melaksanakan perkuliahan 14 kali pertemuan sesuai RPS..."
              class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
              required
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-1"
              >Tautan Bukti Dokumen <span class="text-red-500">*</span></label
            >
            <p class="text-xs text-gray-500 mb-2">
              Masukkan link Google Drive / Cloud yang berisi dokumen yang diminta.
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
            {{ isSubmitting ? 'Menyimpan...' : 'Kirim Evaluasi Diri' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'

const route = useRoute()
const listPertanyaan = ref([])
const modeIsiForm = ref(false)
const soalAktif = ref(null)
const isSubmitting = ref(false)

const form = reactive({
  jadwal_spmi_id: route.query.id,
  pertanyaan_id: '',
  capaian_auditee: '',
  link_bukti: '',
})

// 1. Ambil Data Pertanyaan
const fetchListPertanyaan = async () => {
  try {
    // API memanggil list soal yang sudah dirilis auditor untuk jadwal ini
    const response = await axiosClient.get(`/auditee/jadwal/${route.query.id}/pertanyaan`)
    listPertanyaan.value = response.data
  } catch (error) {
    console.error('Gagal memuat soal:', error)
  }
}

// 2. Buka Form
const bukaForm = (item) => {
  soalAktif.value = item
  form.pertanyaan_id = item.pertanyaan_id
  form.capaian_auditee = ''
  form.link_bukti = ''
  modeIsiForm.value = true
}

const batalIsi = () => {
  modeIsiForm.value = false
  soalAktif.value = null
}

// 3. Submit Jawaban Auditee (Instrumen 1)
const submitEvaluasiAuditee = async () => {
  isSubmitting.value = true
  try {
    // Post jawaban awal auditee ke backend
    await axiosClient.post('/auditee/jawaban/store', form)
    alert('Evaluasi diri dan dokumen bukti berhasil dikirim ke Auditor!')
    modeIsiForm.value = false
    await fetchListPertanyaan()
  } catch (error) {
    alert(error.response?.data?.error || 'Terjadi kesalahan saat menyimpan data.')
  } finally {
    isSubmitting.value = false
  }
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
