import './assets/main.css'

// Fix (18 Sep 2026) - baris komentar ini SENGAJA ditambah, cuma buat maksa hash nama file
// index-XXXX.js di build berikutnya BEDA dari sebelumnya (index-CBi34BiL.js). Kemarin
// kelihatannya Cloudflare sempet nyimpen cache jawaban SALAH buat alamat file itu (pas upload
// masih belum lengkap), dan keukeuh ngasih jawaban lama itu terus walau file aslinya di server
// sudah benar - satu-satunya cara tanpa akses panel Cloudflare: pindah ke alamat/hash yang
// belum pernah di-cache sama sekali. Baris ini boleh dihapus kapan saja, tidak mempengaruhi
// jalannya aplikasi.
import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')
