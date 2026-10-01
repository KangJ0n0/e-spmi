<template>
  <div class="p-6 bg-white rounded-lg shadow-sm min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Lihat Jawaban Auditee &amp; Penilaian</h1>
        <p class="text-sm text-gray-500">
          Baca jawaban Auditee, tentukan KS/KTS, lalu lengkapi Instrumen 3-6 sesuai jalur.
        </p>
      </div>
      <router-link
        to="/auditor/nilai-instrumen"
        class="px-4 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50 font-medium"
      >
        Kembali ke Jadwal
      </router-link>
    </div>

    <!-- TAMPILAN 1: TABEL DAFTAR PERTANYAAN + PREVIEW JAWABAN AUDITEE -->
    <div v-if="!modeIsiForm">
      <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/3">
              Butir Pertanyaan
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-1/3">
              Jawaban Auditee
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Status
            </th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="isLoading">
            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Memuat...</td>
          </tr>
          <tr v-else-if="listPertanyaan.length === 0">
            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
              Tidak ada pertanyaan untuk jadwal ini.
            </td>
          </tr>
          <tr
            v-for="(item, index) in pagedPertanyaan"
            :key="item.id"
            v-else
            class="hover:bg-gray-50 align-top"
          >
            <td class="px-4 py-3 text-sm text-gray-800 text-center">{{ (halaman - 1) * perHalaman + index + 1 }}</td>
            <td class="px-4 py-3 text-sm text-gray-800 whitespace-pre-line">
              {{ item.pertanyaan?.butir_pertanyaan }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-700">
              <!-- Dulu langsung nampilin deskripsi_hasil mentah di sini - string ini gabungan
              jawaban + "Link Bukti Dokumen: <url>" (lihat storeAuditee()), jadi kalau linknya
              panjang & nggak ada spasi, browser nggak bisa wrap sendiri dan bikin kolom/tabel
              ini melebar super panjang ke samping (dilaporkan user 10 Sep). Preview di sini
              cuma perlu teks jawabannya - link lengkap yang bisa diklik tetap ada di Instrumen 2
              begitu soal dibuka (DeskripsiHasilComponent di bawah), jadi link-nya dibuang dulu
              dari preview + tambah break-words jaga-jaga kalau teks jawabannya sendiri ada kata
              yang kepanjangan. -->
              <p v-if="item.jawaban?.deskripsi_hasil" class="whitespace-pre-line break-words line-clamp-4">
                {{ previewJawaban(item) }}
              </p>
              <p v-else class="text-xs text-gray-400 italic">Auditee belum menjawab.</p>
              <!-- Badge "Direvisi Auditee" DICABUT (1 Okt 2026, permintaan user: "ga perlu notif
                   sudah direvisi dll") - lihat catatan di JawabanController::storeAuditee(). -->
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <span
                v-if="!item.jawaban?.deskripsi_hasil"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600"
              >
                Menunggu Auditee
              </span>
              <!-- Draft QOL (15 Sep 2026) - beda dari "Siap Dinilai" biasa, kasih tahu Auditor
                   ada isian yang kesimpen otomatis di browser ini dari sesi sebelumnya yang
                   belum sempat di-"Simpan Penilaian" (lihat draftKey()/simpanDraftSekarang() di
                   bawah). Judul (title=) nampilin sudah sampai tahap mana kalau di-hover. -->
              <span
                v-else-if="item.status_jawaban === 'belum' && draftInfo(item)"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700"
                :title="'Draft tersimpan otomatis - ' + draftStepLabel(draftInfo(item))"
              >
                📝 Draft - {{ draftStepLabel(draftInfo(item)) }}
              </span>
              <!-- Fitur baru (30 Sep 2026) - kolaborasi: sudah ADA progres tersimpan di SERVER
                   (dari Auditor manapun yang login, bukan cuma browser ini seperti Draft di atas)
                   tapi belum lengkap semua kolom wajib jalurnya. -->
              <span
                v-else-if="item.status_jawaban === 'belum' && adaProgresServer(item)"
                class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700"
                title="Sudah ada kolom yang disimpan Auditor (bisa dari Auditor manapun di jadwal ini)"
              >
                🔧 Sedang Dikerjakan
              </span>
              <span
                v-else
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="
                  item.status_jawaban === 'sudah'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-yellow-100 text-yellow-700'
                "
              >
                {{ item.status_jawaban === 'sudah' ? 'Selesai Dinilai' : 'Siap Dinilai' }}
              </span>
            </td>
            <td class="px-4 py-3 text-center text-sm">
              <button
                v-if="item.jawaban?.deskripsi_hasil && item.status_jawaban === 'belum'"
                @click="bukaFormInstrumen(item)"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-medium shadow-sm transition-colors"
              >
                {{
                  draftInfo(item)
                    ? 'Lanjutkan Draft'
                    : adaProgresServer(item)
                      ? 'Lanjutkan Penilaian'
                      : 'Nilai (KS/KTS)'
                }}
              </button>
              <button
                v-else-if="item.status_jawaban === 'sudah'"
                @click="bukaFormInstrumen(item, true)"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-md text-xs font-medium border border-gray-300"
              >
                Lihat Hasil
              </button>
              <button
                v-else
                class="bg-gray-300 text-gray-600 px-3 py-1.5 rounded-md text-xs font-medium cursor-not-allowed"
                disabled
              >
                Belum Bisa Dinilai
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
      <Pagination :page="halaman" :total-pages="totalHalaman" @update:page="halaman = $event" />
    </div>

    <!-- TAMPILAN 2: FORM WIZARD INSTRUMEN 3-6 -->
    <div
      v-else
      class="max-w-4xl mx-auto border border-gray-200 rounded-lg shadow-sm p-6 bg-gray-50 animate-fade-in"
    >
      <!-- STEPPER VISUAL: nunjukin posisi Instrumen 1-6 secara eksplisit, biar nggak kerasa
           kayak field yang "tiba-tiba muncul" pas ganti jalur KS/KTS - tiap tahap kelihatan
           urutannya dari awal. -->
      <div class="mb-8 overflow-x-auto">
        <div class="flex items-start min-w-max px-1">
          <template v-for="(node, idx) in stepperNodes" :key="idx">
            <div class="flex flex-col items-center w-24 text-center">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 shrink-0"
                :class="{
                  'bg-green-600 border-green-600 text-white': nodeStatus(idx) === 'done',
                  'bg-blue-600 border-blue-600 text-white': nodeStatus(idx) === 'active',
                  'bg-white border-gray-300 text-gray-400': nodeStatus(idx) === 'upcoming',
                }"
              >
                <span v-if="nodeStatus(idx) === 'done'">&#10003;</span>
                <span v-else>{{ idx + 1 }}</span>
              </div>
              <p
                class="mt-2 text-[11px] font-semibold leading-tight"
                :class="nodeStatus(idx) === 'upcoming' ? 'text-gray-400' : 'text-gray-700'"
              >
                {{ node.title }}
              </p>
              <p class="text-[10px] text-gray-400 leading-tight">{{ node.subtitle }}</p>
            </div>
            <div
              v-if="idx < stepperNodes.length - 1"
              class="flex-1 h-0.5 mt-4"
              :class="nodeStatus(idx) === 'done' ? 'bg-green-500' : 'bg-gray-200'"
            ></div>
          </template>
        </div>
      </div>

      <!-- Instrumen 1: Persiapan (konteks soal) -->
      <div class="mb-6 bg-white p-4 border border-blue-100 rounded-md shadow-sm">
        <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[10px] shrink-0">1</span>
          Instrumen 1 - Butir Soal
        </h3>
        <div class="text-sm text-gray-700 font-medium mb-1" v-html="formatTeksBernomor(soalAktif.pertanyaan?.pertanyaan)"></div>
        <div class="text-sm text-gray-600 mb-3">
          Butir: <span v-html="formatTeksBernomor(soalAktif.pertanyaan?.butir_pertanyaan)"></span>
        </div>
        <div class="text-xs text-gray-500 bg-gray-100 p-2 rounded">
          <span class="font-semibold">Dokumen Dicek:</span><br />
          <span v-html="formatTeksBernomor(soalAktif.pertanyaan?.dokumen_cek)"></span>
        </div>
      </div>

      <!-- Instrumen 2: Jawaban Auditee (read-only) -->
      <div class="mb-6 bg-blue-50 p-4 border border-l-4 border-l-blue-500 rounded-md">
        <h3 class="flex items-center gap-2 text-xs font-bold text-blue-800 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] shrink-0">2</span>
          Instrumen 2 - Jawaban Auditee (Deskripsi Hasil)
        </h3>
        <DeskripsiHasilComponent :text="soalAktif.jawaban?.deskripsi_hasil" />
      </div>

      <!-- Penilaian Auditor: fitur baru (16 Sep 2026) - Auditor menuliskan rumusan/penilaian
           sendiri atas jawaban Auditee di atas, SEBELUM menentukan KS/KTS (posisi dikonfirmasi
           user lewat AskUserQuestion: "opsi 2 karena penilaian auditor berdasarkan respon
           auditee" - makanya kotak ini ditaruh SETELAH Jawaban Auditee, SEBELUM tombol
           KS/KTS di Tahap 1). Read-only kalau sudah dinilai (modeLihatSaja - datanya dari
           server), editable+wajib diisi kalau belum (lihat pilihJalur() yang menolak lanjut
           kalau form.penilaian_auditor masih kosong). Field inilah yang SEKARANG dicetak
           sebagai "Deskripsi Hasil Audit / Rumusan Temuan Hasil AMI" di Instrumen 2,3,4,5,6
           menggantikan Jawaban Auditee - lihat resources/views/dokumen/instrumen{2,3,4,5,6}
           .blade.php & JawabanController::store(). -->
      <div class="mb-6 bg-amber-50 p-4 border border-l-4 border-l-amber-500 rounded-md">
        <h3 class="flex items-center gap-2 text-xs font-bold text-amber-800 uppercase mb-2">
          <span class="w-5 h-5 rounded-full bg-amber-600 text-white flex items-center justify-center text-[10px] shrink-0">2</span>
          Penilaian Auditor (Rumusan Temuan Hasil AMI)
        </h3>
        <div
          v-if="modeLihatSaja"
          class="text-sm text-gray-700 whitespace-pre-line"
          v-html="formatTeksBernomor(soalAktif.jawaban?.penilaian_auditor)"
        ></div>
        <template v-else>
          <p class="text-xs text-amber-700 mb-2">
            Tuliskan penilaian/rumusan temuan Anda sendiri atas jawaban Auditee di atas. Wajib
            diisi sebelum menentukan KS/KTS.
          </p>
          <KolomPenilaianField
            :ref="setKolomRef('penilaian_auditor')"
            v-model="form.penilaian_auditor"
            label="Penilaian Auditor"
            :rows="4"
            :saving="kolomSaving.penilaian_auditor"
            @simpan="(v) => simpanKolom('penilaian_auditor', v)"
          />
        </template>
      </div>

      <!-- Sudah dinilai sebelumnya: tampilkan ringkasan hasil, tanpa form. Dibikin rapi pakai
           grid label+value per kotak (bukan tumpukan <p> polos) biar gampang dipindai. -->
      <div v-if="modeLihatSaja" class="bg-white p-5 border border-gray-200 rounded-md">
        <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
          <span
            class="px-3 py-1 rounded-full text-sm font-bold"
            :class="
              soalAktif.jawaban?.status_temuan === 'KS'
                ? 'bg-green-100 text-green-700'
                : 'bg-red-100 text-red-700'
            "
          >
            {{ soalAktif.jawaban?.status_temuan }}
          </span>
          <h3 class="text-sm font-bold text-gray-700">Hasil Penilaian Auditor</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <template v-if="soalAktif.jawaban?.status_temuan === 'KS'">
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Faktor Pendukung (Instrumen 3)
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.faktor_pendukung)"></div>
            </div>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Rencana Peningkatan
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.rencana_peningkatan)"></div>
            </div>
          </template>
          <template v-else>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Kategori Temuan (Instrumen 4)
              </p>
              <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.kategori_temuan }}</p>
            </div>
            <div class="bg-gray-50 rounded-md p-3">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Faktor Penghambat
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.faktor_penghambat)"></div>
            </div>
            <div class="bg-gray-50 rounded-md p-3 sm:col-span-2">
              <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
                Rencana Perbaikan
              </p>
              <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.rencana_perbaikan)"></div>
            </div>
          </template>

          <div class="bg-gray-50 rounded-md p-3 sm:col-span-2">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">Rekomendasi</p>
            <div class="text-sm text-gray-700" v-html="formatTeksBernomor(soalAktif.jawaban?.rekomendasi)"></div>
          </div>
          <div class="bg-gray-50 rounded-md p-3">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
              Jadwal Penyelesaian
            </p>
            <p class="text-sm text-gray-700">{{ formatTanggalPenyelesaian(soalAktif.jawaban?.jadwal_penyelesaian) }}</p>
          </div>
          <div class="bg-gray-50 rounded-md p-3">
            <p class="text-[11px] font-semibold text-gray-400 uppercase mb-1">
              Pihak Bertanggung Jawab
            </p>
            <p class="text-sm text-gray-700">{{ soalAktif.jawaban?.pihak_tanggung_jawab }}</p>
          </div>
        </div>

        <div class="mt-5 flex gap-2">
          <!-- Fitur baru (1 Okt 2026) - "beri opsi edit pada lihat hasil auditor" (permintaan
               user). Backend (store()) sudah lama mendukung revisi kolom kapan saja (gate lama
               "status_jawaban sudah -> tolak" sudah dihapus 30 Sep 2026), tapi FE belum punya
               jalan masuk ke mode edit dari tampilan ringkasan read-only ini - sekarang ada. -->
          <button
            type="button"
            @click="editPenilaian"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium"
          >
            Edit Penilaian
          </button>
          <button
            type="button"
            @click="batalIsi"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100"
          >
            Tutup
          </button>
        </div>
      </div>

      <!-- Form Wizard (belum dinilai) -->
      <template v-else>
        <!-- Tombol "X Batal / Tutup" DICABUT (1 Okt 2026, permintaan user: "ga perlu di
             auditor") - batalIsi() tetap ada di script, masih dipakai tombol "Tutup" di
             tampilan ringkasan Lihat Hasil (modeLihatSaja) di atas. -->
        <div class="mb-6 border-b pb-4">
          <h2 class="text-lg font-bold text-gray-800">
            {{ labelStep }}
          </h2>
        </div>

        <!-- Draft QOL (15 Sep 2026) - info kalau form ini baru dipulihkan dari draft otomatis
             (localStorage), biar Auditor sadar isiannya BUKAN form kosong baru. -->
        <div
          v-if="restoredFromDraft"
          class="mb-4 flex items-center justify-between gap-3 bg-blue-50 border border-blue-200 text-blue-800 text-sm px-3 py-2 rounded-md"
        >
          <span>📝 Melanjutkan draft tersimpan otomatis dari sesi sebelumnya.</span>
          <button
            type="button"
            @click="mulaiUlangDariAwal"
            class="text-xs font-semibold text-blue-700 underline hover:text-blue-900 shrink-0"
          >
            Mulai Ulang dari Awal
          </button>
        </div>

        <!-- Draft QOL (15 Sep 2026) - status simpan draft real-time, biar Auditor nggak ragu
             apa isiannya kesimpen atau enggak kalau nanti tab kepencet ketutup. -->
        <p v-if="!modeLihatSaja" class="text-xs text-gray-400 mb-4 -mt-2">
          {{ draftSaveStatus }}
        </p>

        <!-- Fitur baru (30 Sep 2026) - form ini SEKARANG cuma pembungkus tata letak, BUKAN
             submit-semua-sekaligus lagi (lihat catatan di blok NAVIGASI + simpanKolom() di
             script) - preventDefault tetap dipasang jaga-jaga (mis. Enter di input text). -->
        <form @submit.prevent>
          <!-- STEP 1: Keputusan KS/KTS - "di luar instrumen" sesuai diagram alur -->
          <div v-if="step === 1" class="bg-white border border-gray-200 rounded-md shadow-sm p-4 space-y-4 animate-fade-in">
            <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase">
              <span class="w-5 h-5 rounded-full bg-gray-500 text-white flex items-center justify-center text-[10px] shrink-0">3</span>
              Keputusan Auditor (di luar Instrumen)
            </h3>
            <p class="text-sm text-gray-600">
              Berdasarkan jawaban Auditee di atas, tentukan apakah kondisinya Sesuai atau Tidak
              Sesuai dengan standar.
            </p>
            <div class="flex gap-4 pt-4">
              <button
                type="button"
                @click="pilihJalur('KS')"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-md font-medium transition-colors"
              >
                Kondisi Sesuai (KS)
              </button>
              <button
                type="button"
                @click="pilihJalur('KTS')"
                class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-md font-medium transition-colors"
              >
                Kondisi Tidak Sesuai (KTS)
              </button>
            </div>
          </div>

          <!-- ============ JALUR KS: Instrumen 3 -> Instrumen 6 ============ -->
          <template v-if="jalur === 'KS'">
            <div v-if="step === 2" class="bg-white border border-green-100 rounded-md shadow-sm p-4 space-y-4 animate-fade-in">
              <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase">
                <span class="w-5 h-5 rounded-full bg-green-600 text-white flex items-center justify-center text-[10px] shrink-0">3</span>
                Instrumen 3 - Faktor Pendukung Keberhasilan
              </h3>
              <KolomPenilaianField
                :ref="setKolomRef('faktor_pendukung')"
                v-model="form.faktor_pendukung"
                label="Faktor Pendukung Keberhasilan"
                :rows="4"
                :saving="kolomSaving.faktor_pendukung"
                @simpan="(v) => simpanKolom('faktor_pendukung', v)"
              />
            </div>

            <div v-if="step === 3" class="bg-white border border-green-100 rounded-md shadow-sm p-4 space-y-4 animate-fade-in">
              <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase">
                <span class="w-5 h-5 rounded-full bg-green-600 text-white flex items-center justify-center text-[10px] shrink-0">6</span>
                Instrumen 6 - Tindak Lanjut KS
              </h3>
              <KolomPenilaianField
                :ref="setKolomRef('rencana_peningkatan')"
                v-model="form.rencana_peningkatan"
                label="Rencana Peningkatan"
                :saving="kolomSaving.rencana_peningkatan"
                @simpan="(v) => simpanKolom('rencana_peningkatan', v)"
              />
              <KolomPenilaianField
                :ref="setKolomRef('rekomendasi')"
                v-model="form.rekomendasi"
                label="Rekomendasi"
                :saving="kolomSaving.rekomendasi"
                @simpan="(v) => simpanKolom('rekomendasi', v)"
              />
              <KolomPenilaianField
                :ref="setKolomRef('jadwal_penyelesaian')"
                v-model="form.jadwal_penyelesaian"
                label="Jadwal Penyelesaian"
                type="date"
                :saving="kolomSaving.jadwal_penyelesaian"
                @simpan="(v) => simpanKolom('jadwal_penyelesaian', v)"
              />
              <KolomPenilaianField
                :ref="setKolomRef('pihak_tanggung_jawab')"
                v-model="form.pihak_tanggung_jawab"
                label="Pihak Bertanggung Jawab"
                type="text"
                placeholder="Contoh: Dekan"
                :saving="kolomSaving.pihak_tanggung_jawab"
                @simpan="(v) => simpanKolom('pihak_tanggung_jawab', v)"
              />
            </div>
          </template>

          <!-- ============ JALUR KTS: Instrumen 4 -> Instrumen 5 ============ -->
          <template v-if="jalur === 'KTS'">
            <div v-if="step === 2" class="bg-white border border-red-100 rounded-md shadow-sm p-4 space-y-4 animate-fade-in">
              <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase">
                <span class="w-5 h-5 rounded-full bg-red-600 text-white flex items-center justify-center text-[10px] shrink-0">4</span>
                Instrumen 4 - Kategori Temuan &amp; Akar Penyebab
              </h3>
              <KolomPenilaianField
                :ref="setKolomRef('kategori_temuan')"
                v-model="form.kategori_temuan"
                label="Kategori Temuan"
                type="select"
                :options="[
                  { value: 'OBS', label: 'Observasi (OBS)' },
                  { value: 'MINOR', label: 'Minor' },
                  { value: 'MAYOR', label: 'Mayor' },
                ]"
                :saving="kolomSaving.kategori_temuan"
                @simpan="(v) => simpanKolom('kategori_temuan', v)"
              />
              <KolomPenilaianField
                :ref="setKolomRef('faktor_penghambat')"
                v-model="form.faktor_penghambat"
                label="Akar Penyebab / Faktor Penghambat"
                :saving="kolomSaving.faktor_penghambat"
                @simpan="(v) => simpanKolom('faktor_penghambat', v)"
              />
            </div>

            <div v-if="step === 3" class="bg-white border border-red-100 rounded-md shadow-sm p-4 space-y-4 animate-fade-in">
              <h3 class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase">
                <span class="w-5 h-5 rounded-full bg-red-600 text-white flex items-center justify-center text-[10px] shrink-0">5</span>
                Instrumen 5 - Tindak Lanjut KTS
              </h3>
              <KolomPenilaianField
                :ref="setKolomRef('rencana_perbaikan')"
                v-model="form.rencana_perbaikan"
                label="Rencana Perbaikan"
                :saving="kolomSaving.rencana_perbaikan"
                @simpan="(v) => simpanKolom('rencana_perbaikan', v)"
              />
              <KolomPenilaianField
                :ref="setKolomRef('rekomendasi')"
                v-model="form.rekomendasi"
                label="Rekomendasi"
                :saving="kolomSaving.rekomendasi"
                @simpan="(v) => simpanKolom('rekomendasi', v)"
              />
              <KolomPenilaianField
                :ref="setKolomRef('jadwal_penyelesaian')"
                v-model="form.jadwal_penyelesaian"
                label="Jadwal Penyelesaian"
                type="date"
                :saving="kolomSaving.jadwal_penyelesaian"
                @simpan="(v) => simpanKolom('jadwal_penyelesaian', v)"
              />
              <KolomPenilaianField
                :ref="setKolomRef('pihak_tanggung_jawab')"
                v-model="form.pihak_tanggung_jawab"
                label="Pihak Bertanggung Jawab"
                type="text"
                placeholder="Contoh: Dekan"
                :saving="kolomSaving.pihak_tanggung_jawab"
                @simpan="(v) => simpanKolom('pihak_tanggung_jawab', v)"
              />
            </div>
          </template>

          <!-- Tombol "Simpan Semua yang Sudah Diisi" DICABUT (1 Okt 2026, permintaan user: "buang
               fungsi dan button simpan semua yang sudah diisi"). Mekanisme simpan otomatis saat
               pindah tahap (nextStep()/selesaiIsi() -> simpanSemuaTerisi(), lihat script) TETAP
               ADA - cuma tombol manual + loading state-nya yang dibuang, biar nggak ada 2 cara
               yang keliatan tumpang tindih di UI. -->

          <!-- NAVIGASI - fitur baru (30 Sep 2026): dulu tombol terakhir "Simpan Penilaian" POST
               SEMUA kolom sekaligus di step 3. Sekarang tiap kolom sudah simpan sendiri-sendiri
               (lihat KolomPenilaianField di atas, status_jawaban 'sudah' dihitung otomatis di
               backend begitu semua kolom wajib jalur ini terisi) - tombol di sini cuma navigasi
               antar tahap + "Selesai" buat nutup wizard & refresh status di Tampilan 1 (BUKAN
               submit form lagi). -->
          <div
            v-if="step > 1"
            class="flex justify-between items-center mt-8 pt-4 border-t border-gray-200"
          >
            <button
              type="button"
              @click="prevStep"
              class="px-5 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100"
            >
              &larr; Sebelumnya
            </button>

            <button
              v-if="step < 3"
              type="button"
              @click="nextStep"
              class="px-5 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700"
            >
              Selanjutnya &rarr;
            </button>

            <button
              v-if="step === 3"
              type="button"
              @click="selesaiIsi"
              class="px-5 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700"
            >
              Selesai / Tutup
            </button>
          </div>
        </form>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import axiosClient from '@/axios'
import DeskripsiHasilComponent from '@/components/DeskripsiHasilComponent.vue'
import KolomPenilaianField from '@/components/KolomPenilaianField.vue'
import Pagination from '@/components/Pagination.vue'
import { usePagination } from '@/composables/usePagination'
import { confirmDialog } from '@/utils/confirmDialog'
import { formatTeksBernomor } from '@/utils/formatTeksBernomor'
import { notifyError } from '@/utils/notify'

const route = useRoute()
const listPertanyaan = ref([])
const modeIsiForm = ref(false)
const modeLihatSaja = ref(false)
const soalAktif = ref(null)
const isLoading = ref(false)

// Pagination (1 Okt 2026) - Tampilan 1 (tabel daftar pertanyaan) dipotong 20 baris per halaman -
// lihat composables/usePagination.js. Data tetap di-fetch penuh sekali jalan seperti biasa, ini
// cuma motong tampilannya.
const { page: halaman, totalPages: totalHalaman, pagedItems: pagedPertanyaan, perPage: perHalaman } =
  usePagination(listPertanyaan, 20)

// Fitur baru (30 Sep 2026) - "simpan per kolom": loading state PER KOLOM (bukan 1 isSubmitting
// global lagi kayak dulu), biar tombol Simpan yang lagi diklik aja yang nunjukin "Menyimpan..."
// - kolom lain tetap bisa diedit/disimpan bebas tanpa saling kunci.
const kolomSaving = reactive({})

// Fitur baru (1 Okt 2026) - dipakai simpanSemuaTerisi() (dipanggil diam-diam dari nextStep()/
// selesaiIsi(), lihat script di bawah): butuh akses ke instance tiap <KolomPenilaianField> yang
// SEDANG ditampilkan (cuma yang untuk step/jalur aktif - lihat defineExpose di
// KolomPenilaianField.vue), buat ngumpulin kolom mana yang lagi diketik tapi belum sempat diklik
// Simpan sendiri-sendiri. Plain object (bukan reactive) - cukup dipakai imperatif, nggak perlu
// reaktivitas Vue.
const kolomRefs = {}
const setKolomRef = (kunci) => (el) => {
  kolomRefs[kunci] = el
}
const KUNCI_KOLOM_BULK = [
  'penilaian_auditor', 'faktor_pendukung', 'rencana_peningkatan',
  'kategori_temuan', 'faktor_penghambat', 'rencana_perbaikan',
  'rekomendasi', 'jadwal_penyelesaian', 'pihak_tanggung_jawab',
]

const step = ref(1)
const jalur = ref(null) // 'KS' atau 'KTS'

const form = reactive({
  jadwal_spmi_id: route.params.id,
  pertanyaan_id: '',
  // Fitur baru (16 Sep 2026) - penilaian/rumusan temuan Auditor sendiri, diisi SEBELUM
  // menentukan KS/KTS (lihat pilihJalur() & kotak "Penilaian Auditor" di template). Ini yang
  // sekarang dicetak sebagai Instrumen 2-6, BUKAN deskripsi_hasil (Jawaban Auditee).
  penilaian_auditor: '',
  status_temuan: '',
  // deskripsi_hasil TIDAK ada di sini - itu jawaban Auditee (Instrumen 2), Auditor cuma baca.
  faktor_pendukung: '',
  rencana_peningkatan: '',
  kategori_temuan: '',
  faktor_penghambat: '',
  rencana_perbaikan: '',
  // rekomendasi/jadwal_penyelesaian/pihak_tanggung_jawab DIPAKAI BERSAMA oleh jalur KS
  // (Instrumen 6) dan KTS (Instrumen 5) - sesuai diagram alur, ini kolom yang sama untuk
  // kedua jalur, bukan kolom terpisah per jalur.
  rekomendasi: '',
  jadwal_penyelesaian: '',
  pihak_tanggung_jawab: '',
})

// axiosClient men-toast error otomatis lewat interceptor dan me-resolve (bukan reject)
// promise-nya untuk error 400/404/422/500 — jadi cek bentuk response-nya, bukan cuma try/catch.
const gagal = (res) => Boolean(res?.isAxiosError || res?.response)

// QOL (16 Sep 2026) - "Jadwal Penyelesaian" sekarang diisi lewat date picker (<input
// type="date">), tersimpan sebagai teks ISO "YYYY-MM-DD". Dulu field ini teks bebas (mis.
// "September 2026"), jadi data LAMA yang mungkin masih tersimpan format bebas TIDAK dipaksa
// parse jadi tanggal (fallback: tampilkan apa adanya) - cuma nilai baru yang format-nya beneran
// "YYYY-MM-DD" yang dirapikan jadi "16 September 2026".
const formatTanggalPenyelesaian = (tgl) => {
  if (!tgl) return '-'
  if (!/^\d{4}-\d{2}-\d{2}$/.test(tgl)) return tgl
  return new Date(tgl + 'T00:00:00').toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

// ============================================================
// DRAFT OTOMATIS (localStorage) - QOL fix (15 Sep 2026)
// ============================================================
// Laporan user: form penilaian ini wizard 3 tahap dengan banyak isian wajib (Faktor Pendukung,
// Rencana Perbaikan, Rekomendasi, dst) - kalau Auditor nggak sengaja nutup tab/browser SEBELUM
// klik "Simpan Penilaian" di tahap terakhir, semua isian hilang total & pas dibuka lagi harus
// ulang dari Tahap 1 (pilih KS/KTS lagi). Dulu memang TIDAK ADA mekanisme simpan draft sama
// sekali - form cuma hidup di memori Vue, dikirim ke server SEKALI pas submit terakhir.
//
// Fix (Opsi 1 dari 3 yang ditawarkan user - paling ringan, TANPA migration/endpoint baru):
// isian di-draft ke localStorage BROWSER INI tiap kali ada perubahan (step/jalur/isi field),
// didebounce 500ms biar nggak nulis ke localStorage tiap ketikan huruf. Kalau tab ketutup/
// reload, pas dibuka lagi & soal yang sama diklik tombol "Lanjutkan Draft" (lihat template di
// atas), Auditor ditawari lanjut dari draft terakhir atau mulai baru. Draft otomatis kehapus
// begitu kolomnya beneran disimpan ke server (lihat simpanKolom(), fitur "simpan per kolom" -
// 30 Sep 2026) atau Auditor sengaja klik "Batal/Tutup" (sudah ada konfirmasi eksplisit "data
// akan hilang" di situ).
//
// Batasan yang perlu diketahui (localStorage, bukan draft server): draft CUMA ada di browser +
// device yang dipakai ngisi - kalau Auditor pindah browser/device/mode Incognito, draft nggak
// ikut. Kalau ini jadi masalah nyata di lapangan, upgrade ke draft tersimpan di server (Opsi 2)
// tinggal diminta lagi.
//
// Key di-scope per jadwal + per baris soal (list_pertanyaans.id, BUKAN bank_pertanyaans.id -
// sama seperti pola jawaban.pertanyaan_id di ListPertanyaanController::getByJadwal(), supaya
// soal yang sama dipakai ulang di jadwal LAIN tidak nyampur draft-nya).
const DRAFT_PREFIX = 'espmi_draft_nilai_v1'
const draftKey = (itemId) => `${DRAFT_PREFIX}:${route.params.id}:${itemId}`

const draftMap = ref({}) // { [itemId]: { step, jalur, form, savedAt } } - buat badge di tabel
const restoredFromDraft = ref(false)
const lastDraftSavedAt = ref(null)
let draftSaveTimer = null

const bacaDraft = (itemId) => {
  try {
    const raw = localStorage.getItem(draftKey(itemId))
    return raw ? JSON.parse(raw) : null
  } catch (e) {
    return null
  }
}

const tulisDraft = (itemId, data) => {
  try {
    localStorage.setItem(draftKey(itemId), JSON.stringify(data))
  } catch (e) {
    // localStorage penuh/private mode/dll - draft cuma "bonus", gagal diam-diam, jangan sampai
    // ganggu alur pengisian form utamanya.
  }
}

const hapusDraft = (itemId) => {
  try {
    localStorage.removeItem(draftKey(itemId))
  } catch (e) {
    // no-op
  }
}

// Dipanggil abis fetchListPertanyaan() - scan localStorage buat tau soal MANA SAJA di jadwal
// ini yang punya draft nyangkut, buat ditampilin badge-nya di tabel (Tampilan 1).
const refreshDraftMap = () => {
  const map = {}
  listPertanyaan.value.forEach((item) => {
    const d = bacaDraft(item.id)
    if (d) map[item.id] = d
  })
  draftMap.value = map
}

const draftInfo = (item) => draftMap.value[item.id] || null

const draftStepLabel = (d) => {
  if (!d) return ''
  if (d.step === 1 || !d.jalur) return 'Tahap 1 (belum pilih KS/KTS)'
  const jalurLabel = d.jalur === 'KS' ? 'Kondisi Sesuai' : 'Kondisi Tidak Sesuai'
  return `Tahap ${d.step} - ${jalurLabel}`
}

const draftSaveStatus = computed(() => {
  if (!lastDraftSavedAt.value) return 'Draft otomatis akan tersimpan begitu Anda mulai mengisi.'
  const jam = lastDraftSavedAt.value.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
  })
  return `📝 Draft tersimpan otomatis pukul ${jam} di browser ini.`
})

