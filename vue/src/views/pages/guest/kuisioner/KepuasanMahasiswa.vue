<template>
  <div class="bg-white font-poppins min-h-screen">
    <!-- Breadcrumb -->
    <div class="max-w-3xl mx-auto px-6 pt-8 text-sm text-gray-400 flex items-center gap-2">
      <router-link to="/" class="hover:text-[#0F2A4A] transition-colors">Beranda</router-link>
      <span>/</span>
      <span class="text-gray-500">Kuisioner</span>
      <span>/</span>
      <span class="text-[#0F2A4A] font-medium">Kepuasan Mahasiswa</span>
    </div>

    <!-- ============ SUKSES SUBMIT ============ -->
    <div v-if="submitted" class="max-w-2xl mx-auto px-6 py-24 text-center">
      <div class="h-16 w-16 rounded-full bg-[#C9A227]/15 flex items-center justify-center mx-auto mb-6">
        <svg class="h-8 w-8 text-[#C9A227]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25L15 8.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h1 class="font-poppins font-bold text-2xl text-[#0F2A4A] mb-3">Terima kasih!</h1>
      <p class="text-gray-500 leading-relaxed mb-8">
        Jawaban Anda telah kami terima dan akan menjadi masukan berharga
        bagi peningkatan mutu layanan UNWIKU.
      </p>
      <router-link
        to="/"
        class="inline-flex items-center gap-2 bg-[#0F2A4A] text-white text-sm font-semibold px-6 py-3 rounded-full hover:bg-[#16386b] transition-colors"
      >
        Kembali ke Beranda
      </router-link>
    </div>

    <!-- ============ FORM ============ -->
    <div v-else class="max-w-2xl mx-auto px-6 pt-10 pb-24">
      <!-- Header -->
      <p class="text-[11px] font-bold tracking-[0.25em] text-[#C9A227] uppercase mb-4">
        Survei Kepuasan
      </p>
      <h1 class="font-poppins font-bold text-2xl md:text-3xl text-[#0F2A4A] mb-3">
        Kuisioner Kepuasan Mahasiswa
      </h1>
      <p class="text-gray-500 leading-relaxed mb-8">
        Mohon isi identitas dan 10 pertanyaan berikut secara jujur. Jawaban
        Anda bersifat rahasia dan hanya digunakan untuk keperluan penjaminan
        mutu UNWIKU.
      </p>

      <!-- Progress bar -->
      <div class="mb-10">
        <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
          <span>Progres pengisian</span>
          <span>{{ answeredCount }} / {{ questions.length }} pertanyaan</span>
        </div>
        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-[#C9A227] rounded-full transition-all duration-300"
            :style="{ width: progressPercent + '%' }"
          ></div>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-12">
        <!-- ============ IDENTITAS ============ -->
        <section>
          <p class="text-xs font-semibold tracking-[0.15em] text-gray-400 uppercase mb-5">
            Identitas Responden
          </p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
              <input
                v-model="identity.nama"
                type="text"
                class="form-input"
                :class="{ 'form-input-error': touched && !identity.nama }"
                placeholder="Nama sesuai KTM"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">NIM</label>
              <input
                v-model="identity.nim"
                type="text"
                class="form-input"
                :class="{ 'form-input-error': touched && !identity.nim }"
                placeholder="Nomor Induk Mahasiswa"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Program Studi</label>
              <select
                v-model="identity.prodi"
                class="form-input"
                :class="{ 'form-input-error': touched && !identity.prodi }"
              >
                <option value="" disabled>Pilih program studi</option>
                <option v-for="prodi in daftarProdi" :key="prodi" :value="prodi">{{ prodi }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Semester</label>
              <input
                v-model="identity.semester"
                type="text"
                class="form-input"
                :class="{ 'form-input-error': touched && !identity.semester }"
                placeholder="Semester saat ini"
              />
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <input
                v-model="identity.email"
                type="email"
                class="form-input"
                :class="{ 'form-input-error': touched && !identity.email }"
                placeholder="nama@unwiku.ac.id"
              />
            </div>
          </div>
        </section>

        <!-- ============ 10 PERTANYAAN (Skala Likert) ============ -->
        <section>
          <p class="text-xs font-semibold tracking-[0.15em] text-gray-400 uppercase mb-2">
            Penilaian Kepuasan
          </p>
          <p class="text-xs text-gray-400 mb-6">
            1 = Sangat Tidak Puas &middot; 5 = Sangat Puas
          </p>

          <div class="space-y-8">
            <div
              v-for="(q, idx) in questions"
              :key="q.id"
              class="pb-8"
              :class="{ 'border-b border-gray-100': idx < questions.length - 1 }"
            >
              <p class="text-sm font-medium text-gray-800 mb-4 leading-relaxed">
                <span class="text-[#0F2A4A] font-bold">{{ idx + 1 }}.</span>
                {{ q.text }}
              </p>

              <div class="flex items-center justify-between max-w-sm">
                <button
                  v-for="score in 5"
                  :key="score"
                  type="button"
                  @click="answers[q.id] = score"
                  class="likert-dot"
                  :class="{ 'likert-dot-active': answers[q.id] === score }"
                >
                  {{ score }}
                </button>
              </div>
              <div class="flex items-center justify-between max-w-sm mt-1.5">
                <span class="text-[10px] text-gray-400">Tidak Puas</span>
                <span class="text-[10px] text-gray-400">Sangat Puas</span>
              </div>

              <p v-if="touched && !answers[q.id]" class="text-xs text-red-500 mt-2">
                Pertanyaan ini belum dijawab
              </p>
            </div>
          </div>
        </section>

        <!-- Submit -->
        <div>
          <p v-if="touched && !isFormValid" class="text-sm text-red-500 mb-4">
            Mohon lengkapi identitas dan semua pertanyaan sebelum mengirim.
          </p>
          <button
            type="submit"
            :disabled="submitting"
            class="w-full bg-[#0F2A4A] text-white font-semibold text-sm py-3.5 rounded-full hover:bg-[#16386b] transition-colors disabled:opacity-50"
          >
            {{ submitting ? 'Mengirim...' : 'Kirim Jawaban' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'KuisionerKepuasanMahasiswa' });

import { reactive, ref, computed } from 'vue';

const identity = reactive({
  nama: '',
  nim: '',
  prodi: '',
  semester: '',
  email: '',
});

const daftarProdi = [
  'S1 Ilmu Hukum',
  'S1 Administrasi Publik',
  'S1 Manajemen',
  'S1 Ekonomi Pembangunan',
  'S1 Akuntansi',
  'S1 Teknik Sipil',
  'S1 Teknik Arsitektur',
  'S1 Teknik Elektro',
  'S1 Peternakan',
];

// TODO: sesuaikan 10 pertanyaan dengan instrumen kuisioner resmi LPMU
const questions = [
  { id: 'q1', text: 'Kualitas proses pembelajaran di kelas sesuai dengan harapan saya.' },
  { id: 'q2', text: 'Dosen memberikan materi yang relevan dan mudah dipahami.' },
  { id: 'q3', text: 'Fasilitas ruang kelas dan laboratorium mendukung proses belajar.' },
  { id: 'q4', text: 'Layanan administrasi akademik (KRS, KHS, dll) mudah diakses.' },
  { id: 'q5', text: 'Perpustakaan menyediakan referensi yang memadai.' },
  { id: 'q6', text: 'Bimbingan akademik dari dosen wali berjalan efektif.' },
  { id: 'q7', text: 'Kegiatan kemahasiswaan mendukung pengembangan minat dan bakat.' },
  { id: 'q8', text: 'Sistem informasi akademik UNWIKU mudah digunakan.' },
  { id: 'q9', text: 'Lingkungan kampus mendukung kenyamanan proses belajar.' },
  { id: 'q10', text: 'Secara keseluruhan, saya puas dengan layanan pendidikan UNWIKU.' },
];

const answers = reactive({});
const touched = ref(false);
const submitting = ref(false);
const submitted = ref(false);

const answeredCount = computed(() => Object.keys(answers).filter((k) => answers[k]).length);
const progressPercent = computed(() => Math.round((answeredCount.value / questions.length) * 100));

const isIdentityValid = computed(() =>
  identity.nama && identity.nim && identity.prodi && identity.semester && identity.email
);
const isFormValid = computed(() => isIdentityValid.value && answeredCount.value === questions.length);

function handleSubmit() {
  touched.value = true;
  if (!isFormValid.value) return;

  submitting.value = true;

  // FE-only untuk sekarang — belum terhubung backend.
  // Payload sudah disiapkan dalam bentuk yang siap dikirim begitu endpoint tersedia.
  const payload = { identity: { ...identity }, answers: { ...answers } };
  console.log('Payload kuisioner:', payload);

  setTimeout(() => {
    submitting.value = false;
    submitted.value = true;
  }, 600);
}
</script>

<style scoped>
.font-poppins {
  font-family: 'Poppins', sans-serif;
}

.form-input {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.form-input:focus {
  outline: none;
  border-color: #0f2a4a;
  box-shadow: 0 0 0 3px rgba(15, 42, 74, 0.08);
}
.form-input-error {
  border-color: #ef4444;
}

.likert-dot {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 2.5rem;
  width: 2.5rem;
  border-radius: 9999px;
  border: 1.5px solid #e5e7eb;
  color: #9ca3af;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.15s ease;
}
.likert-dot:hover {
  border-color: #c9a227;
  color: #0f2a4a;
}
.likert-dot-active {
  background-color: #0f2a4a;
  border-color: #0f2a4a;
  color: white;
}
</style>