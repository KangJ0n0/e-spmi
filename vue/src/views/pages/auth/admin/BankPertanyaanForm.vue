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

const props = defineProps({
  tipe: {
    type: String,
    required: true,
  },
  data: {
    type: Object,
    default: () => ({}),
  },
})
const emit = defineEmits(['back'])

const isSaving = ref(false)
const model = reactive({
  id: null,
  pertanyaan: '',
  butir_pertanyaan: '',
  dokumen_cek: '',
})

onMounted(() => {
  if (props.tipe === 'edit' && props.data) {
    Object.assign(model, props.data)
  }
})

const buttonBack = () => {
  emit('back')
}

const buttonSubmitForm = async () => {
  isSaving.value = true
  try {
    if (props.tipe === 'create') {
      await axiosClient.post('/bank-pertanyaan', model)
      alert('Pertanyaan berhasil ditambahkan!')
    } else {
      await axiosClient.put(`/bank-pertanyaan/${model.id}`, model)
      alert('Pertanyaan berhasil diperbarui!')
    }
    emit('back')
  } catch (error) {
    console.error('Gagal menyimpan:', error)
    alert('Gagal menyimpan data pertanyaan.')
  } finally {
    isSaving.value = false
  }
}
</script>