// Ada progres berarti-nya draft ini "layak disimpan" - dicek biar bukaFormInstrumen() nggak
// langsung bikin entry draft kosongan cuma gara-gara soal baru dibuka lalu ditutup lagi tanpa
// isi apa-apa.
const adaProgresBerarti = () =>
  jalur.value !== null ||
  Object.entries(form).some(
    ([k, v]) => k !== 'jadwal_spmi_id' && k !== 'pertanyaan_id' && k !== 'status_temuan' && v !== '',
  )

const simpanDraftSekarang = (itemId) => {
  if (!adaProgresBerarti()) return
  tulisDraft(itemId, {
    step: step.value,
    jalur: jalur.value,
    form: { ...form },
    savedAt: new Date().toISOString(),
  })
  lastDraftSavedAt.value = new Date()
}

const labelStep = computed(() => {
  if (step.value === 1) return 'Tahap 1 - Tentukan KS / KTS'
  const jalurLabel = jalur.value === 'KS' ? 'Kondisi Sesuai' : 'Kondisi Tidak Sesuai'
  return `Tahap ${step.value} - Jalur ${jalurLabel}`
})

// Data buat stepper visual (Instrumen 1-6). Node 4 & 5 labelnya generik ("Instrumen 3/4",
// "Instrumen 5/6") selama jalur KS/KTS belum dipilih, terus ganti jadi spesifik begitu
// Auditor pilih jalurnya di Tahap 1.
const stepperNodes = computed(() => {
  const isKS = jalur.value === 'KS'
  const isKTS = jalur.value === 'KTS'
  return [
    { title: 'Instrumen 1', subtitle: 'Butir Soal' },
    // Subtitle diganti (16 Sep 2026) - "Instrumen 2" isi resminya sekarang Penilaian Auditor,
    // bukan Jawaban Auditee lagi (Jawaban Auditee tetap ditampilkan sebagai konteks, tapi
    // sudah tidak dicetak) - lihat kotak Penilaian Auditor di template.
    { title: 'Instrumen 2', subtitle: 'Penilaian Auditor' },
    { title: 'Keputusan', subtitle: 'KS / KTS' },
    {
      title: isKS ? 'Instrumen 3' : isKTS ? 'Instrumen 4' : 'Instrumen 3/4',
      subtitle: isKS ? 'Faktor Pendukung' : isKTS ? 'Kategori & Penyebab' : 'Cabang Awal',
    },
    {
      title: isKS ? 'Instrumen 6' : isKTS ? 'Instrumen 5' : 'Instrumen 5/6',
      subtitle: 'Tindak Lanjut',
    },
  ]
})

