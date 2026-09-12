<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Pelaksanaan Audit (Isi Instrumen)</h1>
        <p class="text-sm text-gray-500">Pilih butir standar untuk dievaluasi (KS / KTS).</p>
      </div>
      <router-link
        to="/auditor/jadwal"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- TAMPILAN 1: TABEL DAFTAR PERTANYAAN (INSTRUMEN 1) -->
    <div v-if="!modeIsiForm" class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/2">
              Butir Pertanyaan
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
          <tr v-for="(item, index) in listPertanyaan" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ index + 1 }}</td>
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.pertanyaan.butir_pertanyaan }}
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <span
                v-if="!item.jawaban?.deskripsi_hasil"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600"
              >
                Menunggu Auditee
              </span>
              <span
                v-else
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="
                  item.status_jawaban === 'sudah'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700'
                "
              >
                {{ item.status_jawaban === 'sudah' ? 'Selesai Dinilai' : 'Siap Dinilai' }}
              </span>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <button
                v-if="item.jawaban?.deskripsi_hasil && item.status_jawaban === 'belum'"
                @click="bukaFormInstrumen(item)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-medium shadow-sm transition-colors"
              >
                Nilai (KS/KTS)
              </button>
              <button
                v-else
                class="bg-gray-300 text-gray-600 px-3 py-1.5 rounded-md text-xs font-medium cursor-not-allowed"
                disabled
              >
                {{ item.status_jawaban === 'sudah' ? 'Sudah Dinilai' : 'Belum Bisa Dinilai' }}
              </button>
            </td>
          </tr>
          <tr v-if="listPertanyaan.length === 0">
            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
              Tidak ada pertanyaan untuk jadwal ini.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TAMPILAN 2: FORM WIZARD (INSTRUMEN LANJUTAN) -->
    <div
      v-else
      class="max-w-4xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50"
    >
      <!-- Info Soal yang Sedang Dikerjakan -->
      <div class="mb-6 bg-white p-4 border border-blue-100 rounded-md shadow-sm">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">Soal yang sedang dievaluasi:</h3>
        <p class="text-sm text-gray-700 font-medium mb-1">{{ soalAktif.pertanyaan.pertanyaan }}</p>
        <p class="text-sm text-gray-600 whitespace-pre-line mb-3">
          Butir: {{ soalAktif.pertanyaan.butir_pertanyaan }}
        </p>
        <p class="text-xs text-gray-500 bg-gray-100 p-2 rounded">
          <span class="font-semibold">Dokumen Dicek:</span><br />
          {{ soalAktif.pertanyaan.dokumen_cek }}
        </p>
      </div>

      <!-- Jawaban dari Auditee (Instrumen 2, read-only) - konteks buat Auditor sebelum menilai -->
      <div class="mb-6 bg-blue-50 p-4 border border-l-4 border-l-blue-500 rounded-md">
        <h3 class="text-sm font-bold text-blue-800 mb-2">Jawaban Auditee (Deskripsi Hasil):</h3>
        <p class="text-sm text-gray-700 whitespace-pre-line">
          {{ soalAktif.jawaban?.deskripsi_hasil }}
        </p>
      </div>

      <!-- Header Step -->
      <div class="mb-6 border-b pb-4 flex justify-between items-center">
        <h2 class="text-lg font-bold text-gray-800">
          Tahap {{ step }}
          <span v-if="jalur" class="text-blue-600"
            >- Jalur {{ jalur === 'KS' ? 'Kondisi Sesuai' : 'Kondisi Tidak Sesuai' }}</span
          >
        </h2>
        <button @click="batalIsi" class="text-sm text-red-500 hover:text-red-700 font-medium">
          X Batal / Tutup
        </button>
      </div>

      <!-- Form V-IF Logic -->
      <form @submit.prevent="submitJawaban">
        <!-- STEP 1: Keputusan (KTS / KS) - berdasarkan jawaban Auditee di atas, "di luar
             instrumen" sesuai diagram alur ("Auditor menyimpulkan (di luar instrumen): KS atau
             KTS") -->
        <div v-if="step === 1" class="space-y-4 animate-fade-in">
          <p class="text-sm text-gray-600">
            Berdasarkan jawaban Auditee di atas, tentukan apakah kondisinya Sesuai atau Tidak
            Sesuai dengan standar.
          </p>

          <div class="flex gap-4 pt-4">
            <button
              type="button"
              @click="pilihJalur('KS')"
              class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-md font-medium transition-colors"
            >
              Kondisi Sesuai (KS)
            </button>
            <button
              type="button"
              @click="pilihJalur('KTS')"
              class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-md font-medium transition-colors"
            >
              Kondisi Tidak Sesuai (KTS)
            </button>
          </div>
        </div>

        <!-- ============ CABANG KTS (TIDAK SESUAI) ============ -->
        <template v-if="jalur === 'KTS'">
          <div v-if="step === 2" class="space-y-4 animate-fade-in">
            <label class="block text-sm font-semibold text-gray-700">Kategori Temuan</label>
            <select
              v-model="form.kategori_temuan"
              class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="" disabled>Pilih Kategori KTS...</option>
              <option value="OBS">Observasi (OBS)</option>
              <option value="MINOR">Minor</option>
              <option value="MAYOR">Mayor</option>
            </select>

            <label class="block text-sm font-semibold text-gray-700 mt-4">Faktor Penghambat</label>
            <textarea
              v-model="form.faktor_penghambat"
              rows="3"
              class="w-full border border-gray-300 rounded-md p-3 text-sm"
            ></textarea>
          </div>

          <div v-if="step === 3" class="space-y-4 animate-fade-in">
            <label class="block text-sm font-semibold text-gray-700">Rekomendasi Perbaikan</label>
            <textarea
              v-model="form.rekomendasi"
              rows="4"
              class="w-full border border-gray-300 rounded-md p-3 text-sm"
            ></textarea>
          </div>

          <div v-if="step === 4" class="space-y-4 animate-fade-in">
            <label class="block text-sm font-semibold text-gray-700"
              >Rencana Perbaikan & Jadwal</label
            >
            <textarea
              v-model="form.rencana_perbaikan"
              rows="2"
              placeholder="Rencana..."
              class="w-full border border-gray-300 rounded-md p-3 text-sm mb-3"
            ></textarea>

            <input
              type="text"
              v-model="form.jadwal_penyelesaian"
              placeholder="Jadwal (Contoh: September 2026)"
              class="w-full border border-gray-300 rounded-md p-3 text-sm mb-3"
            />

            <input
              type="text"
              v-model="form.pihak_tanggung_jawab"
              placeholder="Penanggung Jawab (Contoh: Dekan)"
              class="w-full border border-gray-300 rounded-md p-3 text-sm"
            />
          </div>
        </template>

        <!-- ============ CABANG KS (SESUAI) ============ -->
        <template v-if="jalur === 'KS'">
          <div v-if="step === 2" class="space-y-4 animate-fade-in">
            <label class="block text-sm font-semibold text-gray-700">Faktor Pendukung</label>
            <textarea
              v-model="form.faktor_pendukung"
              rows="4"
              class="w-full border border-gray-300 rounded-md p-3 text-sm"
            ></textarea>
          </div>

          <div v-if="step === 3" class="space-y-4 animate-fade-in">
            <label class="block text-sm font-semibold text-gray-700">Rencana Peningkatan</label>
            <textarea
              v-model="form.rencana_peningkatan"
              rows="4"
              class="w-full border border-gray-300 rounded-md p-3 text-sm"
            ></textarea>
          </div>
        </template>

        <!-- TOMBOL NAVIGASI NEXT/PREV/SUBMIT -->
        <div
          v-if="step > 1"
          class="flex justify-between items-center mt-8 pt-4 border-t border-gray-200"
        >
          <button
            type="button"
            @click="prevStep"
            class="px-5 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100"
          >
            &larr; Sebelumnya
          </button>

          <button
            v-if="!isLastStep"
            type="button"
            @click="nextStep"
            class="px-5 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700"
          >
            Selanjutnya &rarr;
          </button>

          <button
            v-if="isLastStep"
            type="submit"
            :disabled="isSubmitting"
            class="px-5 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Instrumen' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'

