<template>
  <div class="w-full overflow-hidden rounded-lg shadow-xs hidden md:block">
    <div class="w-full overflow-x-auto">
      <div
        v-if="!props.no_paginate"
        class="mt-4 flex flex-column sm:flex-row flex-wrap space-y-4 sm:space-y-0 items-center justify-between pb-4"
      >
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <label for="per-page" class="whitespace-nowrap">Tampilkan</label>
          <div class="relative">
            <select
              v-model="selectedPerPage"
              id="per-page"
              name="per-page"
              class="appearance-none border border-gray-300 rounded-lg text-sm text-gray-700 pl-3 pr-8 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-[#0F2A4A] dark:bg-new-dark-primary dark:text-gray-300 dark:border-new-dark-secondary"
            >
              <option v-for="option in perPageOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
            <!-- Dulu pakai panah bawaan browser tanpa jarak kanan, jadi kelihatan nempel/motong
            angka di select ini (beda sama select Status yang lebih lebar jadi nggak masalah) -
            sekarang appearance-none + ikon panah sendiri yang punya jarak (pr-8) dari teksnya. -->
            <svg
              class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <span class="whitespace-nowrap">data</span>
        </div>
        <label for="table-search" class="sr-only">Cari</label>
        <div v-if="props.show_search" class="relative">
          <div
            class="absolute inset-y-0 left-0 rtl:inset-r-0 rtl:right-0 flex items-center ps-3 pointer-events-none"
          >
            <svg
              class="w-5 h-5 text-gray-500 dark:text-gray-400"
              aria-hidden="true"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </div>
          <input
            v-model="searchQuery"
            @input="search"
            type="text"
            id="table-search"
            class="block p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-new-dark-secondary dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
            placeholder="Cari..."
          />
        </div>
      </div>

      <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead
          class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-new-dark-secondary dark:text-gray-300"
        >
          <tr>
            <th v-if="props.number" scope="col" class="px-6 py-3">No</th>

            <th
              scope="col"
              class="px-6 py-3"
              v-for="{ key, label, button, checkbox } in props.headers"
              :key="key"
            >
              <p v-if="button">Action</p>
              <p v-else-if="checkbox">
                <input
                  class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                  type="checkbox"
                  @change="toggleSelectAll"
                />
              </p>
              <p v-else>
                {{ label }}
              </p>
            </th>
            <th v-if="props.hidden" scope="col" class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody v-if="props.loading && itemTableLength === 0">
          <tr
            class="bg-white border-b dark:bg-new-dark-primary dark:border-new-dark-darker"
          >
            <td class="px-6 py-8 text-center" :colspan="totalColumns">
              <span class="inline-flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400">
                <svg
                  class="animate-spin h-4 w-4 shrink-0"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                  ></path>
                </svg>
                Memuat data...
              </span>
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="itemTableLength === 0">
          <tr
            class="bg-white border-b dark:bg-new-dark-primary dark:border-new-dark-darker hover:bg-gray-50 dark:hover:bg-new-dark-secondary dark:hover:text-blue-400"
          >
            <td class="px-6 py-8 text-center" :colspan="totalColumns">Data tidak ditemukan</td>
          </tr>
        </tbody>
        <tbody
          class="text-gray-700 dark:text-gray-400"
          v-else
          v-for="(p, index) in itemTable"
          :key="p"
        >
          <tr
            class="bg-white border-b dark:bg-new-dark-primary dark:border-new-dark-darker hover:bg-gray-50 dark:hover:bg-new-dark-secondary hover:text-blue-500 dark:hover:text-blue-400"
          >
            <td v-if="props.number && !props.no_paginate" class="px-6 py-1 w-3 text-center">
              {{ (props.dataTable.page - 1) * props.dataTable.per_page + index + 1 }}
            </td>
            <td v-if="props.number && props.no_paginate" class="px-6 py-1 w-3 text-center">
              {{ index + 1 }}
            </td>
            <td
              v-for="{ key, button, condition, style, view, checkbox, image } in props.headers"
              :key="key"
              class="px-6 whitespace-nowrap"
            >
              <div v-if="button">
                <div class="flex items-center space-x-4 text-sm">
                  <div v-for="btn in button" :key="btn">
                    <button
                      @click.prevent="handleClick(btn, p)"
                      :class="buttonClasses[btn]"
                      class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 rounded-lg focus:outline-none focus:shadow-outline-gray"
                      :aria-label="btn"
                    >
                      <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                        <path
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          :d="svgPaths[btn]"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              <div v-else-if="checkbox">
                <input
                  class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                  type="checkbox"
                  :value="p[checkbox]"
                  v-model="selectedCheckboxes"
                />
              </div>
              <div v-else-if="image">
                <img class="w-10 h-10 rounded-full" :src="p[image]" :alt="'img' + key" />
              </div>
              <div
                v-else-if="condition && condition[p[key]]"
                @click="props.click_body ? buttonBody(p) : null"
                class="flex items-center space-x-4 text-sm"
              >
                <span
                  class="text-xs font-semibold inline-block py-1 last:mr-0 mr-1 px-2 uppercase rounded-full"
                  :class="conditionClasses[condition[p[key]].color]"
                >
                  {{ condition[p[key]].name }}
                </span>
              </div>
              <div v-else-if="view && view === 'xhr'">
                <p class="blog" v-html="sanitize(p[key])"></p>
              </div>
              <div
                v-else
                @click="props.click_body ? buttonBody(p) : null"
                :class="{ 'font-bold': style && style == 'bold' }"
              >
                {{ p[key] }}
              </div>
            </td>
            <td v-if="props.hidden">
              <button
                type="button"
                @click.prevent="buttonToggleHiddenIndex(index)"
                class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-yellow-500 md:dark:text-gray-400 dark:hover:text-yellow-400 rounded-lg dark:text-yellow-400 focus:outline-none focus:shadow-outline-gray"
              >
                <svg
                  class="w-6 h-6"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 7.757v8.486M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                  />
                </svg>
              </button>
            </td>
          </tr>

          <tr v-if="props.hidden && openHiddenIndex === index">
            <td colspan="5" class="px-6 py-4">
              <div class="border rounded-md overflow-hidden shadow-md dark:border-new-dark-light">
                <table class="w-full dark:bg-new-dark-secondary">
                  <tbody>
                    <tr v-for="{ hidden_key, hidden_label } in props.hidden" :key="hidden_key">
                      <td class="py-2 px-4 border-r border-b dark:border-new-dark-light">
                        {{ hidden_label }}
                      </td>
                      <td class="py-2 px-4 border-b dark:border-new-dark-light">
                        {{ p[hidden_key] }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <!-- Mobile Responsive Table -->

  <div class="grid grid-cols-12 md:hidden" v-for="(p, index) in itemTable" :key="index">
    <div
      class="col-span-12 relative w-auto max-w-6xl"
      :class="openHiddenIndex === index ? 'mb-8' : ''"
    >
      <div
        class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white dark:bg-new-dark-primary outline-none focus:outline-none"
      >
        <div
          class="items-start justify-between p-5 border-b border-solid border-gray-300 dark:border-gray-500 rounded-t"
        >
          <div
            @click="props.click_body ? buttonBody(p) : null"
            class="flex items-center justify-between gap-4 text-xs font-semibold"
          >
            <!-- Title -->
            <p class="truncate">
              <span v-if="!no_paginate">
                # {{ (props.dataTable.page - 1) * props.dataTable.per_page + index + 1 }}
              </span>
              <span v-else> #{{ index + 1 }}</span>
              {{ p[headerTitle?.key] }}
            </p>

            <!-- Right Side: Nilai + Button -->
            <div class="flex items-center gap-2">
              <div
                v-if="headerNilai && p[headerNilai.key] != null"
                class="w-10 h-10 flex items-center justify-center rounded-full font-bold text-lg"
                :class="styleNilai(p[headerNilai.key])"
              >
                {{ String(p[headerNilai.key])[0] }}
              </div>
              <button
                type="button"
                @click="buttonToggleHiddenIndex(index)"
                class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 text-yellow-500 rounded-lg dark:text-yellow-400 focus:outline-none focus:shadow-outline-gray"
                aria-label="Delete"
              >
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
                    d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>
        <div v-if="openHiddenIndex === index" class="text-xs">
          <!--  -->
          <div
            class="items-start justify-between p-5 border-b border-solid border-gray-300 dark:border-gray-500 rounded-t"
          >
            <p class="font-semibold text-blue-500">{{ p[headerTitle?.key] }}</p>
          </div>
          <div
            v-for="{ key, button, condition, label, view, image } in props.headers.filter(
              (h) => h.view !== 'title' && h.view !== 'nilai'
            )"
            :key="key"
            class="items-start justify-between p-5 border-b border-solid border-gray-300 dark:border-gray-500 rounded-t"
          >
            <div
              v-if="condition && condition[p[key]]"
              @click="props.click_body ? buttonBody(p) : null"
              class="text-gray-700 dark:text-gray-400"
            >
              <span class="font-semibold">{{ label }}</span> :
              <span
                class="text-xs font-semibold inline-block py-1 last:mr-0 mr-1 px-2 uppercase rounded-full"
                :class="conditionClasses[condition[p[key]].color]"
              >
                {{ condition[p[key]].name }}
              </span>
            </div>
            <div v-else-if="image">
              <span class="font-semibold">{{ label }}</span> :
              <div v-if="p[image]" class="flex items-center justify-center h-20">
                <img class="w-20 h-20 rounded-full" :src="p[image]" :alt="label + p[key]" />
              </div>
              <div v-else>No photo</div>
            </div>
            <div v-else-if="view && view === 'xhr'">
              <span class="font-semibold">{{ label }}</span> :
              <p class="blog" v-html="sanitize(p[key])"></p>
            </div>
            <div
              v-else-if="label"
              class="text-gray-700 dark:text-gray-400 text-sm"
              @click="props.click_body ? buttonBody(p) : null"
            >
              <span class="font-semibold">{{ label }} :</span> {{ p[key] }}
            </div>
            <div v-else-if="button" class="flex items-center justify-end">
              <div v-for="btn in button" :key="btn">
                <button
                  @click.prevent="handleClick(btn, p)"
                  :class="buttonClasses[btn]"
                  class="flex items-center justify-between px-2 py-2 text-sm font-medium leading-5 rounded-lg focus:outline-none focus:shadow-outline-gray"
                  :aria-label="btn"
                >
                  <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="svgPaths[btn]"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <nav
    v-if="!props.no_paginate"
    class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
    aria-label="Table navigation"
  >
    <span
      class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto"
      >Showing
      <span class="font-semibold text-gray-900 dark:text-white"
        >{{ props.dataTable.from }}-{{ props.dataTable.to }}</span
      >
      of
      <span class="font-semibold text-gray-900 dark:text-white">{{
        props.dataTable.total
      }}</span></span
    >
    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
      <li>
        <a
          href="#"
          @click.prevent="onClickPreviousPage"
          :class="hitung.isInFirstPage ? 'disabled' : ''"
          :disabled="hitung.isInFirstPage"
          class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-new-dark-primary dark:border-new-dark-darker dark:text-gray-400 dark:hover:bg-new-dark-secondary dark:hover:text-white"
          >Previous</a
        >
      </li>
      <li v-for="page in hitung.pages" :key="page.id">
        <a
          href="#"
          @click.prevent="onClickPage(page.name)"
          :disabled="page.isDisabled"
          :class="[
            isPageActive(page.name)
              ? ' dark:bg-new-dark-secondary dark:text-blue-400 dark:hover:text-blue-500 dark:border-new-dark-darker hover:text-blue-700 hover:bg-blue-100 bg-blue-50 text-blue-600 '
              : ' dark:hover:bg-new-dark-secondary dark:hover:text-white dark:text-gray-400 dark:bg-new-dark-primary hover:text-gray-700 hover:bg-gray-100 bg-white leading-tight text-gray-500'
          ]"
          class="flex items-center justify-center px-3 h-8 border border-gray-300 dark:border-new-dark-darker"
        >
          {{ page.name }}</a
        >
      </li>
      <li v-if="!hitung.isInLastPage && !hitung.isInFirstPage && props.dataTable.last_page - 1">
        <a
          href="#"
          :disabled="hitung.isInLastPage"
          class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-new-dark-primary dark:border-new-dark-darker dark:text-gray-400 dark:hover:bg-new-dark-secondary dark:hover:text-white"
          >...</a
        >
      </li>
      <li v-if="!hitung.isInLastPage && !hitung.isInFirstPage && props.dataTable.last_page - 1">
        <a
          href="#"
          @click.prevent="onClickPage(props.dataTable.last_page)"
          :disabled="hitung.isInLastPage"
          class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-new-dark-primary dark:border-new-dark-darker dark:text-gray-400 dark:hover:bg-new-dark-secondary dark:hover:text-white"
          >{{ props.dataTable.last_page }}</a
        >
      </li>
      <li>
        <a
          href="#"
          :class="hitung.isInLastPage ? 'disabled' : ''"
          :disabled="hitung.isInLastPage"
          @click.prevent="onClickNextPage"
          class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-new-dark-primary dark:border-new-dark-darker dark:text-gray-400 dark:hover:bg-new-dark-secondary dark:hover:text-white"
          >Next</a
        >
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed, reactive, ref, watch, watchEffect } from 'vue'


