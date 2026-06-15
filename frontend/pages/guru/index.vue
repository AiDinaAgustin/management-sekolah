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
              <input v-model="search" type="text" placeholder="Cari nama, NIP, atau email..." class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-300 focus:bg-white lg:max-w-xs" />
            </label>
          </div>

          <div class="flex flex-wrap gap-3 lg:justify-end">
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="fetchTeachers">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Refresh
            </button>
            <NuxtLink to="/guru/tambah" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6v-2Z" />
              </svg>
              Tambah Guru
            </NuxtLink>
          </div>
        </div>
      </div>

      <div v-if="pending" class="p-6 text-sm text-slate-500">Memuat data guru...</div>
      <div v-else-if="errorMessage" class="p-6 text-sm text-rose-500">{{ errorMessage }}</div>
      <div v-else class="overflow-x-auto px-5 py-5">
        <table class="min-w-full text-sm">
          <thead class="border-b border-slate-300 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
            <tr>
              <th class="px-4 py-4 font-medium">Photo</th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('nama')">
                  <span>Nama Guru</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('nama') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('nip')">
                  <span>NIP</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('nip') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">
                <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('email')">
                  <span>Email</span>
                  <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('email') }}</span>
                </button>
              </th>
              <th class="px-4 py-4 font-medium">No. HP</th>
              <th class="px-4 py-4 font-medium">Wali Kelas</th>
              <th class="px-4 py-4 font-medium text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="teacher in teachers.data" :key="teacher.id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
              <td class="px-4 py-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z" />
                  </svg>
                </span>
              </td>
              <td class="px-4 py-4">
                <div>
                  <p class="font-semibold text-slate-900">{{ teacher.nama }}</p>
                  <p class="text-xs text-slate-400">ID #{{ teacher.id }}</p>
                </div>
              </td>
              <td class="px-4 py-4 text-slate-600">{{ teacher.nip }}</td>
              <td class="px-4 py-4 text-slate-600">{{ teacher.email }}</td>
              <td class="px-4 py-4 text-slate-600">{{ teacher.no_hp || '-' }}</td>
              <td class="px-4 py-4 text-slate-600">{{ teacher.jumlah_kelas ?? 0 }}</td>
              <td class="px-4 py-4">
                <div class="flex items-center justify-end gap-2">
                  <NuxtLink :to="`/guru/${teacher.id}/edit`" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600" aria-label="Edit guru">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path d="m3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.25H5v-.92l8.06-8.06.92.92L5.92 19.5ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.13 1.13 3.75 3.75 1.14-1.13Z" />
                    </svg>
                  </NuxtLink>
                  <button type="button" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-60" :disabled="actionLoadingId === teacher.id" aria-label="Hapus guru" @click="deleteTeacher(teacher)">
                    <svg v-if="actionLoadingId !== teacher.id" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
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
            <tr v-if="!teachers.data.length">
              <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data guru yang cocok.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <p>Menampilkan {{ teachers.meta.from || 0 }}-{{ teachers.meta.to || 0 }} dari {{ teachers.meta.total }} data</p>
        <div class="flex items-center gap-2">
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="teachers.meta.current_page <= 1 || pending" @click="page--">Prev</button>
          <span class="px-2 font-semibold text-slate-700">Page {{ teachers.meta.current_page }} / {{ teachers.meta.last_page }}</span>
          <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="teachers.meta.current_page >= teachers.meta.last_page || pending" @click="page++">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PaginatedResponse, TeacherItem } from '~/types/teacher';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Master Data',
  pageTitle: 'Data Guru',
  pageDescription: 'Kelola daftar guru, pencarian, dan aksi data guru.',
});

const toast = useToast();
const search = ref('');
const page = ref(1);
const sortBy = ref<'nama' | 'nip' | 'email'>('nama');
const sortDirection = ref<'asc' | 'desc'>('asc');
const pending = ref(false);
const errorMessage = ref('');
const actionLoadingId = ref<number | null>(null);

const emptyState: PaginatedResponse<TeacherItem> = {
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

const teachers = ref<PaginatedResponse<TeacherItem>>(emptyState);
let debounceHandle: ReturnType<typeof setTimeout> | null = null;

const fetchTeachers = async () => {
  pending.value = true;
  errorMessage.value = '';

  try {
    teachers.value = await useApi<PaginatedResponse<TeacherItem>>('/teachers', {
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
    errorMessage.value = error?.data?.message || 'Gagal memuat data guru.';
  } finally {
    pending.value = false;
  }
};

const toggleSort = async (column: 'nama' | 'nip' | 'email') => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }

  page.value = 1;
  await fetchTeachers();
};

const getSortIcon = (column: 'nama' | 'nip' | 'email') => {
  if (sortBy.value !== column) {
    return '↕';
  }

  return sortDirection.value === 'asc' ? '↑' : '↓';
};

const deleteTeacher = async (teacher: TeacherItem) => {
  if (!window.confirm(`Hapus data guru ${teacher.nama}?`)) {
    return;
  }

  actionLoadingId.value = teacher.id;

  try {
    await useApi(`/teachers/${teacher.id}`, { method: 'DELETE' });

    toast.success(`Data guru ${teacher.nama} berhasil dihapus.`);

    if (teachers.value.data.length === 1 && page.value > 1) {
      page.value -= 1;
    } else {
      await fetchTeachers();
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal menghapus data guru.';
    toast.error(errorMessage.value);
  } finally {
    actionLoadingId.value = null;
  }
};

watch(page, fetchTeachers, { immediate: true });

watch(search, () => {
  page.value = 1;

  if (debounceHandle) {
    clearTimeout(debounceHandle);
  }

  debounceHandle = setTimeout(fetchTeachers, 400);
});
</script>
