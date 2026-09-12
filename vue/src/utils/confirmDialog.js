import { useConfirmStore } from '@/stores/confirmDialog'

// Pengganti window.confirm(...) bawaan browser. Dipakai MIRIP PERSIS pola lama supaya gampang
// nge-swap di kode yang sudah ada:
//   Sebelum:  if (!confirm('Yakin ingin menghapus?')) return
//   Sesudah:  if (!(await confirmDialog('Yakin ingin menghapus?'))) return
// Bedanya cuma ditambah `await` (dan fungsi pemanggilnya harus `async`) - karena window.confirm()
// itu SYNCHRONOUS (block browser sampai user klik), sedangkan modal Vue custom ini harus nunggu
// event klik async lewat Promise.
//
// message: string teks konfirmasi (wajib).
// options (semua opsional):
//   - title: judul modal, default "Konfirmasi"
//   - confirmText: teks tombol konfirmasi, default "Ya" (pakai "Hapus" dsb untuk aksi hapus)
//   - cancelText: teks tombol batal, default "Batal"
//   - variant: 'primary' (default, navy) atau 'danger' (merah - dipakai untuk aksi hapus/
//     membatalkan data supaya user sadar ini aksi yang lebih beresiko)
export function confirmDialog(message, options = {}) {
  const store = useConfirmStore()
  return store.open({ message, ...options })
}