import DOMPurify from 'dompurify'
const props = defineProps({
  headers: {
    type: Array,
    required: true
  },
  hidden: {
    type: Array,
    required: false
  },

  number: {
    type: Boolean,
    default: true
  },

  maxVisibleButtons: {
    type: Number,
    required: false,
    default: 3
  },

  dataTable: {
    type: [Object, Array],
    required: true
  },
  show_search: {
    type: Boolean,
    default: false
  },
  no_paginate: {
    type: Boolean,
    default: false
  },
  click_body: {
    type: Boolean,
    default: false
  },
  checkBoxes: {
    type: Boolean,
    required: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'per_page',
  'search',
  'pagechanged',
  'delete',
  'valid',
  'restore',
  'file',
  'cancel',
  'detail',
  'edit',
  'print',
  'body',
  'updateCheckboxes'
])

const buttonClasses = reactive({
  Delete: '  text-red-500  md:dark:text-gray-400 dark:text-red-400 dark:hover:text-red-400 ',
  Edit: ' text-blue-500  md:dark:text-gray-400 dark:text-blue-400 dark:hover:text-blue-400 ',
  Detail: ' text-yellow-500 md:dark:text-gray-400 dark:text-yellow-400 dark:hover:text-yellow-400',
  Restore: 'text-green-500 md:dark:text-gray-400 dark:text-green-400 dark:hover:text-green-400',
  File: 'text-green-500 md:dark:text-gray-400 dark:text-green-400 dark:hover:text-green-400',
  Print: 'text-blue-500  md:dark:text-gray-400 dark:text-blue-400 dark:hover:text-blue-400',
  Valid: 'text-green-500 md:dark:text-gray-400 dark:text-green-400 dark:hover:text-green-400',
  Cancel: 'text-red-500  md:dark:text-gray-400 dark:text-red-400 dark:hover:text-red-400 '
})

const conditionClasses = reactive({
  green:
    ' text-green-600 bg-green-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-green-400  dark:text-green-400',
  gray: 'text-gray-600 bg-gray-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-gray-400 dark:text-gray-400',
  red: 'text-red-600 bg-red-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-red-400 dark:text-red-400',
  yellow:
    'text-yellow-600 bg-yellow-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-yellow-400 dark:text-yellow-400',
  blue: 'text-blue-600 bg-blue-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-blue-400 dark:text-blue-400',
  indigo:
    'text-indigo-600 bg-indigo-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-indigo-400 dark:text-indigo-400',
  purple:
    'text-purple-600 bg-purple-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-purple-400 dark:text-purple-400',
  pink: 'text-pink-600 bg-pink-200 md:dark:text-gray-400  dark:bg-new-dark-darker dark:hover:text-pink-400 dark:text-pink-400'
})

const svgPaths = reactive({
  Delete:
    'M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z',
  Edit: 'm14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z',
  Detail:
    'M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
  Restore: 'M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4',
  File: 'M10 3v4a1 1 0 0 1-1 1H5m14-4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z',
  Print:
    ' M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z',
  Valid: 'M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
  Cancel: 'm15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
})

const handleClick = (button, p) => {
  if (button === 'Delete') {
    deleteThis(p)
  } else if (button === 'Edit') {
    editThis(p)
  } else if (button === 'Detail') {
    detailThis(p)
  } else if (button === 'Restore') {
    restoreThis(p)
  } else if (button === 'File') {
    fileThis(p)
  } else if (button === 'Print') {
    printThis(p)
  } else if (button === 'Valid') {
    validThis(p)
  } else if (button === 'Cancel') {
    cancelThis(p)
  }
}
const selectedPerPage = ref(0)
const searchQuery = ref('')
const perPageOptions = [5, 10, 25, 50, 100]
const openHiddenIndex = ref(null)
const selectedCheckboxes = ref([])
const buttonToggleHiddenIndex = async (index) => {
  openHiddenIndex.value = openHiddenIndex.value === index ? null : index
}

const emitPerPageChange = () => {
  emit('per_page', selectedPerPage.value)
}
const search = () => {
  emit('search', searchQuery.value)
}

const itemTable = computed(() => {
  if (props.dataTable && props.dataTable.data) {
    return props.dataTable.data
  } else {
    return props.dataTable
  }
})
const itemTableLength = computed(() => {
  if (props.dataTable && props.dataTable.data) {
    return props.dataTable.data.length
  } else {
    return props.dataTable.length
  }
})
// Total kolom tabel (No + semua header + kolom hidden), dipakai buat colspan baris "Memuat
// data..."/"Data Not Found" supaya nge-span penuh lebar tabel, bukan cuma nyempil di kolom
// pertama doang.
const totalColumns = computed(() => {
  let total = props.headers.length
  if (props.number) total += 1
  if (props.hidden) total += 1
  return total
})
// watch(
//   () => props.dataTable.per_page,
//   (newPageSize) => {
//     selectedPerPage.value = newPageSize
//   }
// )
watchEffect(() => {
  if (props.dataTable.per_page) {
    selectedPerPage.value = props.dataTable.per_page
  }
  if (props.checkBoxes) {
    selectedCheckboxes.value = []
  }
})

watch(selectedPerPage, () => {
  emitPerPageChange()
})
const deleteThis = (e) => {
  emit('delete', e)
}
const buttonBody = (e) => {
  emit('body', e)
}
const editThis = (e) => {
  emit('edit', e)
}
const detailThis = (e) => {
  emit('detail', e)
}
const restoreThis = (e) => {
  emit('restore', e)
}
const validThis = (e) => {
  emit('valid', e)
}
const fileThis = (e) => {
  emit('file', e)
}
const cancelThis = (e) => {
  emit('cancel', e)
}
const printThis = (e) => {
  emit('print', e)
}
const onClickPreviousPage = () => {
  if (hitung.isInFirstPage) {
    return false
  }
  emit('pagechanged', props.dataTable.page - 1)
}
const onClickPage = (page) => {
  emit('pagechanged', page)
}
const onClickNextPage = () => {
  if (hitung.isInLastPage) {
    return false
  }
  emit('pagechanged', props.dataTable.page + 1)
}
const isPageActive = (page) => {
  return props.dataTable.page === page
}
const hitung = reactive({
  isInFirstPage: computed(() => {
    return props.dataTable.page === 1
  }),
  isInLastPage: computed(() => {
    if (props.dataTable.last_page === 0) {
      return true
    }
    return props.dataTable.page === props.dataTable.last_page
  }),

  startPage: computed(() => {
    if (props.dataTable.page === 1) {
      return 1
    }
    if (props.dataTable.last_page < props.maxVisibleButtons) {
      return 1
    }
    if (props.dataTable.page === props.dataTable.last_page) {
      return props.dataTable.last_page - props.maxVisibleButtons + 1
    }
    return props.dataTable.page - 1
  }),
  endPage: computed(() => {
    if (props.dataTable.last_page === 0) {
      return 1
    }
    if (props.dataTable.last_page < props.maxVisibleButtons) {
      return props.dataTable.last_page
    }
    return Math.min(hitung.startPage + props.maxVisibleButtons - 1, props.dataTable.last_page)
  }),
  pages: computed(() => {
    const range = []
    for (let i = hitung.startPage; i <= hitung.endPage; i++) {
      range.push({
        name: i,
        isDisabled: i === props.dataTable.page
      })
    }
    return range
  })
})
const sanitize = (content) => {
  return DOMPurify.sanitize(content)
}

const toggleSelectAll = (event) => {
  if (event.target.checked) {
    if (props.dataTable && props.dataTable.data) {
      selectedCheckboxes.value = props.dataTable.data.map(
        (item) => item[props.headers.find((h) => h.checkbox)?.checkbox]
      )
    } else {
      selectedCheckboxes.value = props.dataTable.map(
        (item) => item[props.headers.find((h) => h.checkbox)?.checkbox]
      )
    }
  } else {
    selectedCheckboxes.value = []
  }
}

watch(selectedCheckboxes, (newVal) => {
  emit('updateCheckboxes', newVal)
})
const styleNilai = (item) => {
  switch (item) {
    case 'D':
      return 'bg-yellow-200 dark:bg-yellow-100 text-yellow-700 dark:text-yellow-500'
    case 'E':
      return 'bg-red-200 dark:bg-red-100 text-red-700 dark:text-red-500'
    case 'A':
      return 'bg-green-200 dark:bg-green-100 text-green-700 dark:text-green-500'
    default:
      return 'bg-blue-200 dark:bg-blue-100 text-blue-700 dark:text-blue-500'
  }
}
const headerTitle = computed(() => props.headers.find((h) => h.view === 'title'))

const headerNilai = computed(() => props.headers.find((h) => h.view === 'nilai'))
</script>

<style lang="scss" scoped></style>