// index node aktif (0-based): step 1 (Keputusan) = index 2, step 2 = index 3, step 3 = index 4.
// Instrumen 1 & 2 (index 0 & 1) selalu dianggap "done" karena selalu ditampilkan duluan sebagai
// konteks sebelum wizard dimulai.
const activeNodeIndex = computed(() => step.value + 1)

const nodeStatus = (idx) => {
  if (modeLihatSaja.value) return 'done' // sudah dinilai -> semua tahap dianggap selesai
  if (idx < activeNodeIndex.value) return 'done'
  if (idx === activeNodeIndex.value) return 'active'
  return 'upcoming'
}

// Reverted (1 Okt 2026) - node stepper 3/4/5 dulu bisa diklik langsung buat lompat tahap (30 Sep
// 2026), tapi user minta dihapus lagi: "hapus saja karena lebih baik urut" - sekarang stepper
// CUMA indikator visual posisi (lihat nodeStatus()), navigasi balik pakai tombol Sebelumnya/
// Selanjutnya/Edit Penilaian secara berurutan saja.

// Potong deskripsi_hasil di penanda "Link Bukti Dokumen: " yang sama dipakai
// DeskripsiHasilComponent.vue, khusus buat preview di kolom tabel (bukan tampilan detail) -
// lihat komentar di template soal kenapa (fix tabel jadi super panjang, 10 Sep 2026).
const MARKER_LINK_BUKTI = '\n\nLink Bukti Dokumen: '
const previewJawaban = (item) => {
  const text = item.jawaban?.deskripsi_hasil || ''
  const idx = text.indexOf(MARKER_LINK_BUKTI)
  return idx >= 0 ? text.slice(0, idx) : text
}

