<template>
  <div class="h-full overflow-hidden">
    <div class="flex h-full flex-col overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="flex flex-col gap-5 border-b border-slate-200/80 p-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div class="flex flex-1 flex-col gap-3 lg:flex-row lg:items-end">
            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Tanggal</span>
              <input v-model="selectedDate" type="date" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 lg:w-56" />
            </label>

            <label v-if="auth.user?.role === 'admin'" class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Guru</span>
              <select v-model="selectedTeacherId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 lg:w-72">
                <option value="">Pilih guru</option>
                <option v-for="teacher in teachers" :key="teacher.id" :value="String(teacher.id)">
                  {{ teacher.nama }} - {{ teacher.nip }}
                </option>
              </select>
            </label>
          </div>

          <div class="flex flex-wrap gap-3">
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50" @click="fetchScheduleCards">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              Muat Sesi
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-600 transition hover:border-emerald-400 hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-60" :disabled="!attendanceRows.length" @click="setAllHadir">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M9.55 18.2 3.9 12.55l1.4-1.4 4.25 4.25 9.15-9.15 1.4 1.4Z" />
              </svg>
              Set Semua Hadir
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70" :disabled="isSaving || !selectedMeeting || !attendanceRows.length" @click="saveAttendances">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M17 3H5a2 2 0 0 0-2 2v14l4-3h10a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Z" />
              </svg>
              Simpan Pertemuan
            </button>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="border-b border-rose-100 bg-rose-50 px-5 py-4 text-sm text-rose-600">{{ errorMessage }}</div>
      <div v-else-if="successMessage" class="border-b border-emerald-100 bg-emerald-50 px-5 py-4 text-sm text-emerald-600">{{ successMessage }}</div>

      <div class="grid min-h-0 flex-1 gap-6 overflow-hidden p-5 xl:grid-cols-[420px,minmax(0,1fr)]">
        <div class="min-h-0 space-y-4">
          <div class="flex h-full min-h-0 flex-col rounded-[1.75rem] border border-slate-300 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-slate-900">Sesi Pelajaran</h3>
              <span class="text-xs font-medium text-slate-400">{{ scheduleCards.length }} sesi</span>
            </div>

            <div class="mt-4 flex-1 space-y-3 overflow-y-auto pr-1">
              <div v-if="isLoadingSchedules" class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-400">
                Memuat sesi pelajaran...
              </div>
              <div v-else-if="!scheduleCards.length" class="rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-400">
                Belum ada jadwal untuk tanggal ini.
              </div>

              <button
                v-for="card in scheduleCards"
                :key="card.schedule_id"
                type="button"
                class="w-full rounded-[1.5rem] border px-4 py-4 text-left transition"
                :class="selectedScheduleId === card.schedule_id ? 'border-indigo-300 bg-indigo-50 shadow-sm' : 'border-slate-200 bg-white hover:border-indigo-200 hover:bg-slate-50'"
                @click="openMeeting(card)"
              >
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-slate-900">{{ card.subject?.nama_mapel || '-' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ card.kelas?.nama_kelas || '-' }} · {{ formatTime(card.jam_mulai) }} - {{ formatTime(card.jam_selesai) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ card.teacher?.nama || '-' }}<span v-if="card.ruangan"> · {{ card.ruangan }}</span></p>
                  </div>
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="card.meeting ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'">
                    {{ card.meeting ? `Pertemuan ${card.meeting.pertemuan_ke}` : 'Belum dibuat' }}
                  </span>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div class="flex min-h-0 flex-col space-y-4 overflow-hidden">
          <div v-if="selectedMeeting" class="rounded-[1.75rem] border border-slate-300 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ selectedMeeting.schedule.subject?.nama_mapel || '-' }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ selectedMeeting.schedule.kelas?.nama_kelas || '-' }} · {{ selectedMeeting.schedule.teacher?.nama || '-' }}</p>
                <p class="mt-1 text-xs text-slate-400">Pertemuan ke-{{ selectedMeeting.pertemuan_ke }} · {{ formatTime(selectedMeeting.schedule.jam_mulai) }} - {{ formatTime(selectedMeeting.schedule.jam_selesai) }}</p>
              </div>
              <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">{{ selectedMeeting.status }}</span>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
              <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-600">Topik Materi</span>
                <input v-model="meetingTopik" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Contoh: Pecahan dan desimal" />
              </label>
              <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-600">Status Pertemuan</span>
                <select v-model="meetingStatus" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
                  <option value="draft">Draft</option>
                  <option value="selesai">Selesai</option>
                </select>
              </label>
            </div>

            <label class="mt-4 block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Catatan Guru</span>
              <textarea v-model="meetingCatatan" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Catatan tambahan untuk pertemuan ini"></textarea>
            </label>
          </div>

          <div v-if="selectedMeeting" class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-[1.75rem] border border-slate-300 bg-white shadow-sm">
            <div class="border-b border-slate-300 bg-slate-50 px-5 py-3 text-sm text-slate-500">
              Isi absensi per pertemuan untuk sesi pelajaran yang dipilih.
            </div>
            <div v-if="isLoadingSheet" class="p-6 text-sm text-slate-500">Memuat data siswa...</div>
            <div v-else class="min-h-0 flex-1 overflow-y-auto overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="border-b border-slate-300 text-left text-sm font-semibold uppercase tracking-[0.18em] text-slate-400">
                  <tr>
                    <th class="px-4 py-4 font-medium">No</th>
                    <th class="px-4 py-4 font-medium">NIS</th>
                    <th class="px-4 py-4 font-medium">Nama Siswa</th>
                    <th class="px-4 py-4 font-medium">JK</th>
                    <th class="px-4 py-4 font-medium">Status</th>
                    <th class="min-w-[280px] px-4 py-4 font-medium">Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in attendanceRows" :key="row.siswa_id" class="border-b border-slate-300/70 text-slate-700 last:border-b-0">
                    <td class="px-4 py-4 text-center font-semibold text-slate-500">{{ index + 1 }}</td>
                    <td class="px-4 py-4 font-medium text-slate-500">{{ row.nis }}</td>
                    <td class="px-4 py-4 font-semibold text-slate-900">{{ row.nama_lengkap }}</td>
                    <td class="px-4 py-4 font-medium text-slate-500">{{ row.jenis_kelamin }}</td>
                    <td class="px-4 py-4">
                      <div class="flex flex-wrap gap-2">
                        <button v-for="status in attendanceStatuses" :key="status.value" type="button" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition" :class="row.status === status.value ? status.activeClass : 'border-slate-300 bg-white text-slate-400 hover:border-indigo-300 hover:text-indigo-600'" @click="row.status = status.value">
                          {{ status.label }}
                        </button>
                      </div>
                    </td>
                    <td class="px-4 py-4">
                      <input v-model="row.keterangan" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300" placeholder="Opsional" />
                    </td>
                  </tr>
                  <tr v-if="!attendanceRows.length">
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data siswa untuk sesi ini.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white px-6 py-16 text-center text-sm text-slate-400">
            Pilih sesi pelajaran terlebih dahulu untuk mulai input absensi per pertemuan.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { LessonAttendanceRow, LessonMeetingDetail, LessonScheduleCard, TeacherOption } from '~/types/lesson-attendance';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Akademik',
  pageTitle: 'Absensi Pembelajaran',
  pageDescription: 'Input absensi berdasarkan sesi jadwal mata pelajaran dan pertemuan belajar.',
});

