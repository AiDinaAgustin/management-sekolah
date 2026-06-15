<template>
  <div class="space-y-6">
    <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="flex flex-col gap-5 border-b border-slate-200/80 p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex-1 space-y-3">
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="viewMode === 'data' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'border border-slate-300 bg-white text-slate-600 hover:border-indigo-300 hover:bg-indigo-50'"
                @click="viewMode = 'data'"
              >
                Data Jadwal
              </button>
              <button
                type="button"
                class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition"
                :class="viewMode === 'preview' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'border border-slate-300 bg-white text-slate-600 hover:border-indigo-300 hover:bg-indigo-50'"
                @click="viewMode = 'preview'"
              >
                Preview Jadwal
              </button>
            </div>

            <label class="relative block">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M10 4a6 6 0 1 0 3.874 10.582l4.272 4.272 1.414-1.414-4.272-4.272A6 6 0 0 0 10 4Zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z" />
                </svg>
              </span>
              <input
                v-model="search"
                type="text"
                :placeholder="viewMode === 'data' ? 'Cari kelas, guru, atau mapel...' : 'Cari jadwal untuk preview...'"
                class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm outline-none transition focus:border-indigo-300 focus:bg-white lg:max-w-xs"
              />
            </label>
          </div>

          <div class="flex flex-wrap gap-3 xl:justify-end">
            <template v-if="viewMode === 'preview'">
              <div class="inline-flex rounded-2xl border border-slate-300 bg-white p-1">
                <button
                  v-for="mode in previewModes"
                  :key="mode.value"
                  type="button"
                  class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                  :class="previewMode === mode.value ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50'"
                  @click="previewMode = mode.value"
                >
                  {{ mode.label }}
                </button>
              </div>
            </template>

            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50" @click="isFilterModalOpen = true">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M3 5h18v2l-7 7v5l-4-2v-3L3 7V5Z" />
              </svg>
              Filter
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50" @click="fetchSchedules">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Refresh
            </button>
            <NuxtLink v-if="viewMode === 'data' && auth.user?.role !== 'guru'" to="/jadwal-pelajaran/tambah" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M19 11h-6V5h-2v6H5v2h6v6h2v-6h6v-2Z" />
              </svg>
              Tambah Jadwal
            </NuxtLink>
          </div>
        </div>
      </div>

      <div v-if="activeFilterLabel" class="border-b border-slate-100 px-5 py-4 text-sm font-medium text-indigo-500">
        {{ activeFilterLabel }}
      </div>

      <div v-if="viewMode === 'preview'" class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 text-sm text-slate-500">
        Preview ini bersifat template akademik berdasarkan hari dan jam, jadi tampil seperti planner kalender untuk jadwal rutin.
      </div>

      <div v-if="pending" class="p-6 text-sm text-slate-500">Memuat data jadwal...</div>
      <div v-else-if="errorMessage" class="p-6 text-sm text-rose-500">{{ errorMessage }}</div>

      <template v-else-if="viewMode === 'data'">
        <div class="overflow-x-auto px-5 py-5">
          <table class="min-w-full text-sm">
            <thead class="border-b border-slate-300 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
              <tr>
                <th class="px-4 py-4 font-medium">
                  <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('hari')">
                    <span>Hari</span>
                    <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('hari') }}</span>
                  </button>
                </th>
                <th class="px-4 py-4 font-medium">
                  <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('jam_mulai')">
                    <span>Jam</span>
                    <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('jam_mulai') }}</span>
                  </button>
                </th>
                <th class="px-4 py-4 font-medium">
                  <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('kelas')">
                    <span>Kelas</span>
                    <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('kelas') }}</span>
                  </button>
                </th>
                <th class="px-4 py-4 font-medium">
                  <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('subject')">
                    <span>Mata Pelajaran</span>
                    <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('subject') }}</span>
                  </button>
                </th>
                <th class="px-4 py-4 font-medium">
                  <button type="button" class="inline-flex items-center gap-2" @click="toggleSort('teacher')">
                    <span>Guru</span>
                    <span class="text-base font-bold leading-none text-slate-400">{{ getSortIcon('teacher') }}</span>
                  </button>
                </th>
                <th class="px-4 py-4 font-medium">Ruangan</th>
                <th class="px-4 py-4 font-medium">Tahun Ajaran</th>
                <th v-if="auth.user?.role !== 'guru'" class="px-4 py-4 font-medium text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="schedule in schedules.data" :key="schedule.id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
                <td class="px-4 py-4 font-semibold text-slate-900">{{ schedule.hari }}</td>
                <td class="px-4 py-4 text-slate-600">{{ formatTime(schedule.jam_mulai) }} - {{ formatTime(schedule.jam_selesai) }}</td>
                <td class="px-4 py-4 text-slate-600">{{ schedule.kelas?.nama_kelas || '-' }}</td>
                <td class="px-4 py-4">
                  <div>
                    <p class="font-semibold text-slate-900">{{ schedule.subject?.nama_mapel || '-' }}</p>
                    <p class="text-xs text-slate-400">{{ schedule.subject?.kode_mapel || '-' }}</p>
                  </div>
                </td>
                <td class="px-4 py-4 text-slate-600">{{ schedule.teacher?.nama || '-' }}</td>
                <td class="px-4 py-4 text-slate-600">{{ schedule.ruangan || '-' }}</td>
                <td class="px-4 py-4 text-slate-600">{{ formatAcademicYear(schedule) }}</td>
                <td v-if="auth.user?.role !== 'guru'" class="px-4 py-4">
                  <div class="flex items-center justify-end gap-2">
                    <NuxtLink :to="`/jadwal-pelajaran/${schedule.id}/edit`" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600" aria-label="Edit jadwal">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.25H5v-.92l8.06-8.06.92.92L5.92 19.5ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.13 1.13 3.75 3.75 1.14-1.13Z" /></svg>
                    </NuxtLink>
                    <button type="button" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-500 disabled:cursor-not-allowed disabled:opacity-60" :disabled="actionLoadingId === schedule.id" aria-label="Hapus jadwal" @click="deleteSchedule(schedule)">
                      <svg v-if="actionLoadingId !== schedule.id" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 3a1 1 0 0 0-1 1v1H5a1 1 0 1 0 0 2h1l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12h1a1 1 0 1 0 0-2h-3V4a1 1 0 0 0-1-1H9Zm2 2h2v1h-2V5Zm-1 5a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1Zm5 1a1 1 0 1 0-2 0v6a1 1 0 1 0 2 0v-6Z" /></svg>
                      <svg v-else class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-30" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" /><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" /></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!schedules.data.length"><td :colspan="auth.user?.role === 'guru' ? 7 : 8" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data jadwal yang cocok.</td></tr>
            </tbody>
          </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
          <p>Menampilkan {{ schedules.meta.from || 0 }}-{{ schedules.meta.to || 0 }} dari {{ schedules.meta.total }} data</p>
          <div class="flex items-center gap-2">
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="schedules.meta.current_page <= 1 || pending" @click="page--">Prev</button>
            <span class="px-2 font-semibold text-slate-700">Page {{ schedules.meta.current_page }} / {{ schedules.meta.last_page }}</span>
            <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 disabled:opacity-50" :disabled="schedules.meta.current_page >= schedules.meta.last_page || pending" @click="page++">Next</button>
          </div>
        </div>
      </template>

      <template v-else>
        <div class="px-5 py-5">
          <div v-if="!previewSchedules.length" class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-sm text-slate-400">
            Belum ada data jadwal untuk ditampilkan di preview.
          </div>

          <template v-else-if="previewMode === 'weekly'">
            <div class="overflow-x-auto rounded-[1.5rem] border border-slate-300">
              <table class="min-w-[1100px] w-full border-collapse text-sm">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="border-b border-r border-slate-300 px-4 py-4 text-left font-semibold text-slate-500">Jam</th>
                    <th v-for="dayName in days" :key="dayName" class="border-b border-r border-slate-300 px-4 py-4 text-left font-semibold text-slate-500 last:border-r-0">
                      {{ dayName }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="slot in weeklyTimeSlots" :key="slot">
                    <td class="border-b border-r border-slate-300 bg-slate-50/70 px-4 py-4 font-semibold text-slate-700">
                      {{ slot }}
                    </td>
                    <td v-for="dayName in days" :key="`${slot}-${dayName}`" class="border-b border-r border-slate-300 align-top px-3 py-3 last:border-r-0">
                      <div v-if="weeklyMatrix[dayName]?.[slot]?.length" class="space-y-2">
                        <div v-for="item in weeklyMatrix[dayName][slot]" :key="item.id" class="rounded-2xl border border-indigo-100 bg-indigo-50/80 p-3">
                          <p class="font-semibold text-slate-900">{{ item.subject?.nama_mapel }}</p>
                          <p class="mt-1 text-xs text-slate-500">{{ item.kelas?.nama_kelas }} - {{ item.teacher?.nama }}</p>
                          <p class="mt-1 text-xs font-medium text-indigo-600">{{ formatTime(item.jam_mulai) }} - {{ formatTime(item.jam_selesai) }}<span v-if="item.ruangan"> - {{ item.ruangan }}</span></p>
                        </div>
                      </div>
                      <div v-else class="rounded-2xl border border-dashed border-slate-200 bg-white px-3 py-6 text-center text-xs text-slate-300">
                        Kosong
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>

          <template v-else-if="previewMode === 'monthly'">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
              <article v-for="dayName in days" :key="dayName" class="rounded-[1.5rem] border border-slate-300 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                  <h3 class="text-base font-semibold text-slate-900">{{ dayName }}</h3>
                  <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">{{ monthlyGroups[dayName]?.length || 0 }} slot</span>
                </div>
                <div class="mt-4 space-y-3">
                  <div v-for="item in monthlyGroups[dayName]" :key="item.id" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <p class="font-semibold text-slate-900">{{ item.subject?.nama_mapel }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ item.kelas?.nama_kelas }} - {{ item.teacher?.nama }}</p>
                      </div>
                      <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-500">{{ formatTime(item.jam_mulai) }}</span>
                    </div>
                    <p class="mt-2 text-xs font-medium text-indigo-600">{{ formatTime(item.jam_mulai) }} - {{ formatTime(item.jam_selesai) }}<span v-if="item.ruangan"> - {{ item.ruangan }}</span></p>
                  </div>
                  <div v-if="!monthlyGroups[dayName]?.length" class="rounded-2xl border border-dashed border-slate-200 px-3 py-8 text-center text-xs text-slate-300">
                    Belum ada jadwal
                  </div>
                </div>
              </article>
            </div>
          </template>

          <template v-else>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
              <article v-for="month in yearPreviewMonths" :key="month" class="rounded-[1.5rem] border border-slate-300 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                  <h3 class="text-base font-semibold text-slate-900">{{ month }}</h3>
                  <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">{{ previewSchedules.length }} pola</span>
                </div>
                <div class="mt-4 space-y-3">
                  <div v-for="dayName in days" :key="`${month}-${dayName}`" class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
                    <div class="flex items-center justify-between gap-3">
                      <p class="text-sm font-semibold text-slate-800">{{ dayName }}</p>
                      <span class="text-xs font-medium text-slate-400">{{ monthlyGroups[dayName]?.length || 0 }} slot</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                      <span v-for="item in monthlyGroups[dayName]?.slice(0, 3)" :key="`${month}-${dayName}-${item.id}`" class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-slate-500">
                        {{ item.subject?.kode_mapel }} - {{ formatTime(item.jam_mulai) }}
                      </span>
                      <span v-if="(monthlyGroups[dayName]?.length || 0) > 3" class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600">
                        +{{ (monthlyGroups[dayName]?.length || 0) - 3 }} lainnya
                      </span>
                    </div>
                  </div>
                </div>
              </article>
            </div>
          </template>
        </div>
      </template>
    </div>

    <div v-if="isFilterModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4">
      <div class="w-full max-w-md rounded-[2rem] border border-white/70 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.2)]">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Filter Jadwal</h2>
            <p class="mt-1 text-sm text-slate-500">Pilih filter untuk mempersempit jadwal pelajaran.</p>
          </div>
          <button type="button" class="text-slate-400 transition hover:text-slate-600" @click="closeFilterModal">x</button>
        </div>
        <div class="mt-6 space-y-4">
          <label class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Tahun Ajaran</span>
            <select v-model="draftAcademicYearId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua tahun ajaran</option>
              <option v-for="academicYear in academicYears" :key="academicYear.id" :value="String(academicYear.id)">{{ academicYear.nama_tahun_ajaran }} - {{ academicYear.semester }}</option>
            </select>
          </label>
          <label class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Kelas</span>
            <select v-model="draftClassroomId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua kelas</option>
              <option v-for="classroom in classrooms" :key="classroom.id" :value="String(classroom.id)">{{ classroom.nama_kelas }}</option>
            </select>
          </label>
          <label v-if="auth.user?.role !== 'guru'" class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Guru</span>
            <select v-model="draftTeacherId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua guru</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="String(teacher.id)">{{ teacher.nama }} - {{ teacher.nip }}</option>
            </select>
          </label>
          <label class="block space-y-2 text-sm font-medium text-slate-700">
            <span>Hari</span>
            <select v-model="draftDay" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Semua hari</option>
              <option v-for="dayOption in days" :key="dayOption" :value="dayOption">{{ dayOption }}</option>
            </select>
          </label>
        </div>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50" @click="resetFilters">Reset</button>
          <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200" @click="applyFilters">Terapkan Filter</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { AcademicYearOption, ClassroomOption, PaginatedResponse, ScheduleItem, TeacherOption } from '~/types/schedule';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Akademik',
  pageTitle: 'Jadwal Pelajaran',
  pageDescription: 'Kelola jadwal pelajaran per kelas, guru, hari, jam, dan preview planner.',
});