// Fitur baru (30 Sep 2026) - ada progres tersimpan di SERVER (bisa dari Auditor manapun yang
// login, beda dari draft browser yang cuma kepegang di 1 device) tapi belum lengkap semua kolom
// wajib jalurnya (status_jawaban masih 'belum'). Dipakai buat badge "Sedang Dikerjakan" +
// label tombol "Lanjutkan Penilaian" di Tampilan 1.
const adaProgresServer = (item) =>
  Boolean(item.jawaban?.penilaian_auditor || item.jawaban?.status_temuan)

const fetchListPertanyaan = async () => {
  isLoading.value = true
  try {
    // QOL fix (12 Sep 2026) - dibungkus try/finally, pola sama seperti IsiInstrumenAuditee.vue -
    // lihat komentar di sana soal kenapa (tombol/tabel bisa kekunci "Memuat..." permanen kalau
    // axios beneran reject karena network error/timeout).
    const res = await axiosClient.get(`/jadwal-audit/${route.params.id}/pertanyaan`)
    if (gagal(res)) return
    listPertanyaan.value = res.data
    refreshDraftMap()
  } finally {
    isLoading.value = false
  }
}

const resetForm = () => {
  Object.keys(form).forEach((key) => {
    if (key !== 'jadwal_spmi_id' && key !== 'pertanyaan_id') form[key] = ''
  })
}