const route = useRoute()
const listPertanyaan = ref([])
const modeIsiForm = ref(false)
const soalAktif = ref(null)
const isSubmitting = ref(false)

// State WIZARD Form
const step = ref(1)
const jalur = ref(null) // 'KS' atau 'KTS'
const form = reactive({
  jadwal_spmi_id: route.params.id,
  pertanyaan_id: '',
  status_temuan: '',
  // deskripsi_hasil TIDAK ada di sini - itu jawaban Auditee (Instrumen 2), Auditor cuma baca,
  // nggak menimpanya. Lihat box "Jawaban Auditee" di atas.
  faktor_pendukung: '',
  rencana_peningkatan: '',
  kategori_temuan: '',
  faktor_penghambat: '',
  rekomendasi: '',
  rencana_perbaikan: '',
  jadwal_penyelesaian: '',
  pihak_tanggung_jawab: '',
})

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

// 1. Fetch Daftar Pertanyaan
const fetchListPertanyaan = async () => {
  // API ini mengambil data dari list_pertanyaans, termasuk relasi `jawaban` (jawaban Auditee +
  // hasil penilaian Auditor kalau sudah ada)
  const response = await axiosClient.get(`/jadwal-audit/${route.params.id}/pertanyaan`)
  if (gagal(response)) return
  listPertanyaan.value = response.data
}

// 2. Buka Modal / Form untuk satu pertanyaan
const bukaFormInstrumen = (item) => {
  soalAktif.value = item
  form.pertanyaan_id = item.pertanyaan_id // ID dari bank pertanyaan
  modeIsiForm.value = true
  step.value = 1
  jalur.value = null

  // Reset sisa form
  Object.keys(form).forEach((key) => {
    if (key !== 'jadwal_spmi_id' && key !== 'pertanyaan_id') form[key] = ''
  })
}

const batalIsi = () => {
  if (confirm('Yakin ingin membatalkan pengisian? Data yang diketik akan hilang.')) {
    modeIsiForm.value = false
  }
}

// 3. Logika Next & Prev Form
const pilihJalur = (pilihan) => {
  jalur.value = pilihan
  form.status_temuan = pilihan
  step.value = 2
}

const nextStep = () => step.value++
const prevStep = () => {
  if (step.value === 2) {
    jalur.value = null
    form.status_temuan = ''
  }
  step.value--
}

const isLastStep = computed(() => {
  return (jalur.value === 'KS' && step.value === 3) || (jalur.value === 'KTS' && step.value === 4)
})

// 4. Submit Payload ke Backend
const submitJawaban = async () => {
  isSubmitting.value = true
  // Memanggil API store JawabanController yang menerapkan validasi Carbon dari Mas Ezekiel
  const res = await axiosClient.post('/jawaban/store', form)
  isSubmitting.value = false

  if (gagal(res)) {
    // Toast error sudah ditampilkan otomatis oleh interceptor - di sini cukup jangan lanjut.
    return
  }

  // Toast sukses juga sudah otomatis dari interceptor axios.js (backend balikin `message`) -
  // dulu ada alert() manual duplikat di sini (dirapikan 10 Sep, lihat src/utils/notify.js).
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
