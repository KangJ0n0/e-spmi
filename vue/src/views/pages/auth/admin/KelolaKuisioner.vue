<template>
  <div class="font-poppins">
    <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">
      <!-- ============ PANEL KIRI: KATEGORI SURVEI ============ -->
      <aside class="bg-white border border-gray-100 rounded-2xl overflow-hidden h-fit">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <p class="text-xs font-semibold tracking-[0.1em] text-gray-400 uppercase">
            Kategori Survei
          </p>
          <button
            @click="openCategoryModal()"
            class="h-7 w-7 flex items-center justify-center rounded-full bg-[#0F2A4A]/[0.06] text-[#0F2A4A] hover:bg-[#C9A227] hover:text-white transition-colors"
            title="Tambah kategori"
          >
            <svg
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2.5"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </button>
        </div>

        <div class="p-2">
          <button
            v-for="kategori in kategoriList"
            :key="kategori.id"
            @click="selectedKategoriId = kategori.id"
            class="w-full text-left px-4 py-3 rounded-xl transition-colors mb-1 group"
            :class="
              selectedKategoriId === kategori.id
                ? 'bg-[#0F2A4A] text-white'
                : 'hover:bg-gray-50 text-gray-700'
            "
          >
            <div class="flex items-center justify-between gap-2">
              <span class="text-sm font-semibold truncate">{{ kategori.nama_survei }}</span>
              <span
                class="h-1.5 w-1.5 rounded-full shrink-0"
                :class="kategori.is_active ? 'bg-[#C9A227]' : 'bg-gray-300'"
                :title="kategori.is_active ? 'Aktif' : 'Nonaktif'"
              ></span>
            </div>
            <p
              class="text-xs mt-0.5 truncate"
              :class="selectedKategoriId === kategori.id ? 'text-white/60' : 'text-gray-400'"
            >
              {{ questionsOf(kategori.id).length }} pertanyaan
            </p>
          </button>

          <p v-if="kategoriList.length === 0" class="text-sm text-gray-400 text-center py-8">
            Belum ada kategori survei
          </p>
        </div>
      </aside>

      <!-- ============ PANEL KANAN: DETAIL & PERTANYAAN ============ -->
      <section class="bg-white border border-gray-100 rounded-2xl p-6 md:p-8">
        <div v-if="!selectedKategori" class="text-center py-20 text-gray-400">
          <p class="text-sm">Pilih atau tambah kategori survei di panel kiri.</p>
        </div>

        <template v-else>
          <!-- Header kategori -->
          <div class="flex items-start justify-between gap-4 mb-8 pb-6 border-b border-gray-100">
            <div class="min-w-0">
              <div class="flex items-center gap-2.5 mb-1.5">
                <h2 class="font-poppins font-bold text-xl text-[#0F2A4A] truncate">
                  {{ selectedKategori.nama_survei }}
                </h2>
                <span
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide shrink-0"
                  :class="
                    selectedKategori.is_active
                      ? 'bg-[#C9A227]/15 text-[#C9A227]'
                      : 'bg-gray-100 text-gray-400'
                  "
                >
                  {{ selectedKategori.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>
              <p class="text-sm text-gray-500">
                {{ selectedKategori.deskripsi || 'Tidak ada deskripsi.' }}
              </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <button
                @click="openCategoryModal(selectedKategori)"
                class="text-sm font-medium text-gray-500 hover:text-[#0F2A4A] px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Edit
              </button>
              <button
                @click="confirmDeleteCategory(selectedKategori)"
                class="text-sm font-medium text-gray-400 hover:text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors"
              >
                Hapus
              </button>
            </div>
          </div>

          <!-- Daftar pertanyaan -->
          <div class="flex items-center justify-between mb-5">
            <p class="text-xs font-semibold tracking-[0.1em] text-gray-400 uppercase">
              Daftar Pertanyaan
            </p>
            <button
              @click="openQuestionModal()"
              class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0F2A4A] hover:text-[#C9A227] transition-colors"
            >
              <svg
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Tambah Pertanyaan
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(q, idx) in currentQuestions"
              :key="q.id"
              class="border border-gray-100 rounded-xl p-4 hover:border-gray-200 transition-colors"
            >
              <div class="flex items-start gap-3">
                <!-- Kontrol urutan -->
                <div class="flex flex-col items-center gap-0.5 pt-0.5 shrink-0">
                  <button
                    @click="moveQuestion(q, -1)"
                    :disabled="idx === 0"
                    class="h-5 w-5 flex items-center justify-center text-gray-300 hover:text-[#0F2A4A] disabled:opacity-30 disabled:hover:text-gray-300"
                  >
                    <svg
                      class="h-3.5 w-3.5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="3"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.5 15.75l7.5-7.5 7.5 7.5"
                      />
                    </svg>
                  </button>
                  <span class="text-[11px] font-bold text-gray-300">{{ q.urutan }}</span>
                  <button
                    @click="moveQuestion(q, 1)"
                    :disabled="idx === currentQuestions.length - 1"
                    class="h-5 w-5 flex items-center justify-center text-gray-300 hover:text-[#0F2A4A] disabled:opacity-30 disabled:hover:text-gray-300"
                  >
                    <svg
                      class="h-3.5 w-3.5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="3"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                      />
                    </svg>
                  </button>
                </div>

                <!-- Isi pertanyaan -->
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-800 leading-relaxed">
                    {{ q.teks_pertanyaan }}
                    <span v-if="q.is_required" class="text-red-500">*</span>
                  </p>
                  <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span
                      class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full bg-[#0F2A4A]/[0.06] text-[#0F2A4A]"
                    >
                      {{ tipeLabel(q.tipe_jawaban) }}
                    </span>
                    <span v-if="q.opsi_jawaban?.length" class="text-xs text-gray-400">
                      {{ q.opsi_jawaban.length }} opsi jawaban
                    </span>
                  </div>

                  <!-- Preview opsi jika ada -->
                  <div v-if="q.opsi_jawaban?.length" class="flex flex-wrap gap-1.5 mt-3">
                    <span
                      v-for="opsi in q.opsi_jawaban"
                      :key="opsi.id"
                      class="text-xs bg-gray-50 border border-gray-100 rounded-full px-2.5 py-1 text-gray-600"
                    >
                      {{ opsi.label_opsi }}
                      <span class="text-gray-400">({{ opsi.bobot_nilai }})</span>
                    </span>
                  </div>
                </div>

                <!-- Aksi -->
                <div class="flex items-center gap-1 shrink-0">
                  <button
                    @click="openQuestionModal(q)"
                    class="h-8 w-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-[#0F2A4A] hover:bg-gray-50 transition-colors"
                    title="Edit"
                  >
                    <svg
                      class="h-4 w-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"
                      />
                    </svg>
                  </button>
                  <button
                    @click="deleteQuestion(q)"
                    class="h-8 w-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                    title="Hapus"
                  >
                    <svg
                      class="h-4 w-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
                      />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <p v-if="currentQuestions.length === 0" class="text-sm text-gray-400 text-center py-12">
              Belum ada pertanyaan pada kategori ini.
            </p>
          </div>
        </template>
      </section>
    </div>

    <!-- ============ MODAL: KATEGORI ============ -->
    <div v-if="categoryModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40" @click="closeCategoryModal"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="font-poppins font-bold text-lg text-[#0F2A4A] mb-5">
          {{ categoryModal.isEdit ? 'Edit Kategori Survei' : 'Tambah Kategori Survei' }}
        </h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Survei</label>
            <input
              v-model="categoryModal.form.nama_survei"
              type="text"
              class="form-input"
              placeholder="Contoh: Kepuasan Mahasiswa"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5"
              >Deskripsi (opsional)</label
            >
            <textarea
              v-model="categoryModal.form.deskripsi"
              rows="3"
              class="form-input resize-none"
              placeholder="Penjelasan singkat survei"
            ></textarea>
          </div>
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input
              v-model="categoryModal.form.is_active"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-[#0F2A4A] focus:ring-[#0F2A4A]"
            />
            <span class="text-sm text-gray-700">Tampilkan di halaman publik</span>
          </label>
        </div>

        <div class="flex items-center justify-end gap-3 mt-7">
          <button
            @click="closeCategoryModal"
            class="text-sm font-medium text-gray-500 px-4 py-2 hover:bg-gray-50 rounded-lg transition-colors"
          >
            Batal
          </button>
          <button
            @click="saveCategory"
            class="text-sm font-semibold text-white bg-[#0F2A4A] px-5 py-2 rounded-lg hover:bg-[#16386b] transition-colors"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- ============ MODAL: PERTANYAAN ============ -->
    <div v-if="questionModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/40" @click="closeQuestionModal"></div>
      <div
        class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto"
      >
        <h3 class="font-poppins font-bold text-lg text-[#0F2A4A] mb-5">
          {{ questionModal.isEdit ? 'Edit Pertanyaan' : 'Tambah Pertanyaan' }}
        </h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Teks Pertanyaan</label>
            <textarea
              v-model="questionModal.form.teks_pertanyaan"
              rows="2"
              class="form-input resize-none"
              placeholder="Contoh: Bagaimana kualitas fasilitas kelas?"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Jawaban</label>
              <select v-model="questionModal.form.tipe_jawaban" class="form-input">
                <option value="pilihan_ganda">Pilihan Ganda</option>
                <option value="checkbox">Checkbox</option>
                <option value="esai">Esai</option>
              </select>
            </div>
            <div class="flex items-end pb-2.5">
              <label class="flex items-center gap-2.5 cursor-pointer">
                <input
                  v-model="questionModal.form.is_required"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-[#0F2A4A] focus:ring-[#0F2A4A]"
                />
                <span class="text-sm text-gray-700">Wajib diisi</span>
              </label>
            </div>
          </div>

          <!-- Opsi jawaban, hanya untuk pilihan_ganda / checkbox -->
          <div v-if="needsOptions" class="pt-2 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3 mt-4">
              <p class="text-xs font-semibold tracking-[0.1em] text-gray-400 uppercase">
                Opsi Jawaban
              </p>
              <button
                type="button"
                @click="addOption"
                class="text-xs font-semibold text-[#0F2A4A] hover:text-[#C9A227] transition-colors"
              >
                + Tambah Opsi
              </button>
            </div>

            <div class="space-y-2">
              <div
                v-for="(opsi, oIdx) in questionModal.form.opsi_jawaban"
                :key="oIdx"
                class="flex items-center gap-2"
              >
                <input
                  v-model="opsi.label_opsi"
                  type="text"
                  class="form-input flex-1"
                  placeholder="Label, contoh: Sangat Baik"
                />
                <input
                  v-model.number="opsi.bobot_nilai"
                  type="number"
                  class="form-input w-20 text-center"
                  placeholder="Bobot"
                />
                <button
                  type="button"
                  @click="removeOption(oIdx)"
                  class="h-9 w-9 shrink-0 flex items-center justify-center text-gray-300 hover:text-red-600 transition-colors"
                >
                  <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <p
                v-if="questionModal.form.opsi_jawaban.length === 0"
                class="text-xs text-gray-400 py-2"
              >
                Belum ada opsi. Klik "Tambah Opsi" untuk menambahkan.
              </p>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 mt-7">
          <button
            @click="closeQuestionModal"
            class="text-sm font-medium text-gray-500 px-4 py-2 hover:bg-gray-50 rounded-lg transition-colors"
          >
            Batal
          </button>
          <button
            @click="saveQuestion"
            class="text-sm font-semibold text-white bg-[#0F2A4A] px-5 py-2 rounded-lg hover:bg-[#16386b] transition-colors"
          >
            Simpan
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'KelolaKuisioner' })

import { ref, reactive, computed } from 'vue'
import { confirmDialog } from '@/utils/confirmDialog'

// ==========================================
// MOCK DATA — struktur field disamakan persis dengan skema tabel,
// supaya nanti tinggal diganti hasil axiosClient.get(...) tanpa
// perlu ubah nama field di komponen ini.
// ==========================================

const kategoriList = ref([
  {
    id: 1,
    nama_survei: 'Kepuasan Mahasiswa',
    deskripsi: 'Survei kepuasan mahasiswa terhadap layanan akademik.',
    is_active: true,
  },
  {
    id: 2,
    nama_survei: 'Kepuasan Dosen',
    deskripsi: 'Survei kepuasan dosen terhadap dukungan institusi.',
    is_active: true,
  },
  { id: 3, nama_survei: 'Perilaku Berjiwa Pancasila', deskripsi: '', is_active: false },
])

const pertanyaanList = ref([
  {
    id: 101,
    kategori_survei_id: 1,
    teks_pertanyaan: 'Bagaimana kualitas fasilitas kelas?',
    tipe_jawaban: 'pilihan_ganda',
    urutan: 1,
    is_required: true,
    opsi_jawaban: [
      { id: 1, label_opsi: 'Sangat Baik', bobot_nilai: 4 },
      { id: 2, label_opsi: 'Baik', bobot_nilai: 3 },
      { id: 3, label_opsi: 'Cukup', bobot_nilai: 2 },
      { id: 4, label_opsi: 'Kurang', bobot_nilai: 1 },
    ],
  },
  {
    id: 102,
    kategori_survei_id: 1,
    teks_pertanyaan: 'Saran perbaikan layanan akademik',
    tipe_jawaban: 'esai',
    urutan: 2,
    is_required: false,
    opsi_jawaban: [],
  },
])

const selectedKategoriId = ref(kategoriList.value[0]?.id ?? null)

const selectedKategori = computed(
  () => kategoriList.value.find((k) => k.id === selectedKategoriId.value) || null,
)

function questionsOf(kategoriId) {
  return pertanyaanList.value
    .filter((q) => q.kategori_survei_id === kategoriId)
    .sort((a, b) => a.urutan - b.urutan)
}

const currentQuestions = computed(() =>
  selectedKategoriId.value ? questionsOf(selectedKategoriId.value) : [],
)

function tipeLabel(tipe) {
  const map = { pilihan_ganda: 'Pilihan Ganda', checkbox: 'Checkbox', esai: 'Esai' }
  return map[tipe] || tipe
}

// ---- Kategori: modal & CRUD ----
const categoryModal = reactive({
  open: false,
  isEdit: false,
  editingId: null,
  form: { nama_survei: '', deskripsi: '', is_active: true },
})

function openCategoryModal(kategori = null) {
  categoryModal.isEdit = !!kategori
  categoryModal.editingId = kategori?.id ?? null
  categoryModal.form = kategori
    ? {
        nama_survei: kategori.nama_survei,
        deskripsi: kategori.deskripsi,
        is_active: kategori.is_active,
      }
    : { nama_survei: '', deskripsi: '', is_active: true }
  categoryModal.open = true
}
function closeCategoryModal() {
  categoryModal.open = false
}
function saveCategory() {
  if (!categoryModal.form.nama_survei.trim()) return

  if (categoryModal.isEdit) {
    const target = kategoriList.value.find((k) => k.id === categoryModal.editingId)
    Object.assign(target, categoryModal.form)
  } else {
    const newId = Math.max(0, ...kategoriList.value.map((k) => k.id)) + 1
    kategoriList.value.push({ id: newId, ...categoryModal.form })
    selectedKategoriId.value = newId
  }
  // TODO: ganti dengan axiosClient.post('/admin/kategori-survei', form) / .put(...)
  closeCategoryModal()
}
async function confirmDeleteCategory(kategori) {
  const ok = await confirmDialog(
    `Hapus kategori "${kategori.nama_survei}"? Seluruh pertanyaan di dalamnya juga akan terhapus.`,
    { title: 'Hapus Kategori', confirmText: 'Hapus', variant: 'danger' },
  )
  if (!ok) return
  kategoriList.value = kategoriList.value.filter((k) => k.id !== kategori.id)
  pertanyaanList.value = pertanyaanList.value.filter((q) => q.kategori_survei_id !== kategori.id)
  selectedKategoriId.value = kategoriList.value[0]?.id ?? null
  // TODO: ganti dengan axiosClient.delete(`/admin/kategori-survei/${kategori.id}`)
}

// ---- Pertanyaan: modal & CRUD ----
const questionModal = reactive({
  open: false,
  isEdit: false,
  editingId: null,
  form: {
    teks_pertanyaan: '',
    tipe_jawaban: 'pilihan_ganda',
    is_required: false,
    opsi_jawaban: [],
  },
})

const needsOptions = computed(() =>
  ['pilihan_ganda', 'checkbox'].includes(questionModal.form.tipe_jawaban),
)

function openQuestionModal(pertanyaan = null) {
  questionModal.isEdit = !!pertanyaan
  questionModal.editingId = pertanyaan?.id ?? null
  questionModal.form = pertanyaan
    ? {
        teks_pertanyaan: pertanyaan.teks_pertanyaan,
        tipe_jawaban: pertanyaan.tipe_jawaban,
        is_required: pertanyaan.is_required,
        opsi_jawaban: pertanyaan.opsi_jawaban.map((o) => ({ ...o })),
      }
    : { teks_pertanyaan: '', tipe_jawaban: 'pilihan_ganda', is_required: false, opsi_jawaban: [] }
  questionModal.open = true
}
function closeQuestionModal() {
  questionModal.open = false
}
function addOption() {
  questionModal.form.opsi_jawaban.push({ id: Date.now(), label_opsi: '', bobot_nilai: null })
}
function removeOption(idx) {
  questionModal.form.opsi_jawaban.splice(idx, 1)
}
function saveQuestion() {
  if (!questionModal.form.teks_pertanyaan.trim() || !selectedKategoriId.value) return

  const opsi = needsOptions.value ? questionModal.form.opsi_jawaban : []

  if (questionModal.isEdit) {
    const target = pertanyaanList.value.find((q) => q.id === questionModal.editingId)
    Object.assign(target, { ...questionModal.form, opsi_jawaban: opsi })
  } else {
    const newId = Math.max(0, ...pertanyaanList.value.map((q) => q.id)) + 1
    const urutan = currentQuestions.value.length + 1
    pertanyaanList.value.push({
      id: newId,
      kategori_survei_id: selectedKategoriId.value,
      urutan,
      ...questionModal.form,
      opsi_jawaban: opsi,
    })
  }
  // TODO: ganti dengan axiosClient.post('/admin/pertanyaan-survei', payload) / .put(...)
  closeQuestionModal()
}
async function deleteQuestion(pertanyaan) {
  const ok = await confirmDialog('Hapus pertanyaan ini?', {
    title: 'Hapus Pertanyaan',
    confirmText: 'Hapus',
    variant: 'danger',
  })
  if (!ok) return
  pertanyaanList.value = pertanyaanList.value.filter((q) => q.id !== pertanyaan.id)
  // Rapikan ulang nomor urutan setelah dihapus
  questionsOf(pertanyaan.kategori_survei_id).forEach((q, idx) => (q.urutan = idx + 1))
  // TODO: ganti dengan axiosClient.delete(`/admin/pertanyaan-survei/${pertanyaan.id}`)
}
function moveQuestion(pertanyaan, direction) {
  const list = questionsOf(pertanyaan.kategori_survei_id)
  const idx = list.findIndex((q) => q.id === pertanyaan.id)
  const swapWith = list[idx + direction]
  if (!swapWith) return
  ;[pertanyaan.urutan, swapWith.urutan] = [swapWith.urutan, pertanyaan.urutan]
  // TODO: ganti dengan axiosClient.patch('/admin/pertanyaan-survei/reorder', { ... })
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
  transition:
    border-color 0.15s ease,
    box-shadow 0.15s ease;
}
.form-input:focus {
  outline: none;
  border-color: #0f2a4a;
  box-shadow: 0 0 0 3px rgba(15, 42, 74, 0.08);
}
</style>