// Fitur baru (30 Sep 2026) - kolaborasi: dulu form SELALU dibuka kosong (resetForm() polos),
// karena dulu memang cuma ada 2 keadaan (belum diisi sama sekali / sudah lengkap & terkunci).
// Sekarang ada keadaan baru "sudah ADA sebagian kolom tersimpan di server, dari Auditor manapun,
// tapi belum lengkap" - kalau langsung reset ke kosong, Auditor yang buka soal ini nanti KELIHATAN
// kosong padahal sebenernya sudah ada isian tersimpan (baru ilang beneran kalau kepancing simpan
// ulang kosongan). Jadi form di-prefill dulu dari soalAktif.jawaban (kalau ada) SEBELUM ditawarin
// draft browser lokal (draft = lebih baru dari yang di server, jadi tetap ditawarin di atasnya).
const prefillDariServer = (item) => {
  const j = item.jawaban
  if (!j) return
  Object.keys(form).forEach((key) => {
    if (key === 'jadwal_spmi_id' || key === 'pertanyaan_id') return
    if (j[key] !== undefined && j[key] !== null) form[key] = j[key]
  })
  if (j.status_temuan) {
    jalur.value = j.status_temuan
    step.value = 2 // langsung ke Instrumen 3/4, bukan Keputusan lagi - jalur sudah ada
  }
}

