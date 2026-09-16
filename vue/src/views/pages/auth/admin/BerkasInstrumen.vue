<!--
  Halaman baru "Berkas Instrumen" (13 Sep 2026) - repositori link dokumen pendukung instrumen
  (nama, keterangan, link - sumbernya diperkirakan kebanyakan Google Drive/Docs/Sheets/Slides,
  lihat getEmbedUrl() di bawah). Polanya sengaja dibikin sama seperti halaman "Instrumen"
  (BankPertanyaan.vue), filter kategori + pencarian, dan form tambah/edit. Bedanya field-nya lebih
  sederhana (nama/keterangan/link, bukan pertanyaan/butir/dokumen_cek) jadi form ditaruh langsung
  di sini (toggle show_form), tidak perlu komponen form terpisah kayak BankPertanyaanForm.vue.

  REVISI (16 Sep 2026) - kategori sebelumnya sempat TERPISAH TOTAL dari Kategori Instrumen punya
  Bank Pertanyaan (dikonfirmasi user 13 Sep), sekarang DIGABUNG atas permintaan user ("kategori
  pada instrumen dan berkas instrumen sama, jadi dihubungkan saja") - halaman ini sekarang pakai
  `KategoriInstrumenModal.vue` & endpoint `/kategori-instrumen` yang SAMA dengan BankPertanyaan.vue,
  bukan modal/tabel/endpoint sendiri lagi. Lihat migration
  gabungkan_kategori_berkas_instrumen_ke_kategori_instrumen.

  Halaman ini ADMIN ONLY (dikonfirmasi user) - baik lihat maupun kelola, beda dari Instrumen yang
  baca-nya juga dipakai Auditor. Lihat routes/api.php (grup 'claim:role_name,admin').
-->
<template>
  <div class="p-6 bg-white rounded-lg shadow min-h-screen">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Berkas Instrumen</h1>
      <ButtonComponent v-if="!show_form" variant="primary" @click="buttonTambah">
        + Tambah Berkas
      </ButtonComponent>
    </div>

    <!-- ==================== DAFTAR (list + filter) ==================== -->
    <div v-if="!show_form">
      <div class="mb-6 flex flex-wrap items-center gap-3 bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <label class="text-sm font-medium text-gray-700 shrink-0">Kategori:</label>
        <div class="relative">
          <select
            v-model="kategoriAktif"
            class="appearance-none border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="all">Semua Kategori</option>
            <option value="none">Tanpa Kategori</option>
            <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
          </select>
          <svg
            class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
        <button
          @click="showKategoriModal = true"
          class="text-sm font-medium text-blue-600 hover:text-blue-800"
        >
          + Kelola Kategori
        </button>
        <div class="relative ml-auto">
          <input
            type="text"
            v-model="searchInput"
            @input="filterSearch(searchInput)"
            placeholder="Cari nama / keterangan berkas..."
            class="border border-gray-300 rounded-md pl-3 pr-3 py-2 text-sm w-64 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>

      <!-- List berkas (pola card, bukan tabel - lebih pas buat nampung link + toggle preview) -->
      <div v-if="isFetching" class="px-6 py-8 text-center text-sm text-gray-500">
        <span class="inline-flex items-center justify-center gap-2">
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Memuat data...
        </span>
      </div>
      <div v-else-if="berkasList.length === 0" class="px-6 py-6 text-center text-sm text-gray-500 border border-dashed border-gray-200 rounded-lg">
        Belum ada berkas instrumen. Klik "+ Tambah Berkas" untuk menambahkan.
      </div>
      <div v-else class="space-y-3">
        <!-- QOL fix (13 Sep 2026, dilaporkan user) - dirapikan strukturnya: dulu badge/tombol
        Edit-Hapus/link/tombol preview semua numpuk jadi 1 blok tanpa pemisah yang jelas. Sekarang
        card dipecah jadi 3 baris yang jelas fungsinya: (1) header - nama + kategori (kiri) &
        aksi Edit/Hapus (kanan), (2) keterangan, (3) link + tombol preview sebagai "chip" yang
        rapi (bukan teks polos numpuk). Panel preview juga dipisah jadi section sendiri di bawah
        card (bukan nempel di tengah blok info) dengan header "Preview" + tombol tutup + spinner
        selagi iframe-nya sendiri masih memuat. -->
        <div
          v-for="item in berkasList"
          :key="item.id"
          class="border border-gray-200 rounded-lg hover:border-gray-300 transition-colors overflow-hidden"
        >
          <div class="p-4">
            <!-- Baris 1: nama + kategori (kiri), aksi Edit/Hapus (kanan) -->
            <div class="flex items-start justify-between gap-3 mb-1.5">
              <div class="min-w-0 flex items-center gap-2 flex-wrap">
                <h3 class="text-sm font-semibold text-gray-800">{{ item.nama }}</h3>
                <span
                  v-if="item.kategori_instrumen"
                  class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 shrink-0"
                >
                  {{ item.kategori_instrumen.nama }}
                </span>
                <span v-else class="text-xs text-gray-400 italic shrink-0">Tanpa Kategori</span>
              </div>
              <div class="flex items-center gap-3 shrink-0">
                <button @click="buttonEdit(item)" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                  Edit
                </button>
                <button @click="deleteItem(item.id)" class="text-red-600 hover:text-red-800 font-medium text-sm">
                  Hapus
                </button>
              </div>
            </div>

            <!-- Baris 2: keterangan (opsional) -->
            <p v-if="item.keterangan" class="text-sm text-gray-600 whitespace-pre-line mb-3">
              {{ item.keterangan }}
            </p>

            <!-- Baris 3: link + tombol preview, bentuk "chip" biar kebedaan jelas dari teks biasa -->
            <div class="flex items-center flex-wrap gap-2">
              <a
                :href="item.link"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-gray-50 hover:bg-gray-100 border border-gray-200 text-sm text-blue-600 hover:text-blue-800 font-medium max-w-xs transition-colors"
              >
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span class="truncate">{{ item.link }}</span>
              </a>
              <button
                v-if="getEmbedUrl(item.link)"
                @click="togglePreview(item.id)"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-sm font-medium border transition-colors shrink-0"
                :class="previewTerbuka === item.id
                  ? 'bg-gray-700 text-white border-gray-700 hover:bg-gray-800'
                  : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
              >
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ previewTerbuka === item.id ? 'Sembunyikan Preview' : 'Tampilkan Preview' }}
              </button>
              <span v-else class="text-xs text-gray-400 italic shrink-0">
                Preview tidak didukung untuk link ini
              </span>
            </div>
          </div>

          <!-- Panel preview - section terpisah dengan header sendiri (13 Sep 2026, dirapikan) -
          dulu iframe langsung nempel di bawah baris link tanpa jarak/pemisah visual, sekarang
          diberi border-top + header "Preview" + tombol tutup (X) biar jelas ini bagian terpisah
          dari info card, plus spinner "Memuat preview..." selagi iframe Google Drive-nya sendiri
          masih loading (bisa agak lama tergantung ukuran file/koneksi). -->
          <div
            v-if="previewTerbuka === item.id && getEmbedUrl(item.link)"
            class="border-t border-gray-100 bg-gray-50"
          >
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
              <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Preview</span>
              <button @click="togglePreview(item.id)" class="text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="relative">
              <div
                v-if="!previewLoaded[item.id]"
                class="absolute inset-0 flex items-center justify-center bg-gray-50 text-sm text-gray-500"
              >
                <span class="inline-flex items-center justify-center gap-2">
                  <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  Memuat preview...
                </span>
              </div>
              <iframe
                :src="getEmbedUrl(item.link)"
                class="w-full h-96 border-0 block"
                allow="autoplay"
                @load="previewLoaded[item.id] = true"
              ></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== FORM TAMBAH/EDIT ==================== -->
    <div v-else class="max-w-2xl">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">
        {{ tipe_form === 'edit' ? 'Edit Berkas Instrumen' : 'Tambah Berkas Instrumen' }}
      </h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
          <input
            v-model="form.nama"
            type="text"
            required
            placeholder="mis. RPS Semester Ganjil 2026/2027"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
          <textarea
            v-model="form.keterangan"
            rows="3"
            placeholder="Keterangan singkat tentang berkas ini (opsional)"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
          ></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Link <span class="text-red-500">*</span></label>
          <input
            v-model="form.link"
            type="url"
            required
            placeholder="https://drive.google.com/file/d/.../view?usp=sharing"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <p class="text-xs text-gray-500 mt-1">
            Bisa link Google Drive/Docs/Sheets/Slides (otomatis ditampilkan preview embed-nya) atau
            link lain (ditampilkan sebagai link biasa).
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
          <select
            v-model="form.kategori_instrumen_id"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Tanpa Kategori</option>
            <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
          </select>
        </div>
        <div class="flex items-center gap-3 pt-2">
          <ButtonComponent type="submit" variant="primary" :disabled="isSaving">
            {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
          </ButtonComponent>
          <button type="button" @click="buttonKembali" class="text-sm text-gray-500 hover:text-gray-700 px-2">
            Batal
          </button>
        </div>
      </form>
    </div>

    <!-- Modal kelola kategori - SAMA dengan yang dipakai halaman Instrumen (16 Sep 2026) -->
    <KategoriInstrumenModal
      v-if="showKategoriModal"
      @close="showKategoriModal = false"
      @changed="fetchKategoriList"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { debounce } from 'lodash'
import axiosClient from '@/axios'
import ButtonComponent from '@/components/ButtonComponent.vue'
import KategoriInstrumenModal from '@/components/KategoriInstrumenModal.vue'
import { notifyError } from '@/utils/notify'
import { confirmDialog } from '@/utils/confirmDialog'

const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

const berkasList = ref([])
const isFetching = ref(true)
const isSaving = ref(false)

const kategoriList = ref([])
const kategoriAktif = ref('all')
const showKategoriModal = ref(false)
const searchInput = ref('')
const searchQuery = ref('')

const previewTerbuka = ref(null)
// Ditrack per item id - dipakai buat nampilin spinner "Memuat preview..." sampai event @load
// iframe-nya sendiri nembak (13 Sep 2026, dirapikan bareng redesign card). Direset ke false tiap
// preview ditutup/dibuka ulang biar spinner-nya muncul lagi kalau item yang sama dibuka lagi
// nanti (bukan cuma sekali doang seumur hidup komponen).
const previewLoaded = ref({})

const show_form = ref(false)
const tipe_form = ref('create')
const editingId = ref(null)
const form = ref({ nama: '', keterangan: '', link: '', kategori_instrumen_id: '' })

// Konversi link Google Drive/Docs/Sheets/Slides jadi URL embeddable (bisa dipasang di <iframe>).
// User minta "kalau bisa link nya ada embed mungkin source nya kebanyakan gdrive" - fitur ini
// best-effort: kalau link cocok salah satu pola Google di bawah, dibalikin URL preview-nya;
// kalau tidak (link lain jenis apapun), dibalikin null dan halaman cuma nampilin link biasa
// (fallback aman, tidak coba embed sembarang situs yang bisa saja menolak di-iframe).
const getEmbedUrl = (link) => {
  if (!link || typeof link !== 'string') return null

  // Google Drive file: /file/d/<ID>/(view|edit|...) -> /file/d/<ID>/preview
  let match = link.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/)
  if (match) return `https://drive.google.com/file/d/${match[1]}/preview`

  // Google Drive "open?id=" atau "uc?id=" -> bentuk /file/d/<ID>/preview
  match = link.match(/drive\.google\.com\/(?:open|uc)\?(?:.*&)?id=([a-zA-Z0-9_-]+)/)
  if (match) return `https://drive.google.com/file/d/${match[1]}/preview`

  // Google Drive folder: /drive/folders/<ID> -> embeddedfolderview
  match = link.match(/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9_-]+)/)
  if (match) return `https://drive.google.com/embeddedfolderview?id=${match[1]}#list`

  // Google Docs / Sheets / Slides: /document|spreadsheets|presentation/d/<ID>/(edit|view|...)
  match = link.match(/docs\.google\.com\/(document|spreadsheets|presentation)\/d\/([a-zA-Z0-9_-]+)/)
  if (match) {
    const jenis = match[1]
    const id = match[2]
    const akhiran = jenis === 'presentation' ? 'embed' : 'preview'
    return `https://docs.google.com/${jenis}/d/${id}/${akhiran}`
  }

  return null
}