const auth = useAuthStore();
const toast = useToast();

const days: ScheduleItem['hari'][] = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const yearPreviewMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const previewModes = [
  { value: 'weekly', label: 'Mingguan' },
  { value: 'monthly', label: 'Bulanan' },
  { value: 'yearly', label: 'Tahunan' },
] as const;

const search = ref('');
const academicYearId = ref('');
const classroomId = ref('');
const teacherId = ref('');
const day = ref('');
const page = ref(1);
const sortBy = ref<'hari' | 'jam_mulai' | 'kelas' | 'teacher' | 'subject'>('hari');
const sortDirection = ref<'asc' | 'desc'>('asc');
const pending = ref(false);
const errorMessage = ref('');
const isFilterModalOpen = ref(false);
const actionLoadingId = ref<number | null>(null);
const draftAcademicYearId = ref('');
const draftClassroomId = ref('');
const draftTeacherId = ref('');
const draftDay = ref('');
const viewMode = ref<'data' | 'preview'>('data');
const previewMode = ref<'weekly' | 'monthly' | 'yearly'>('weekly');
const classrooms = ref<ClassroomOption[]>([]);
const academicYears = ref<AcademicYearOption[]>([]);
const teachers = ref<TeacherOption[]>([]);

const emptyState: PaginatedResponse<ScheduleItem> = {
  data: [],
  links: [],
  meta: { current_page: 1, from: null, last_page: 1, path: '', per_page: 10, to: null, total: 0 },
};