const bukaFormInstrumen = async (item, lihatSaja = false) => {
  soalAktif.value = item
  form.pertanyaan_id = item.pertanyaan_id // ID dari bank pertanyaan
  modeIsiForm.value = true
  modeLihatSaja.value = lihatSaja
  restoredFromDraft.value = false
  lastDraftSavedAt.value = null
  step.value = 1
  jalur.value = null
  resetForm()

  if (lihatSaja) return

  prefillDariServer(item)

  // Draft QOL (15 Sep 2026) - tawarkan lanjut draft kalau ada, SEBELUM form kosong di atas
  // "ditampilkan sebagai final" ke Auditor - lihat blok DRAFT OTOMATIS di atas.
  const draft = bacaDraft(item.id)
  if (!draft) return

  const lanjut = await confirmDialog(
    `Ditemukan draft tersimpan otomatis dari sesi sebelumnya (${draftStepLabel(draft)}). Lanjutkan draft ini?`,
    { title: 'Draft Ditemukan', confirmText: 'Lanjutkan Draft', cancelText: 'Mulai Baru' },
  )
  if (lanjut) {
    step.value = draft.step || 1
    jalur.value = draft.jalur || null
    Object.assign(form, draft.form)
    form.pertanyaan_id = item.pertanyaan_id // jaga-jaga draft lama kepakai buat soal lain
    restoredFromDraft.value = true
  } else {
    hapusDraft(item.id)
    refreshDraftMap()
  }
}

