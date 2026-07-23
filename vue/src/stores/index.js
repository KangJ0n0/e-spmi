import { defineStore } from 'pinia'

export const useStore = defineStore('store', {
  state: () => ({
    display: 'light',
    
    loader: false,
  }),
  actions: {
    setDisplay(display) {
      if (display === 'dark') {
        document.body.classList.add('dark')
      } else {
        document.body.classList.remove('dark')
      }
      this.display = display
    },
   
    setLoader(value) {
      this.loader = value
    },
  },
  getters: {
    getDisplay: (state) => state.display,
    
    isLoading: (state) => state.loader,
  },
})