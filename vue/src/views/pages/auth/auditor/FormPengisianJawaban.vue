<template>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Progress Indicator -->
    <div class="mb-6 border-b pb-4">
      <h2 class="text-xl font-bold text-gray-800">Form Evaluasi Diri (Instrumen)</h2>
      <p class="text-sm text-gray-500 mt-1">
        Langkah {{ step }}
        <span v-if="jalur">
          - Jalur {{ jalur === 'KS' ? 'Sesuai (KS)' : 'Tidak Sesuai (KTS)' }}</span
        >
      </p>
    </div>

    <!-- FORM WIZARD -->
    <form @submit.prevent="submitJawaban">
      <!-- ================= STEP 1: DESKRIPSI HASIL ================= -->
      <div v-show="step === 1" class="space-y-4 animate-fade-in">
        <label class="block text-sm font-semibold text-gray-700">Deskripsi Hasil Observasi</label>
        <textarea
          v-model="form.deskripsi_hasil"
          rows="4"
          placeholder="Tuliskan kondisi riil di lapangan..."
          class="w-full border border-gray-300 rounded-md p-3 focus:ring-blue-500 focus:border-blue-500 text-sm"
          required
        ></textarea>

        <div class="flex gap-4 pt-4 border-t border-gray-100">
          <button
            type="button"
            @click="pilihJalur('KS')"
            :disabled="!form.deskripsi_hasil"
            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-md font-medium transition-colors disabled:opacity-50"
          >
            Sesuai Standar (KS)
          </button>
          <button
            type="button"
            @click="pilihJalur('KTS')"
            :disabled="!form.deskripsi_hasil"
            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-md font-medium transition-colors disabled:opacity-50"
          >
            Tidak Sesuai (KTS)
          </button>
        </div>
      </div>

      <!-- ================= JALUR KTS (TIDAK SESUAI) ================= -->
      <template v-if="jalur === 'KTS'">
        <!-- Step 2: Kategori Temuan -->
        <div v-show="step === 2" class="space-y-4">
          <label class="block text-sm font-semibold text-gray-700">Kategori Temuan</label>
          <select
            v-model="form.kategori_temuan"
            class="w-full border border-gray-300 rounded-md p-3 text-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="" disabled>Pilih Kategori...</option>
            <option value="OBS">Observasi (OBS)</option>
            <option value="MINOR">Minor</option>
            <option value="MAYOR">Mayor</option>
          </select>
        </div>

        <!-- Step 3: Faktor Penghambat -->
        <div v-show="step === 3" class="space-y-4">
          <label class="block text-sm font-semibold text-gray-700">Faktor Penghambat</label>
          <textarea
            v-model="form.faktor_penghambat"
            rows="4"
            class="w-full border border-gray-300 rounded-md p-3 text-sm"
          ></textarea>
        </div>

        <!-- Step 4: Rekomendasi -->
        <div v-show="step === 4" class="space-y-4">
          <label class="block text-sm font-semibold text-gray-700">Rekomendasi Perbaikan</label>
          <textarea
            v-model="form.rekomendasi"
            rows="4"
            class="w-full border border-gray-300 rounded-md p-3 text-sm"
          ></textarea>
        </div>

        <!-- Step 5: Rencana, Jadwal & Penanggung Jawab -->
        <div v-show="step === 5" class="space-y-4">
          <label class="block text-sm font-semibold text-gray-700">Rencana Perbaikan</label>
          <textarea
            v-model="form.rencana_perbaikan"
            rows="3"
            class="w-full border border-gray-300 rounded-md p-3 text-sm mb-4"
          ></textarea>

          <label class="block text-sm font-semibold text-gray-700">Jadwal Penyelesaian</label>
          <input
            type="text"
            v-model="form.jadwal_penyelesaian"
            placeholder="Contoh: Akhir Semester Genap 2026"
            class="w-full border border-gray-300 rounded-md p-3 text-sm mb-4"
          />

          <label class="block text-sm font-semibold text-gray-700">Pihak Bertanggung Jawab</label>
          <input
            type="text"
            v-model="form.pihak_tanggung_jawab"
            placeholder="Contoh: Kaprodi Informatika"
            class="w-full border border-gray-300 rounded-md p-3 text-sm"
          />
        </div>
      </template>

      <!-- ================= JALUR KS (SESUAI) ================= -->
      <template v-if="jalur === 'KS'">
        <!-- Step 2: Faktor Pendukung -->
        <div v-show="step === 2" class="space-y-4">
          <label class="block text-sm font-semibold text-gray-700">Faktor Pendukung</label>
          <textarea
            v-model="form.faktor_pendukung"
            rows="4"
            class="w-full border border-gray-300 rounded-md p-3 text-sm"
          ></textarea>
        </div>

        <!-- Step 3: Rencana Peningkatan -->
        <div v-show="step === 3" class="space-y-4">
          <label class="block text-sm font-semibold text-gray-700"
            >Rencana Peningkatan ke Depan</label
          >
          <textarea
            v-model="form.rencana_peningkatan"
            rows="4"
            class="w-full border border-gray-300 rounded-md p-3 text-sm"
          ></textarea>
        </div>
      </template>

      <!-- ================= NAVIGATION BUTTONS ================= -->
      <div
        v-if="step > 1"
        class="flex justify-between items-center mt-8 pt-4 border-t border-gray-100"
      >
        <button
          type="button"
          @click="prevStep"
          class="px-5 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50"
        >
          &larr; Kembali
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
          class="px-5 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700"
        >
          Simpan Jawaban
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

