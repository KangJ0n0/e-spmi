<template>
  <ButtonComponent variant="primary" @click="buttonBack"> Kembali </ButtonComponent>

  <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 mt-4">
    <h3 class="text-lg font-bold text-gray-800 mb-4">
      {{ judulForm }}
    </h3>

    <form @submit.prevent="buttonSubmitForm" enctype="multipart/form-data">
      <div class="grid gap-6 mb-6 md:grid-cols-2">
        <div>
          <label for="status" class="block mb-2.5 text-sm font-medium text-heading">Status</label>
          <Multiselect
            id="status"
            v-model="model.status"
            :options="data_status"
            :searchable="true"
            :close-on-select="true"
            :show-labels="false"
            :disabled="isDetail"
            placeholder="Pilih Status"
            track-by="value"
            label="label"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
          />
        </div>

        <div>
          <label for="nama" class="block mb-2.5 text-sm font-medium text-heading">Nama</label>
          <input
            type="text"
            id="nama"
            v-model="model.nama"
            :disabled="isDetail"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body disabled:opacity-60"
            placeholder="Masukkan nama"
            required
          />
        </div>

        <div>
          <label for="jabatan" class="block mb-2.5 text-sm font-medium text-heading">Jabatan</label>
          <input
            type="text"
            id="jabatan"
            v-model="model.jabatan"
            :disabled="isDetail"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body disabled:opacity-60"
            placeholder="Masukkan jabatan"
            required
          />
        </div>

        <div>
          <label for="tugas" class="block mb-2.5 text-sm font-medium text-heading">Tugas</label>
          <textarea
            id="tugas"
            v-model="model.tugas"
            rows="4"
            :disabled="isDetail"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full p-3.5 shadow-xs placeholder:text-body disabled:opacity-60"
            placeholder="Masukkan tugas anggota"
          ></textarea>
        </div>

        <div>
          <label for="foto" class="block mb-2.5 text-sm font-medium text-heading">Foto</label>
          <ImageCropperComponent
            v-model:file="model.foto"
            v-model:preview="model.foto_url"
            :mode="props.tipe"
          ></ImageCropperComponent>
        </div>

        <div>
          <label for="urutan" class="block mb-2.5 text-sm font-medium text-heading">Urutan</label>
          <input
            type="number"
            id="urutan"
            v-model="model.urutan"
            :disabled="isDetail"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body disabled:opacity-60"
            placeholder="Masukkan urutan"
            required
          />
        </div>
      </div>

      <ButtonComponent v-if="!isDetail" variant="primary" type="submit" :disabled="isSaving">
        {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
      </ButtonComponent>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import ButtonComponent from '../../../../components/ButtonComponent.vue'
import ImageCropperComponent from '../../../../components/ImageComponent.vue'
import useStrukturAnggota from './../../../../composable/strukturanggota'
import Multiselect from '@vueform/multiselect'
import '../../../../css/select.css'

const { storeStrukturAnggota, updateStrukturAnggota } = useStrukturAnggota()

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
const emit = defineEmits(['back', 'edit'])

const isSaving = ref(false)
const isDetail = computed(() => props.tipe === 'detail')

// Judul form beda-beda tergantung tipe, biar jelas lagi ngapain (sebelumnya cuma
// "Kembali" doang di atas form tanpa judul, jadi nggak kelihatan lagi Tambah/Edit/Detail).
const judulForm = computed(() => {
  if (props.tipe === 'create') return 'Tambah Anggota Baru'
  if (props.tipe === 'detail') return 'Detail Anggota'
  return 'Edit Anggota'
})

const data_status = ref([
  { value: 'KETUA', label: 'Ketua' },
  { value: 'KOORDINATOR', label: 'Koordinator' },
  { value: 'STAFF', label: 'Staff' },
])

const model = reactive({
  id: null,
  nama: null,
  jabatan: null,
  status: null,
  tugas: null,
  foto: null,
  foto_url: null,
  urutan: null,
})

const buttonBack = async () => {
  emit('back')
}

const buttonSubmitForm = async () => {
  const requestData = {
    id: model.id,
    nama: model.nama,
    jabatan: model.jabatan,
    status: model.status,
    tugas: model.tugas,
    foto: model.foto,
    urutan: model.urutan,
  }

  isSaving.value = true
  try {
    if (props.tipe === 'create') {
      const { error } = await storeStrukturAnggota(requestData)
      if (!error) {
        emit('back')
      }
    } else {
      const { error } = await updateStrukturAnggota(requestData)
      if (!error) {
        emit('back')
      }
    }
  } finally {
    isSaving.value = false
  }
}

onMounted(async () => {
  if (props.data) {
    Object.assign(model, props.data)
  }
})
</script>

<style lang="scss" scoped></style>