const togglePreview = (id) => {
  const sedangTerbuka = previewTerbuka.value === id
  previewTerbuka.value = sedangTerbuka ? null : id
  // Reset status "sudah loaded" tiap kali preview ini ditutup ATAU dibuka - biar spinner
  // "Memuat preview..." konsisten muncul lagi tiap sesi buka preview, bukan cuma pertama kali.
  previewLoaded.value = { ...previewLoaded.value, [id]: false }
}

const fetchBerkasList = async () => {
  isFetching.value = true
  try {
    const params = {}
    if (searchQuery.value) params.filter = searchQuery.value
    if (kategoriAktif.value !== 'all') params.kategori_instrumen_id = kategoriAktif.value

    const res = await axiosClient.get('/berkas-instrumen', { params })
    if (gagal(res)) return
    berkasList.value = res.data ?? []
  } finally {
    isFetching.value = false
  }
}

const fetchKategoriList = async () => {
  // Digabung (16 Sep 2026) - pakai endpoint kategori-instrumen yang SAMA dengan halaman
  // Instrumen, bukan /kategori-berkas-instrumen sendiri lagi.
  const res = await axiosClient.get('/kategori-instrumen')
  if (gagal(res)) return
  kategoriList.value = res.data
}

const filterSearch = debounce(async (query) => {
  searchQuery.value = query
  await fetchBerkasList()
}, 500)