type AttendanceRowForm = {
  siswa_id: number;
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  status: 'hadir' | 'izin' | 'sakit' | 'alfa' | null;
  keterangan: string;
};

const auth = useAuthStore();
const toast = useToast();
const selectedDate = ref(new Date().toISOString().slice(0, 10));
const selectedTeacherId = ref('');
const teachers = ref<TeacherOption[]>([]);
const scheduleCards = ref<LessonScheduleCard[]>([]);
const selectedScheduleId = ref<number | null>(null);
const selectedMeeting = ref<LessonMeetingDetail | null>(null);
const attendanceRows = ref<AttendanceRowForm[]>([]);
const meetingTopik = ref('');
const meetingCatatan = ref('');
const meetingStatus = ref<'draft' | 'selesai'>('draft');
const isLoadingSchedules = ref(false);
const isLoadingSheet = ref(false);
const isSaving = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const attendanceStatuses = [
  { value: 'hadir', label: 'Hadir', activeClass: 'border-emerald-200 bg-emerald-50 text-emerald-600' },
  { value: 'izin', label: 'Izin', activeClass: 'border-amber-200 bg-amber-50 text-amber-600' },
  { value: 'sakit', label: 'Sakit', activeClass: 'border-sky-200 bg-sky-50 text-sky-600' },
  { value: 'alfa', label: 'Alfa', activeClass: 'border-rose-200 bg-rose-50 text-rose-600' },
] as const;