// Draft QOL (15 Sep 2026) - tombol di banner "Melanjutkan draft..." buat Auditor yang MALAH mau
// buang draft-nya & mulai isi dari kosong lagi (bukan lewat "Batal/Tutup" yang nutup form-nya).
const mulaiUlangDariAwal = async () => {
  const ok = await confirmDialog('Draft yang tersimpan akan dihapus dan form dikosongkan. Lanjutkan?', {
    title: 'Mulai Ulang',
    confirmText: 'Ya, Mulai Ulang',
    variant: 'danger',
  })
  if (!ok) return
  hapusDraft(soalAktif.value.id)
  refreshDraftMap()
  restoredFromDraft.value = false
  lastDraftSavedAt.value = null
  step.value = 1
  jalur.value = null
  resetForm()
}

const batalIsi = async () => {
  if (modeLihatSaja.value) {
    modeIsiForm.value = false
    return
  }
  const ok = await confirmDialog('Yakin ingin membatalkan penilaian? Data yang diisi akan hilang.', {
    title: 'Batalkan Penilaian',
    confirmText: 'Ya, Batalkan',
    variant: 'danger',
  })
  if (ok) {
    // Draft QOL (15 Sep 2026) - "Batal" itu aksi SENGAJA (sudah ada konfirmasi "data akan
    // hilang" di atas), jadi draft-nya ikut dihapus - beda dari nutup tab nggak sengaja yang
    // justru mau ditolong draft ini.
    if (soalAktif.value) hapusDraft(soalAktif.value.id)
    refreshDraftMap()
    modeIsiForm.value = false
  }
}

// Fitur baru (1 Okt 2026) - "beri opsi edit pada lihat hasil auditor". Dipanggil dari tombol
// "Edit Penilaian" di tampilan ringkasan read-only (modeLihatSaja) - keluar dari mode lihat-saja
// dan prefill ulang form dari data server yang sama. Tombol ini CUMA muncul kalau penilaiannya
// sudah LENGKAP (status_jawaban 'sudah', itu syarat tombol "Lihat Hasil" muncul di Tampilan 1),
// jadi jalur + semua kolom di tahap 2 & 3 sudah pasti terisi - makanya langsung dibuka di TAHAP
// PALING AKHIR (step 3, Instrumen 5/6), bukan tahap 2 (diperbaiki 1 Okt 2026 - sebelumnya malah
// balik ke step 2 lewat prefillDariServer() yang defaultnya begitu buat kasus "lanjutkan
// penilaian belum lengkap", bukan buat kasus "edit penilaian yang sudah lengkap" ini).
const editPenilaian = () => {
  modeLihatSaja.value = false
  resetForm()
  jalur.value = null
  step.value = 1
  prefillDariServer(soalAktif.value)
  if (jalur.value) step.value = 3
}

const pilihJalur = async (pilihan) => {
  // Gate baru (16 Sep 2026) - Penilaian Auditor WAJIB diisi dulu sebelum bisa lanjut ke
  // KS/KTS (posisinya "sebelum KS dan KTS" sesuai permintaan user), jadi dicegat di sini
  // sebelum step berpindah - bukan cuma andalkan atribut `required` di textarea (yang nggak
  // ngapa-ngapain karena tombol KS/KTS bukan submit button form).
  if (!form.penilaian_auditor?.trim()) {
    notifyError('Isi dulu Penilaian Auditor sebelum menentukan KS/KTS.')
    return
  }
  jalur.value = pilihan
  form.status_temuan = pilihan
  step.value = 2

  // Fitur baru (30 Sep 2026) - PENTING: dulu status_temuan ikut ter-submit bareng semua kolom
  // lain di tombol "Simpan Penilaian" paling akhir (lihat submitJawaban() versi lama). Sekarang
  // sudah TIDAK ADA submit gabungan lagi (lihat simpanKolom() - simpan per kolom), jadi
  // keputusan KS/KTS ini WAJIB disimpan sendiri DI SINI JUGA, kalau tidak cuma nyangkut di
  // state Vue lokal - ilang kalau tab ditutup sebelum sempat simpan kolom lain, dan Auditor LAIN
  // yang buka soal yang sama nggak bakal lihat jalur ini sudah dipilih (merusak kolaborasi).
  // penilaian_auditor diikutkan juga KALAU belum sempat disimpan lewat tombol Simpan sendiri di
  // kotaknya (jaga-jaga, request status_temuan+penilaian_auditor jadi 1 kali panggilan).
  if (!soalAktif.value?.jawaban?.penilaian_auditor) {
    await simpanKolom('penilaian_auditor', form.penilaian_auditor)
  }
  await simpanKolom('status_temuan', pilihan)
}