// Ganti kategori filter langsung refetch ke server - pola sama dengan BankPertanyaan.vue.
watch(kategoriAktif, async () => {
  await fetchBerkasList()
})

const buttonTambah = () => {
  tipe_form.value = 'create'
  editingId.value = null
  // Kalau kategori filter sedang dipilih spesifik, berkas baru default masuk kategori yang
  // sama - sama seperti pola di BankPertanyaan.vue - bisa diganti manual di form.
  const kategoriDefault = kategoriAktif.value !== 'all' && kategoriAktif.value !== 'none' ? kategoriAktif.value : ''
  form.value = { nama: '', keterangan: '', link: '', kategori_instrumen_id: kategoriDefault }
  show_form.value = true
}

const buttonEdit = (item) => {
  tipe_form.value = 'edit'
  editingId.value = item.id
  form.value = {
    nama: item.nama,
    keterangan: item.keterangan || '',
    link: item.link,
    kategori_instrumen_id: item.kategori_instrumen_id || '',
  }
  show_form.value = true
}

const buttonKembali = () => {
  show_form.value = false
  editingId.value = null
}

const submitForm = async () => {
  isSaving.value = true
  try {
    const payload = {
      nama: form.value.nama,
      keterangan: form.value.keterangan || null,
      link: form.value.link,
      kategori_instrumen_id: form.value.kategori_instrumen_id || null,
    }
    const res = editingId.value
      ? await axiosClient.put(`/berkas-instrumen/${editingId.value}`, payload)
      : await axiosClient.post('/berkas-instrumen', payload)

    if (gagal(res)) return

    show_form.value = false
    editingId.value = null
    await fetchBerkasList()
  } finally {
    isSaving.value = false
  }
}

const deleteItem = async (id) => {
  const ok = await confirmDialog('Yakin ingin menghapus berkas instrumen ini?', {
    title: 'Hapus Berkas Instrumen',
    confirmText: 'Hapus',
    variant: 'danger',
  })
  if (!ok) return

  const res = await axiosClient.delete(`/berkas-instrumen/${id}`)
  if (gagal(res)) {
    notifyError('Gagal menghapus berkas instrumen.')
    return
  }
  await fetchBerkasList()
}

onMounted(() => {
  fetchBerkasList()
  fetchKategoriList()
})
</script>
