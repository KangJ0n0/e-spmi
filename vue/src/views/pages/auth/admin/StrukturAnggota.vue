<template>

</template>

<script setup>
import useStrukturAnggota from '@/composables/strukturanggota.js'
import tableComponent from '@/components/TableComponent.vue'
const { data_struktur_anggota, getStrukturAnggota, storeStrukturAnggota, updateStrukturAnggota, destroyStrukturAnggota} = useStrukturAnggota()
</script>