const fetchTeachers = async () => {
  if (auth.user?.role !== 'admin') {
    teachers.value = [];
    return;
  }

  try {
    const response = await useApi<{ data: TeacherOption[] }>('/teachers', { method: 'GET', query: { simple: true } });
    teachers.value = response.data;
  } catch {}
};

const fetchScheduleCards = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (auth.user?.role === 'admin' && !selectedTeacherId.value) {
    errorMessage.value = 'Pilih guru terlebih dahulu.';
    return;
  }

  isLoadingSchedules.value = true;

  try {
    const response = await useApi<{ data: LessonScheduleCard[] }>('/lesson-attendances/schedules', {
      method: 'GET',
      query: {
        tanggal: selectedDate.value,
        teacher_id: auth.user?.role === 'admin' ? selectedTeacherId.value : undefined,
      },
    });

    scheduleCards.value = response.data;
    selectedScheduleId.value = null;
    selectedMeeting.value = null;
    attendanceRows.value = [];
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat sesi pelajaran.';
  } finally {
    isLoadingSchedules.value = false;
  }
};

const openMeeting = async (card: LessonScheduleCard) => {
  errorMessage.value = '';
  successMessage.value = '';
  selectedScheduleId.value = card.schedule_id;
  isLoadingSheet.value = true;

  try {
    const opened = await useApi<{ data: LessonMeetingDetail }>('/lesson-attendances/open', {
      method: 'POST',
      body: {
        schedule_id: card.schedule_id,
        tanggal: selectedDate.value,
      },
    });

    selectedMeeting.value = opened.data;
    meetingTopik.value = opened.data.topik || '';
    meetingCatatan.value = opened.data.catatan || '';
    meetingStatus.value = (opened.data.status as 'draft' | 'selesai') || 'draft';

    const sheet = await useApi<{ data: { meeting: LessonMeetingDetail; students: LessonAttendanceRow[] } }>(`/lesson-attendances/meetings/${opened.data.id}/sheet`, {
      method: 'GET',
    });

    attendanceRows.value = sheet.data.students.map((student) => ({
      siswa_id: student.id,
      nis: student.nis,
      nama_lengkap: student.nama_lengkap,
      jenis_kelamin: student.jenis_kelamin,
      status: student.attendance?.status || null,
      keterangan: student.attendance?.keterangan || '',
    }));
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal membuka sesi absensi pembelajaran.';
  } finally {
    isLoadingSheet.value = false;
  }
};

const setAllHadir = () => {
  attendanceRows.value = attendanceRows.value.map((row) => ({
    ...row,
    status: 'hadir',
  }));
  errorMessage.value = '';
};

const saveAttendances = async () => {
  errorMessage.value = '';
  successMessage.value = '';

  if (!selectedMeeting.value) {
    errorMessage.value = 'Pilih sesi pelajaran terlebih dahulu.';
    return;
  }

  if (attendanceRows.value.some((row) => !row.status)) {
    errorMessage.value = 'Semua siswa harus dipilih status absensinya terlebih dahulu.';
    return;
  }

  isSaving.value = true;

  try {
    await useApi(`/lesson-attendances/meetings/${selectedMeeting.value.id}/save`, {
      method: 'POST',
      body: {
        topik: meetingTopik.value || null,
        catatan: meetingCatatan.value || null,
        status: meetingStatus.value,
        attendances: attendanceRows.value.map((row) => ({
          siswa_id: row.siswa_id,
          status: row.status,
          keterangan: row.keterangan || null,
        })),
      },
    });

    successMessage.value = 'Absensi pembelajaran berhasil disimpan.';
    toast.success(successMessage.value);
    await fetchScheduleCards();
  } catch (error: any) {
    const validationErrors = error?.data?.errors;
    errorMessage.value = validationErrors && typeof validationErrors === 'object'
      ? Object.values(validationErrors).flat().join(' ')
      : error?.data?.message || 'Gagal menyimpan absensi pembelajaran.';
    toast.error(errorMessage.value);
  } finally {
    isSaving.value = false;
  }
};

const formatTime = (value: string) => value?.slice(0, 5) || value;

watch(selectedDate, async () => {
  if (auth.user?.role === 'guru') {
    await fetchScheduleCards();
  }
});

watch(selectedTeacherId, async () => {
  if (auth.user?.role === 'admin' && selectedTeacherId.value) {
    await fetchScheduleCards();
  }
});

onMounted(async () => {
  await fetchTeachers();

  if (auth.user?.role === 'guru') {
    await fetchScheduleCards();
  }
});
</script>
