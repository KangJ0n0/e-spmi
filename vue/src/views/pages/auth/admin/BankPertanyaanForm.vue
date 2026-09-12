<template>
  <ButtonComponent variant="primary" @click="buttonBack"> Kembali </ButtonComponent>

  <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 mt-4">
    <h3 class="text-lg font-bold text-gray-800 mb-4">
      {{ props.tipe === 'create' ? 'Tambah Pertanyaan Baru' : 'Edit Pertanyaan' }}
    </h3>
    <form @submit.prevent="buttonSubmitForm">
      <div class="mb-4">
        <label for="pertanyaan" class="block mb-2.5 text-sm font-medium text-heading"
          >Pernyataan Isi Standar / Pertanyaan</label
        >
        <textarea
          id="pertanyaan"
          v-model="model.pertanyaan"
          rows="3"
          required
          class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full p-3.5 shadow-xs placeholder:text-body"
        ></textarea>
      </div>

      <div class="mb-4">
        <label for="butir_pertanyaan" class="block mb-2.5 text-sm font-medium text-heading"
          >Butir Pertanyaan</label
        >
        <textarea
          id="butir_pertanyaan"
          v-model="model.butir_pertanyaan"
          rows="3"
          required
          class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full p-3.5 shadow-xs placeholder:text-body"
        ></textarea>
      </div>

      <div class="mb-6">
        <label for="dokumen_cek" class="block mb-2.5 text-sm font-medium text-heading"
          >Dokumen Akan Dicek (Satu per baris)</label
        >
        <textarea
          id="dokumen_cek"
          v-model="model.dokumen_cek"
          rows="3"
          required
          class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full p-3.5 shadow-xs placeholder:text-body"
        ></textarea>
      </div>

      <!-- Kategori Instrumen (fitur baru 9 Sep 2026) - opsional, "Tanpa Kategori" tetap valid. -->
      <div class="mb-6">
        <label for="kategori_instrumen_id" class="block mb-2.5 text-sm font-medium text-heading"
          >Kategori Instrumen</label
        >
        <select
          id="kategori_instrumen_id"
          v-model="model.kategori_instrumen_id"
          class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full p-3.5 shadow-xs"
        >
          <option value="">Tanpa Kategori</option>
          <option v-for="k in props.kategoriList" :key="k.id" :value="k.id">{{ k.nama }}</option>
        </select>
      </div>

      <ButtonComponent variant="primary" type="submit" :disabled="isSaving">
        {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
      </ButtonComponent>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import ButtonComponent from '@/components/ButtonComponent.vue'
import axiosClient from '@/axios'
import { notifyError } from '@/utils/notify'

const props = defineProps({
  tipe: {
    type: String,
    required: true,
  },
  data: {
    type: Object,
    default: () => ({}),
  },
  // Daftar Kategori Instrumen buat dropdown (fitur baru 9 Sep 2026) - dilempar dari
  // BankPertanyaan.vue, sumbernya sama (GET /kategori-instrumen), zero fetch tambahan di sini.
  kategoriList: {
    type: Array,
    default: () => [],
  },
})
const emit = defineEmits(['back'])

const isSaving = ref(false)
const model = reactive({
  id: null,
  pertanyaan: '',
  butir_pertanyaan: '',
  dokumen_cek: '',
  kategori_instrumen_id: '',
})

onMounted(() => {
  if (props.tipe === 'edit' && props.data) {
    Object.assign(model, props.data)
  } else if (props.data?.kategori_instrumen_id) {
    // Mode create - BankPertanyaan.vue bisa nitip default kategori (kategori yang lagi aktif
    // di filter), biar alur "buka ruang LAMEMBA -> tambah soal" nggak perlu pilih ulang manual.
    model.kategori_instrumen_id = props.data.kategori_instrumen_id
  }
  // Select value harus '' (bukan null/undefined) biar cocok sama opsi "Tanpa Kategori".
  if (!model.kategori_instrumen_id) model.kategori_instrumen_id = ''
})

const buttonBack = () => {
  emit('back')
}

const buttonSubmitForm = async () => {
  isSaving.value = true
  try {
    // '' (Tanpa Kategori) dikirim sebagai null, biar validasi `nullable|exists:...` di
    // backend lolos (string kosong bukan UUID valid).
    const payload = { ...model, kategori_instrumen_id: model.kategori_instrumen_id || null }
    // Toast sukses sudah otomatis dari interceptor axios.js (backend balikin `message`) - dulu
    // ada alert() manual duplikat di sini (dirapikan 10 Sep, lihat src/utils/notify.js).
    if (props.tipe === 'create') {
      await axiosClient.post('/bank-pertanyaan', payload)
    } else {
      await axiosClient.put(`/bank-pertanyaan/${model.id}`, payload)
    }
    emit('back')
  } catch (error) {
    console.error('Gagal menyimpan:', error)
    notifyError('Gagal menyimpan data pertanyaan.')
  } finally {
    isSaving.value = false
  }
}
</script>
