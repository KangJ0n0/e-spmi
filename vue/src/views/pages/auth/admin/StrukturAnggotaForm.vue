<template>
  <ButtonComponent variant="primary" @click="buttonBack">
    Kembali
  </ButtonComponent>

  <div>
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
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
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
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
            placeholder="Masukkan jabatan"
            required
          />
        </div>

        

        <div>
          <label for="website" class="block mb-2.5 text-sm font-medium text-heading">Tugas</label>
          <textarea
            id="tugas"
            v-model="model.tugas"
            rows="4"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full p-3.5 shadow-xs placeholder:text-body"
            placeholder="Write your thoughts here..."
          ></textarea>
        </div>


        <div>
          <label for="visitors" class="block mb-2.5 text-sm font-medium text-heading">Foto</label>
          <ImageCropperComponent
            v-model:file="model.foto"
            v-model:preview="model.foto_url"
            :mode="props.tipe"
          ></ImageCropperComponent>
        </div>
      </div>

       <div>
          <label for="urutan" class="block mb-2.5 text-sm font-medium text-heading">Urutan</label>
          <input
            type="number"
            id="urutan"
            v-model="model.urutan"
            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
            placeholder="Masukkan urutan"
            required
          />
        </div>

     
      <button
        type="submit"
        class="text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
      >
        Submit
      </button>
    </form>
  </div>
</template>

<script setup>

import ButtonComponent from '../../../../components/ButtonComponent.vue'
import ImageCropperComponent from '../../../../components/ImageComponent.vue'
import useStrukturAnggota from './../../../../composable/strukturanggota'
import Multiselect from '@vueform/multiselect'
import { reactive, ref ,onMounted} from 'vue'
import '../../../../css/select.css'

const { data_struktur_anggota, getStrukturAnggota, storeStrukturAnggota, updateStrukturAnggota, destroyStrukturAnggota} = useStrukturAnggota()
const buttonSubmitForm = async () => {
const requestData = {
id:model.id,
nama:model.nama,
jabatan:model.jabatan,
status:model.status,
tugas:model.tugas,
foto:model.foto,
urutan:model.urutan 
}
 if (props.tipe == 'create') {
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
}

const data_status = ref([
  { value: 'KETUA', label: 'Ketua' },
  { value: 'KOORDINATOR', label: 'Koordinator' },
  { value: 'STAFF', label: 'Staff' }
])

const model = reactive({
  id: null,
  nama: null,
  jabatan: null,
  status: null,
  tugas: null,
  foto: null,
  foto_url: null,
  urutan: null
})

const emit = defineEmits(['back', 'edit'])

const buttonBack = async () => {
  emit('back')
}

const buttonEdit = async () => {
  emit('edit')
}

const props = defineProps({
  tipe: {
    type: String,
    required: true
  },
  data: {
    type: Object,
    default: () => ({})
  }
})
onMounted(async () => {
  
  if (props.data) {
    Object.assign(model, props.data)
  }
 
})
</script>

<style lang="scss" scoped></style>