const schedules = ref<PaginatedResponse<ScheduleItem>>(emptyState);
let debounceHandle: ReturnType<typeof setTimeout> | null = null;

const fetchSchedules = async () => {
  pending.value = true;
  errorMessage.value = '';

  try {
    schedules.value = await useApi<PaginatedResponse<ScheduleItem>>('/schedules', {
      method: 'GET',
      query: {
        page: viewMode.value === 'preview' ? 1 : page.value,
        per_page: viewMode.value === 'preview' ? 500 : 10,
        search: search.value || undefined,
        tahun_ajaran_id: academicYearId.value || undefined,
        kelas_id: classroomId.value || undefined,
        teacher_id: teacherId.value || undefined,
        hari: day.value || undefined,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
      },
    });
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data jadwal.';
  } finally {
    pending.value = false;
  }
};

const fetchOptions = async () => {
  try {
    const [classroomsResponse, academicYearsResponse, teachersResponse] = await Promise.all([
      useApi<{ data: ClassroomOption[] }>('/classes', { method: 'GET', query: { simple: true } }),
      useApi<{ data: AcademicYearOption[] }>('/academic-years', { method: 'GET', query: { simple: true } }),
      auth.user?.role === 'guru'
        ? Promise.resolve({ data: [] as TeacherOption[] })
        : useApi<{ data: TeacherOption[] }>('/teachers', { method: 'GET', query: { simple: true } }),
    ]);
    classrooms.value = classroomsResponse.data;
    academicYears.value = academicYearsResponse.data;
    teachers.value = teachersResponse.data;
  } catch {}
};

