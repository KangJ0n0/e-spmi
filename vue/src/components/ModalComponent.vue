<template>
  <div
    v-if="props.show"
    class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none justify-center items-center"
  >
    <div class="relative w-auto my-6 mx-auto max-w-6xl">
      <!--content-->
      <div
        class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white dark:bg-new-dark-primary outline-none focus:outline-none"
      >
        <!--header-->
        <div
          class="flex items-start justify-between p-5 border-b border-solid border-slate-200 rounded-t"
        >
          <h3 class="text-2xl font-semibold">{{ title }}</h3>
          <button
            class="p-1 ml-auto bg-transparent border-0 text-black opacity-5 float-right text-3xl leading-none font-semibold outline-none focus:outline-none"
            v-on:click="toggleModal()"
          >
            <span
              class="bg-transparent text-black opacity-5 h-6 w-6 text-2xl block outline-none focus:outline-none"
            >
              ×
            </span>
          </button>
        </div>
        <!--body-->
        <div class="relative p-6 flex-auto">
          <slot></slot>
        </div>
        <!--footer-->
        <div
          class="flex items-center justify-end p-6 border-t border-solid border-slate-200 rounded-b"
        >
          <ButtonComponent v-if="props.show_exit" size="sm" v-on:click="toggleModal()" class="mr-2">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="w-6 h-6"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </ButtonComponent>
          <ButtonComponent v-if="props.show_submit" size="sm" type="submit">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.5"
              stroke="currentColor"
              class="w-6 h-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25"
              />
            </svg>
          </ButtonComponent>
        </div>
      </div>
    </div>
  </div>
  <div v-if="props.show" class="opacity-25 fixed inset-0 z-40 bg-black"></div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import ButtonComponent from './ButtonComponent.vue'

const props = defineProps({
  show: Boolean,
  title: {
    type: String,
    default: ''
  },
  show_exit: {
    type: Boolean,
    default: true
  },
  show_submit: {
    type: Boolean,
    default: true
  }
})
const emits = defineEmits(['exit'])
const toggleModal = async () => {
  emits('exit')
}
const handleKeyDown = (event) => {
  if (event.key === 'Enter') {
    event.preventDefault() // Prevent default behavior (form submission)
  }
}

// Attach keydown event listener when component mounts
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

// Remove keydown event listener when component unmounts
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})
</script>

<style lang="scss" scoped></style>