import { fileURLToPath, URL } from 'node:url'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

import process from 'node:process'
import tailwindcss from '@tailwindcss/vite'
// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  // Fix (18 Sep 2026) - Cloudflare di depan domain unwiku.ac.id kelihatan nyimpen cache jawaban
  // SALAH (halaman error/HTML) buat beberapa alamat file .js tertentu, dari pas situs sempet
  // rusak (build dari folder salah). Vite normalnya kasih nama file berdasar hash ISI file
  // (index-XXXX.js) - biasanya bagus buat cache permanen, tapi jadi masalah kalau salah satu
  // alamat itu SUDAH KETERLANJUR ke-cache jawaban salah oleh Cloudflare, soalnya nama filenya
  // gak berubah walau isinya sama kayak sebelumnya (yang error).
  //
  // `buildStamp` di bawah ini (timestamp saat build dijalankan) ditambahkan ke SEMUA nama file
  // hasil build ini (entry, chunk per halaman, css/gambar) - jadi tiap kali `npm run build`
  // dijalankan, SEMUA nama file otomatis beda dari build sebelumnya, gak peduli isinya sama
  // atau nggak. Ini mastiin gak ada satu pun alamat file yang "nabrak" cache lama Cloudflare
  // yang mungkin masih nyangkut, tanpa perlu tau persis file mana yang kena.
  const buildStamp = Date.now().toString(36)

  return {
    plugins: [vue(), tailwindcss()],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
    build: {
      rollupOptions: {
        output: {
          entryFileNames: `assets/[name]-${buildStamp}-[hash].js`,
          chunkFileNames: `assets/[name]-${buildStamp}-[hash].js`,
          assetFileNames: `assets/[name]-${buildStamp}-[hash][extname]`,
        },
      },
    },
    server: {
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      hmr: {
        clientPort: 443, // Ensures HMR works with HTTPS proxy
      },
      allowedHosts: env.VITE_ALLOWED_HOSTS ? env.VITE_ALLOWED_HOSTS.split(',') : [],
    },
  }
})