// Fitur baru (1 Okt 2026) - "jadi pakai logic sebelumnya juga selain perkolom": selain simpan
// per-kolom manual, SEKARANG klik "Selanjutnya" juga otomatis nyimpen diam-diam kolom-kolom tahap
// ini yang sudah diisi tapi belum diklik Simpan sendiri (panggil simpanSemuaTerisi(), lihat
// script). Ini PENTING dipanggil SEBELUM step berubah, karena begitu step ganti,
// KolomPenilaianField tahap ini di-unmount - isian yang belum diklik Simpan (cuma hidup di
// localValue internal komponen, belum pernah ke-emit ke form induk) akan HILANG kalau nggak
// ditangkap duluan lewat ambilNilaiBelumTersimpan() di sini.
const nextStep = async () => {
  await simpanSemuaTerisi()
  step.value++
}
const prevStep = () => {
  if (step.value === 2) {
    jalur.value = null
    form.status_temuan = ''
  }
  step.value--
}

// Fitur baru (30 Sep 2026) - ganti submitJawaban() lama (kirim SEMUA kolom sekaligus di step
// terakhir). Dipanggil dari @simpan tiap <KolomPenilaianField> - kirim CUMA 1 kolom yang lagi
// disimpan (lihat JawabanController::store() - sekarang terima payload partial).
const simpanKolom = async (namaKolom, nilai) => {
  if (!soalAktif.value) return
  kolomSaving[namaKolom] = true
  try {
    const res = await axiosClient.post('/jawaban/store', {
      jadwal_spmi_id: route.params.id,
      pertanyaan_id: soalAktif.value.pertanyaan_id,
      [namaKolom]: nilai,
    })
    if (gagal(res)) return

    // Refleksikan hasil simpanan ke soalAktif.jawaban juga (bukan cuma `form`), biar
    // adaProgresServer() dkk yang baca soalAktif tetap konsisten tanpa perlu
    // fetchListPertanyaan() penuh tiap simpan 1 kolom (baru fetch ulang pas selesaiIsi()).
    if (!soalAktif.value.jawaban) soalAktif.value.jawaban = {}
    soalAktif.value.jawaban[namaKolom] = nilai
    if (res.data?.status_jawaban) {
      soalAktif.value.status_jawaban = res.data.status_jawaban
    }

    // Draft QOL (15 Sep 2026) - kolom ini sudah beneran tersimpan ke server, draft lokal buat
    // soal ini juga dibersihkan biar nggak nyangkut/nawarin "lanjutkan draft" data basi lagi.
    hapusDraft(soalAktif.value.id)
    refreshDraftMap()
  } finally {
    kolomSaving[namaKolom] = false
  }
}

// Fitur baru (1 Okt 2026, disederhanakan 1 Okt 2026 - tombol manualnya "buang fungsi dan button
// simpan semua yang sudah diisi" dicabut, tapi mekanismenya tetap dipakai DIAM-DIAM dari
// nextStep()/selesaiIsi() di bawah). Ngumpulin nilai dari tiap <KolomPenilaianField> yang SEDANG
// mode edit & sudah ada isian tapi belum diklik Simpan sendiri (ambilNilaiBelumTersimpan(), lihat
// defineExpose di komponen itu), lalu kirim SEMUANYA dalam SATU request ke /jawaban/store (backend
// sudah mendukung multi-kolom per request sejak fitur simpan-per-kolom 30 Sep 2026). Nggak ada
// kolom baru yang perlu disimpan = kondisi normal (dipanggil tiap pindah tahap), jadi diam-diam
// aja, nggak ada toast.
const simpanSemuaTerisi = async () => {
  if (!soalAktif.value) return

  const payloadTambahan = {}
  KUNCI_KOLOM_BULK.forEach((kunci) => {
    const nilai = kolomRefs[kunci]?.ambilNilaiBelumTersimpan?.()
    if (nilai) payloadTambahan[kunci] = nilai
  })

  if (Object.keys(payloadTambahan).length === 0) return

  try {
    const res = await axiosClient.post('/jawaban/store', {
      jadwal_spmi_id: route.params.id,
      pertanyaan_id: soalAktif.value.pertanyaan_id,
      ...payloadTambahan,
    })
    if (gagal(res)) return

    if (!soalAktif.value.jawaban) soalAktif.value.jawaban = {}
    Object.entries(payloadTambahan).forEach(([kunci, nilai]) => {
      form[kunci] = nilai
      soalAktif.value.jawaban[kunci] = nilai
      kolomRefs[kunci]?.tandaiTersimpan?.()
    })
    if (res.data?.status_jawaban) {
      soalAktif.value.status_jawaban = res.data.status_jawaban
    }

    hapusDraft(soalAktif.value.id)
    refreshDraftMap()
  } catch (e) {
    // no-op - gagal simpan diam-diam di sini ditolerir (bukan aksi yang sengaja diklik user),
    // isian tetap kepegang di `form`/localValue komponen, nggak hilang, tinggal klik Simpan
    // manual di kolomnya kalau mau coba lagi.
  }
}

// Fitur baru (30 Sep 2026) - ganti tombol "Simpan Penilaian" lama. Tiap kolom sudah tersimpan
// sendiri-sendiri lewat simpanKolom() di atas, jadi ini CUMA nutup wizard + refresh Tampilan 1
// (status_jawaban 'sudah'/'belum' dihitung otomatis di backend, bukan dikirim dari sini).
//
// Update (1 Okt 2026) - jaga-jaga: kalau Auditor ngetik sesuatu di tahap 3 (step terakhir) lalu
// LANGSUNG klik "Selesai / Tutup" tanpa klik Simpan kolom itu dulu, isiannya bakal hilang begitu
// wizard ditutup (localValue komponen ikut lenyap). Simpan dulu diam-diam lewat simpanSemuaTerisi()
// sebelum nutup wizard, sama seperti nextStep().
const selesaiIsi = async () => {
  await simpanSemuaTerisi()
  modeIsiForm.value = false
  await fetchListPertanyaan()
}

// Draft QOL (15 Sep 2026) - auto-save: tiap step/jalur/isi field berubah, tulis ke localStorage
// (didebounce 500ms biar nggak nulis tiap ketikan huruf). Cuma jalan pas wizard beneran lagi
// dibuka buat DIISI (bukan mode "Lihat Hasil" yang read-only).
watch(
  [step, jalur, form],
  () => {
    if (!modeIsiForm.value || modeLihatSaja.value || !soalAktif.value) return
    clearTimeout(draftSaveTimer)
    draftSaveTimer = setTimeout(() => simpanDraftSekarang(soalAktif.value.id), 500)
  },
  { deep: true },
)

onMounted(() => {
  fetchListPertanyaan()
})
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.line-clamp-4 {
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