const closeFilterModal = () => {
  isFilterModalOpen.value = false;
  draftAcademicYearId.value = academicYearId.value;
  draftClassroomId.value = classroomId.value;
  draftTeacherId.value = teacherId.value;
  draftDay.value = day.value;
};

const applyFilters = async () => {
  academicYearId.value = draftAcademicYearId.value;
  classroomId.value = draftClassroomId.value;
  teacherId.value = auth.user?.role === 'guru' ? '' : draftTeacherId.value;
  day.value = draftDay.value;
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchSchedules();
};

const resetFilters = async () => {
  draftAcademicYearId.value = '';
  draftClassroomId.value = '';
  draftTeacherId.value = '';
  draftDay.value = '';
  academicYearId.value = '';
  classroomId.value = '';
  teacherId.value = '';
  day.value = '';
  page.value = 1;
  isFilterModalOpen.value = false;
  await fetchSchedules();
};

const toggleSort = async (column: 'hari' | 'jam_mulai' | 'kelas' | 'teacher' | 'subject') => {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDirection.value = 'asc';
  }
  page.value = 1;
  await fetchSchedules();
};

const getSortIcon = (column: 'hari' | 'jam_mulai' | 'kelas' | 'teacher' | 'subject') => {
  if (sortBy.value !== column) {
    return '↕';
  }
  return sortDirection.value === 'asc' ? '↑' : '↓';
};