// Anda bisa passing pertanyaan_id ini via props dari komponen Parent (Tabel Soal)
const props = defineProps({
  pertanyaanId: { type: String, required: true },
})

const step = ref(1)
const jalur = ref(null) // 'KS' atau 'KTS'

const form = reactive({
  pertanyaan_id: props.pertanyaanId,
  status_temuan: '',
  deskripsi_hasil: '',
  faktor_pendukung: '',
  rencana_peningkatan: '',
  kategori_temuan: '',
  faktor_penghambat: '',
  rekomendasi: '',
  rencana_perbaikan: '',
  jadwal_penyelesaian: '',
  pihak_tanggung_jawab: '',
})

// Fungsi memicu tombol KS / KTS di step 1
const pilihJalur = (pilihan) => {
  jalur.value = pilihan
  form.status_temuan = pilihan

  // Reset field agar jika user berubah pikiran (back & pilih jalur lain), data jalur sebelahnya bersih
  if (pilihan === 'KS') {
    form.kategori_temuan = ''
    form.faktor_penghambat = ''
    form.rekomendasi = ''
    form.rencana_perbaikan = ''
    form.jadwal_penyelesaian = ''
    form.pihak_tanggung_jawab = ''
  } else {
    form.faktor_pendukung = ''
    form.rencana_peningkatan = ''
  }

  step.value = 2
}

const nextStep = () => {
  step.value++
}

const prevStep = () => {
  if (step.value === 2) {
    // Kembali ke deskripsi hasil, bersihkan jalur
    jalur.value = null
    form.status_temuan = ''
  }
  step.value--
}

// Menentukan batas akhir form berdasarkan jalur yang dipilih
const isLastStep = computed(() => {
  if (jalur.value === 'KS' && step.value === 3) return true
  if (jalur.value === 'KTS' && step.value === 5) return true
  return false
})

const submitJawaban = async () => {
  console.log('Data Payload Siap Dikirim:', form)
  // Ganti dengan axios POST ke API endpoint jawaban Anda
  // try {
  //   await axiosClient.post('/jawaban', form)
  //   alert('Jawaban berhasil disimpan!')
  // } catch (error) {
  //   console.error(error)
  // }
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
