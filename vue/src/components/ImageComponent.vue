<template>
  <div
    class="relative w-full sm:w-[354px] h-[472px] sm:h-[472px] max-w-full mx-auto"
    :class="[!props.preview ? 'hidden' : '']"
  >
    <div class="flex justify-center items-center h-full">
      <img v-if="props.preview" :src="props.preview" class="max-w-full max-h-full" />
    </div>
    <div
      v-if="props.preview && props.mode != 'detail'"
      class="absolute top-0 right-0 flex items-center space-x-2 p-2 bg-gray-800 bg-opacity-50 rounded-bl-lg rounded-tr-lg"
    >
      <button
        type="button"
        class="flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded-full focus:outline-none hover:bg-red-600"
        @click="onDeleteFile"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 9.707a1 1 0 01-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 11-1.414-1.414L8.586 10l-2.293-2.293a1 1 0 111.414-1.414L10 8.586l2.293-2.293a1 1 0 111.414 1.414L11.414 10l2.293 2.293z"
            clip-rule="evenodd"
          ></path>
        </svg>
      </button>
    </div>
  </div>

  <div class="flex items-center justify-center w-full" :class="[props.preview ? 'hidden' : '']">
    <label
      for="dropzone-file"
      class="flex flex-col items-center justify-center border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-new-dark-primary hover:bg-gray-100 dark:border-gray-600 w-[354px] h-[472px]"
    >
      <div class="flex flex-col items-center justify-center pt-5 pb-6">
        <svg
          aria-hidden="true"
          class="w-10 h-10 mb-3 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
          ></path>
        </svg>
        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
          <span class="font-semibold">Upload File</span>
        </p>
        <p v-if="result.validate.file" class="text-xs text-red-500 dark:text-red-400">
          {{ result.validate.file }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-400" v-else>PNG / JPG (Maksimal 2MB)</p>
      </div>
      <input
        id="dropzone-file"
        type="file"
        class="hidden"
        @change="selectFile"
        accept="image/jpeg,image/png"
      />
    </label>
  </div>

  <ModalComponent
    :show="isShowModal"
    @exit="isShowModal = false"
    title="Crop Gambar"
    :show_exit="false"
    :show_submit="false"
  >
    <div class="flex flex-row-reverse">
      <<ButtonComponent variant="primary" @click="getResult">
        Crop
      ></ButtonComponent>
      <ButtonComponent variant="primary" @click="reset">Reset</ButtonComponent>
       
      <ButtonComponent variant="primary" @click="isShowModal = false">
        Cancel
      ></ButtonComponent>
    </div>
    <div class="w-full mt-2 mb-2">
      <VuePictureCropper
        :boxStyle="{
          width: '100%',
          height: '100%',
          backgroundColor: '#f8f8f8',
          margin: 'auto'
        }"
        :img="pic"
        :options="{
          viewMode: 1,
          dragMode: 'move',
          aspectRatio: 3 / 4,
          cropBoxResizable: false
        }"
        :presetMode="{
          mode: 'fixedSize',
          width: 354,
          height: 472
        }"
        @ready="ready"
      />
    </div>
  </ModalComponent>
</template>

<script setup>
import { ref, reactive } from 'vue'
import VuePictureCropper, { cropper } from 'vue-picture-cropper'
import ModalComponent from '../components/ModalComponent.vue'
import ButtonComponent from '../components/ButtonComponent.vue'

const isShowModal = ref(false)

const pic = ref('')
const result = reactive({
  dataURL: '',
  blobURL: '',
  validate: []
})
const props = defineProps({
  file: {
    type: [Object, String]
  },
  preview: {
    type: String,
    default: ''
  },
  mode: {
    type: String,
    default: 'edit'
  }
})
const onDeleteFile = async () => {
  result.dataURL = ''
  result.blobURL = ''
  pic.value = ''
  emit('update:file', null)
  emit('update:preview', '')
}
const emit = defineEmits(['update:file', 'update:preview'])
function selectFile(ev) {
  // Reset last selection and results
  pic.value = ''
  result.dataURL = ''
  result.blobURL = ''

  const file = ev.target.files[0]
  if (file.size > 2 * 1024 * 1024) {
    result.validate['file'] = 'Ukuran File Maksimum 2MB'
    return
  }
  if (!['image/jpeg', 'image/png'].includes(file.type)) {
    result.validate['file'] = 'Format File Bukan PNG / JPG'
    return
  }

  const reader = new FileReader()
  reader.readAsDataURL(file)
  reader.onload = () => {
    pic.value = String(reader.result)
    isShowModal.value = true
  }
}

/**
 * Get cropping results
 */
async function getResult() {
  if (!cropper) return
  const base64 = cropper.getDataURL()
  const blob = await cropper.getBlob()
  if (!blob) return

  // const file = await cropper.getFile({
  //   fileName: 'A'
  // })

  // console.log({ base64, blob, file })
  result.dataURL = base64
  result.blobURL = URL.createObjectURL(blob)
  emit('update:file', base64)
  emit('update:preview', base64)
  isShowModal.value = false
}

/**
 * Clear the crop box
 */
// function clear() {
//   if (!cropper) return
//   cropper.clear()
// }

/**
 * Reset the default cropping area
 */
function reset() {
  if (!cropper) return
  cropper.reset()
}

/**
 * The ready event passed to Cropper.js
 */
function ready() {
  console.log('Cropper is ready.')
}
</script>

<style lang="less" scoped></style>