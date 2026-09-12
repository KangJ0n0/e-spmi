import { defineStore } from 'pinia'

// Store buat modal konfirmasi Ya/Tidak yang MODULAR - dipasang SATU kali sebagai instance global
// (lihat ConfirmDialogComponent.vue + App.vue), disetir dari mana saja lewat helper
// src/utils/confirmDialog.js. Ganti window.confirm() bawaan browser yang tampilannya beda-beda
// tiap browser/OS dan tidak bisa distyling sama sekali.
export const useConfirmStore = defineStore('confirmDialog', {
  state: () => ({
    isOpen: false,
    title: 'Konfirmasi',
    message: '',
    confirmText: 'Ya',
    cancelText: 'Batal',
    // 'primary' (navy, aksi netral) atau 'danger' (merah, aksi menghapus/membatalkan data) -
    // dipetakan langsung ke variant yang sudah ada di ButtonComponent.vue.
    variant: 'primary',
    resolver: null,
  }),
  actions: {
    // Dipanggil oleh helper confirmDialog(), BUKAN dipanggil manual dari halaman - balikin
    // Promise<boolean> yang baru resolve begitu user klik salah satu tombol.
    open({ title, message, confirmText, cancelText, variant }) {
      // Jaga-jaga kalau ada dialog lain yang belum di-resolve pas dialog baru dibuka (harusnya
      // nggak pernah kejadian di alur normal karena UI lain ke-block modal overlay) - batalkan
      // dulu yang lama biar Promise-nya nggak nggantung selamanya.
      if (this.resolver) {
        this.resolver(false)
      }

      this.title = title || 'Konfirmasi'
      this.message = message || ''
      this.confirmText = confirmText || 'Ya'
      this.cancelText = cancelText || 'Batal'
      this.variant = variant || 'primary'
      this.isOpen = true

      return new Promise((resolve) => {
        this.resolver = resolve
      })
    },
    resolve(value) {
      this.isOpen = false
      if (this.resolver) {
        this.resolver(value)
        this.resolver = null
      }
    },
  },
})
