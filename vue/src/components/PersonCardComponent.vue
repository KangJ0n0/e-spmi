<template>
  <div :class="wrapperClass">
    <div :class="avatarClass">
      <img v-if="foto" :src="foto" :alt="nama" class="w-full h-full object-cover object-top" />
      <span v-else :class="initialsClass">{{ initials }}</span>
    </div>
    <h3 :class="namaClass">{{ nama }}</h3>
    <p :class="jabatanClass">{{ jabatan }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ name: 'PersonCardComponent' })

const props = defineProps({
  nama: { type: String, required: true },
  jabatan: { type: String, required: true },
  foto: { type: String, default: '' },
  variant: {
    type: String,
    default: 'coordinator',
    validator: (v) => ['lead', 'coordinator'].includes(v),
  },
})

const isLead = computed(() => props.variant === 'lead')

const initials = computed(() => {
  if (!props.nama) return ''
  const words = props.nama
    .split(' ')
    .filter((w) => w.length > 0 && !w.includes('.') && !w.includes(','))
  return words.length >= 2
    ? (words[0][0] + words[1][0]).toUpperCase()
    : props.nama.substring(0, 2).toUpperCase()
})

const wrapperClass = computed(() =>
  isLead.value
    ? 'person-card person-card--lead w-full max-w-sm bg-[var(--navy)] text-white p-8 rounded-2xl text-center'
    : 'person-card person-card--coordinator text-center p-6',
)

const avatarClass = computed(() =>
  isLead.value
    ? 'w-32 h-32 md:w-36 md:h-36 mx-auto aspect-square bg-white rounded-xl overflow-hidden mb-4 shadow-inner ring-4 ring-[var(--navy)] flex items-center justify-center'
    : 'w-28 h-28 md:w-32 md:h-32 mx-auto aspect-square bg-white rounded-xl overflow-hidden mb-4 flex items-center justify-center shadow-sm',
)

const initialsClass = computed(() =>
  isLead.value
    ? 'font-poppins font-bold text-2xl text-[var(--navy)]'
    : 'font-poppins font-bold text-xl text-[var(--navy)]',
)

const namaClass = computed(() =>
  isLead.value
    ? 'font-poppins text-[1.1rem] font-bold mb-1'
    : 'font-poppins text-[1.05rem] font-bold text-[var(--navy)] leading-snug mb-3 min-h-[3rem] flex items-center justify-center',
)

const jabatanClass = computed(() =>
  isLead.value
    ? 'text-xs text-[var(--gold)] font-bold tracking-widest uppercase mt-3'
    : 'text-xs text-gray-500 font-medium leading-relaxed',
)
</script>

<style scoped>
.person-card {
  --navy: #0f2a4a;
  --gold: #c9a227;
}

/* Kartu Ketua: shadow drop biasa + aksen garis gold di bawah lewat inset,
   bukan border-bottom — supaya sudut rounded-nya mulus, tidak "sobek". */
.person-card--lead {
  box-shadow:
    0 20px 25px -5px rgb(0 0 0 / 0.1),
    0 8px 10px -6px rgb(0 0 0 / 0.1),
    inset 0 -4px 0 0 var(--gold);
}

/* Kartu Koordinator: border tipis rata di semua sisi + aksen garis navy
   di atas lewat inset, alasan sama seperti di atas. */
.person-card--coordinator {
  background-color: color-mix(in srgb, var(--navy) 3.5%, white);
  border: 1px solid color-mix(in srgb, var(--navy) 8%, white);
  border-radius: 1rem;
  box-shadow: inset 0 4px 0 0 var(--navy);
  transition:
    background-color 0.25s ease,
    transform 0.25s ease,
    box-shadow 0.25s ease;
}
.person-card--coordinator:hover {
  background-color: color-mix(in srgb, var(--navy) 6%, white);
  transform: translateY(-4px);
  box-shadow:
    inset 0 4px 0 0 var(--navy),
    0 12px 28px -10px color-mix(in srgb, var(--navy) 20%, transparent);
}

@media (prefers-reduced-motion: reduce) {
  .person-card--coordinator {
    transition: none !important;
  }
  .person-card--coordinator:hover {
    transform: none;
  }
}
</style>