const deleteSchedule = async (schedule: ScheduleItem) => {
  if (!window.confirm(`Hapus jadwal ${schedule.subject?.nama_mapel || 'mapel'} untuk kelas ${schedule.kelas?.nama_kelas || '-'}?`)) {
    return;
  }

  actionLoadingId.value = schedule.id;

  try {
    await useApi(`/schedules/${schedule.id}`, { method: 'DELETE' });

    toast.success('Jadwal pelajaran berhasil dihapus.');

    if (schedules.value.data.length === 1 && page.value > 1) {
      page.value -= 1;
    } else {
      await fetchSchedules();
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal menghapus jadwal pelajaran.';
    toast.error(errorMessage.value);
  } finally {
    actionLoadingId.value = null;
  }
};

watch(page, async () => {
  if (viewMode.value === 'data') {
    await fetchSchedules();
  }
}, { immediate: true });

watch([search, viewMode], () => {
  page.value = 1;
  if (debounceHandle) {
    clearTimeout(debounceHandle);
  }
  debounceHandle = setTimeout(fetchSchedules, 400);
});

onMounted(async () => {
  await fetchOptions();
  draftAcademicYearId.value = academicYearId.value;
  draftClassroomId.value = classroomId.value;
  draftTeacherId.value = teacherId.value;
  draftDay.value = day.value;
});

const previewSchedules = computed(() => schedules.value.data);

const weeklyTimeSlots = computed(() => {
  const slots = Array.from(new Set(previewSchedules.value.map((item) => `${formatTime(item.jam_mulai)} - ${formatTime(item.jam_selesai)}`)));
  return slots.sort((left, right) => left.localeCompare(right));
});

const weeklyMatrix = computed(() => {
  const matrix = days.reduce((carry, dayName) => {
    carry[dayName] = {};
    return carry;
  }, {} as Record<string, Record<string, ScheduleItem[]>>);

  for (const item of previewSchedules.value) {
    const slot = `${formatTime(item.jam_mulai)} - ${formatTime(item.jam_selesai)}`;
    if (!matrix[item.hari][slot]) {
      matrix[item.hari][slot] = [];
    }
    matrix[item.hari][slot].push(item);
  }

  return matrix;
});

const monthlyGroups = computed(() => {
  return days.reduce((carry, dayName) => {
    carry[dayName] = previewSchedules.value
      .filter((item) => item.hari === dayName)
      .sort((left, right) => left.jam_mulai.localeCompare(right.jam_mulai));
    return carry;
  }, {} as Record<string, ScheduleItem[]>);
});

const activeFilterLabel = computed(() => {
  const labels: string[] = [];

  if (academicYearId.value) {
    const item = academicYears.value.find((it) => String(it.id) === academicYearId.value);
    if (item) labels.push(`Tahun Ajaran: ${item.nama_tahun_ajaran} ${item.semester}`);
  }

  if (classroomId.value) {
    const item = classrooms.value.find((it) => String(it.id) === classroomId.value);
    if (item) labels.push(`Kelas: ${item.nama_kelas}`);
  }

  if (auth.user?.role !== 'guru' && teacherId.value) {
    const item = teachers.value.find((it) => String(it.id) === teacherId.value);
    if (item) labels.push(`Guru: ${item.nama}`);
  }

  if (day.value) labels.push(`Hari: ${day.value}`);
  if (viewMode.value === 'preview') labels.push(`Mode: ${previewModes.find((item) => item.value === previewMode.value)?.label}`);

  return labels.join(' | ');
});

const formatTime = (value: string) => value?.slice(0, 5) || value;
const formatAcademicYear = (schedule: ScheduleItem) => schedule.tahun_ajaran ? `${schedule.tahun_ajaran.nama_tahun_ajaran} - ${schedule.tahun_ajaran.semester}` : '-';
</script>

