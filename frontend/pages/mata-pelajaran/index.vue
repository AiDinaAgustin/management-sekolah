<template>
  <div class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="flex flex-col gap-5 border-b border-slate-200/80 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div class="flex-1">
            <label class="relative block">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M10 4a6 6 0 1 0 3.874 10.582l4.272 4.272 1.414-1.414-4.272-4.272A6 6 0 0 0 10 4Zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z" />
                </svg>
              </span>
              <input v-model="search" type="text" placeholder="Cari nama atau kode mapel..." class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-300 focus:bg-white lg:max-w-xs" />
            </label>
          </div>

          <div class="flex flex-wrap gap-3 lg:justify-end">
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="fetchSubjects">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Refresh
            </button>
            <NuxtLink to="/mata-pelajaran/tambah" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6v-2Z" />
              </svg>
              Tambah Mata Pelajaran
            </NuxtLink>
          </div>
        </div>
      </div>

      <div v-if="pending" class="p-6 text-sm text-slate-500">Memuat data mata pelajaran...</div>
      <div v-else-if="errorMessage" class="p-6 text-sm text-rose-500">{{ errorMessage }}</div>
      <div v-else class="overflow-x-auto px-5 py-5">
        <table class="min-w-full text-sm">
          <thead class="border-b border-slate-300 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
            <tr>
              <th class="px-4 py-4 font-medium">Icon</th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('kode_mapel')">
                  <span>Kode</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('kode_mapel') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('nama_mapel')">
                  <span>Nama Mata Pelajaran</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('nama_mapel') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="subject in subjects.data" :key="subject.id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
              <td class="px-4 py-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M5 4.75A1.75 1.75 0 0 1 6.75 3h8.586c.464 0 .909.184 1.237.513l2.914 2.914c.329.328.513.773.513 1.237v11.586A1.75 1.75 0 0 1 18.25 21h-11.5A1.75 1.75 0 0 1 5 19.25V4.75ZM15 4.81V7a1 1 0 0 0 1 1h2.19L15 4.81Z" />
                  </svg>
                </span>
              </td>
              <td class="px-4 py-4 text-slate-600">{{ subject.kode_mapel }}</td>
              <td class="px-4 py-4">
                <div>
                  <p class="font-semibold text-slate-900">{{ subject.nama_mapel }}</p>
                  <p class="text-xs text-slate-400">ID #{{ subject.id }}</p>
                </div>
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center justify-end gap-2">
                  <NuxtLink :to="`/mata-pelajaran/${subject.id}/edit`" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600" aria-label="Edit mata pelajaran">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="m3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.25H5v-.92l8.06-8.06.92.92L5.92 19.5ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.13 1.13 3.75 3.75 1.14-1.13Z" />
                    </svg>
                  </NuxtLink>
                  <button type="button" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-60" :disabled="actionLoadingId === subject.id" aria-label="Hapus mata pelajaran" @click="deleteSubject(subject)">
                    <svg v-if="actionLoadingId !== subject.id" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="M9 3a1 1 0 0 0-1 1v1H5a1 1 0 1 0 0 2h1l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12h1a1 1 0 1 0 0-2h-3V4a1 1 0 0 0-1-1H9Zm2 2h2v1h-2V5Zm-1 5a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1Zm5 1a1 1 0 1 0-2 0v6a1 1 0 1 0 2 0v-6Z" />
                    </svg>
                    <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" />
                      <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!subjects.data.length">
              <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data mata pelajaran yang cocok.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <p>Menampilkan {{ subjects.meta.from || 0 }}-{{ subjects.meta.to || 0 }} dari {{ subjects.meta.total }} data</p>
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="subjects.meta.current_page <= 1 || pending" @click="page--">Prev</button>
          <span class="px-2 font-semibold text-slate-700">Page {{ subjects.meta.current_page }} / {{ subjects.meta.last_page }}</span>
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="subjects.meta.current_page >= subjects.meta.last_page || pending" @click="page++">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PaginatedResponse, SubjectItem } from '~/types/subject';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Master Data',
  pageTitle: 'Mata Pelajaran',
  pageDescription: 'Kelola daftar mata pelajaran, pencarian, dan aksi data mapel.',
});

const toast = useToast();
const search = ref('');
const page = ref(1);
const sortBy = ref<'kode_mapel' | 'nama_mapel'>('nama_mapel');
const sortDirection = ref<'asc' | 'desc'>('asc');
const pending = ref(false);
const errorMessage = ref('');
const actionLoadingId = ref<number | null>(null);

const emptyState: PaginatedResponse<SubjectItem> = {
  data: [],
  links: [],
  meta: {
    current_page: 1,
    from: null,
    last_page: 1,
    path: '',
    per_page: 10,
    to: null,
    total: 0,
  },
};

const subjects = ref<PaginatedResponse<SubjectItem>>(emptyState);
let debounceHandle: ReturnType<typeof setTimeout> | null = null;

const fetchSubjects = async () => {
  pending.value = true;
  errorMessage.value = '';

  try {
    subjects.value = await useApi<PaginatedResponse<SubjectItem>>('/subjects', {
      method: 'GET',
      query: {
        page: page.value,
        per_page: 10,
        search: search.value || undefined,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
      },
    });
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data mata pelajaran.';
  } finally {
    pending.value = false;
  }
};

const toggleSort = async (column: 'kode_mapel' | 'nama_mapel') => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }

  page.value = 1;
  await fetchSubjects();
};

const getSortIcon = (column: 'kode_mapel' | 'nama_mapel') => {
  if (sortBy.value !== column) {
    return '↕';
  }

  return sortDirection.value === 'asc' ? '↑' : '↓';
};

const deleteSubject = async (subject: SubjectItem) => {
  if (!window.confirm(`Hapus data mata pelajaran ${subject.nama_mapel}?`)) {
    return;
  }

  actionLoadingId.value = subject.id;

  try {
    await useApi(`/subjects/${subject.id}`, { method: 'DELETE' });

    toast.success(`Mata pelajaran ${subject.nama_mapel} berhasil dihapus.`);

    if (subjects.value.data.length === 1 && page.value > 1) {
      page.value -= 1;
    } else {
      await fetchSubjects();
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal menghapus data mata pelajaran.';
    toast.error(errorMessage.value);
  } finally {
    actionLoadingId.value = null;
  }
};

watch(page, fetchSubjects, { immediate: true });

watch(search, () => {
  page.value = 1;

  if (debounceHandle) {
    clearTimeout(debounceHandle);
  }

  debounceHandle = setTimeout(fetchSubjects, 400);
});
</script>
