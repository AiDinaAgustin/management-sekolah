<template>
  <div class="space-y-6">
    <!-- Panel filter (tidak ikut tercetak) -->
    <div class="rapot-no-print overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="border-b border-slate-200/80 p-5">
        <!-- Toggle mode -->
        <div class="mb-4 flex flex-wrap gap-2">
          <button
            type="button"
            class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition"
            :class="mode === 'siswa' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'border border-slate-300 bg-white text-slate-600 hover:border-indigo-300 hover:bg-indigo-50'"
            @click="mode = 'siswa'"
          >
            Rapot Per Siswa
          </button>
          <button
            type="button"
            class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition"
            :class="mode === 'kelas' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'border border-slate-300 bg-white text-slate-600 hover:border-indigo-300 hover:bg-indigo-50'"
            @click="mode = 'kelas'"
          >
            Rapot Per Kelas
          </button>
        </div>

        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
          <div class="grid flex-1 gap-3 sm:grid-cols-2 xl:max-w-3xl xl:grid-cols-3">
            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Kelas</span>
              <select v-model="selectedClassroomId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
                <option value="">Pilih kelas</option>
                <option v-for="classroom in classrooms" :key="classroom.id" :value="String(classroom.id)">
                  {{ classroom.nama_kelas }}
                </option>
              </select>
            </label>

            <label v-if="mode === 'siswa'" class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Siswa</span>
              <select v-model="selectedStudentId" :disabled="!students.length" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300 disabled:cursor-not-allowed disabled:bg-slate-50">
                <option value="">{{ students.length ? 'Pilih siswa' : 'Pilih kelas dulu' }}</option>
                <option v-for="student in students" :key="student.id" :value="String(student.id)">
                  {{ student.nama_lengkap }} · {{ student.nis }}
                </option>
              </select>
            </label>

            <label class="block">
              <span class="mb-2 block text-sm font-medium text-slate-600">Semester</span>
              <select v-model="selectedSemester" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
                <option v-for="semester in semesters" :key="semester" :value="semester">{{ semester }}</option>
              </select>
            </label>
          </div>

          <div class="flex shrink-0 gap-3">
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70"
              :disabled="reloadDisabled"
              @click="reload"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              {{ isLoading ? 'Memuat...' : 'Tampilkan Rapot' }}
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="!hasResult"
              @click="printReportCard"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M6 9V2h12v7h2a2 2 0 0 1 2 2v6h-4v4H6v-4H2v-6a2 2 0 0 1 2-2h2Zm2-5v5h8V4H8Zm0 12v4h8v-4H8Zm-2-2h2v-2h8v2h2v-3H6v3Z" />
              </svg>
              Unduh PDF
            </button>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="px-5 py-4 text-sm text-rose-500">{{ errorMessage }}</div>
    </div>

    <!-- Empty state -->
    <div v-if="!hasResult && !isLoading" class="rapot-no-print rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-16 text-center text-sm text-slate-400">
      <template v-if="mode === 'siswa'">
        Pilih kelas, siswa, dan semester untuk melihat laporan hasil belajar.
      </template>
      <template v-else>
        Pilih kelas dan semester untuk menampilkan rapot seluruh siswa di kelas tersebut.
      </template>
    </div>

    <!-- Info jumlah (mode kelas, tidak tercetak) -->
    <div v-if="mode === 'kelas' && classReports.length" class="rapot-no-print rounded-2xl border border-indigo-100 bg-indigo-50/70 px-5 py-3 text-sm font-medium text-indigo-600">
      Menampilkan {{ classReports.length }} rapot siswa. Klik <span class="font-semibold">Unduh PDF</span> untuk mencetak semuanya sekaligus (satu siswa per halaman).
    </div>

    <!-- Dokumen rapot (area cetak) -->
    <div class="rapot-print-area space-y-6">
      <template v-if="mode === 'siswa'">
        <ReportCardsReportCardDocument v-if="reportCard" :report="reportCard" class="rapot-page" />
      </template>
      <template v-else>
        <ReportCardsReportCardDocument v-for="report in classReports" :key="report.student.id" :report="report" class="rapot-page" />
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReportCard } from '~/types/report-card';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Akademik',
  pageTitle: 'Rapot',
  pageDescription: 'Lihat dan cetak laporan hasil belajar siswa per semester.',
});

type ClassroomOption = { id: number; nama_kelas: string };
type StudentOption = { id: number; nis: string; nama_lengkap: string };
type PaginatedResponse<T> = { data: T[] };

const toast = useToast();

const semesters = ['Ganjil', 'Genap'];
const mode = ref<'siswa' | 'kelas'>('siswa');
const classrooms = ref<ClassroomOption[]>([]);
const students = ref<StudentOption[]>([]);
const selectedClassroomId = ref('');
const selectedStudentId = ref('');
const selectedSemester = ref('Ganjil');
const reportCard = ref<ReportCard | null>(null);
const classReports = ref<ReportCard[]>([]);
const isLoading = ref(false);
const errorMessage = ref('');

const hasResult = computed(() => (mode.value === 'siswa' ? Boolean(reportCard.value) : classReports.value.length > 0));

const reloadDisabled = computed(() => {
  if (isLoading.value) {
    return true;
  }

  return mode.value === 'siswa' ? !selectedStudentId.value : !selectedClassroomId.value;
});

const fetchClassrooms = async () => {
  try {
    const response = await useApi<{ data: ClassroomOption[] }>('/classes', {
      method: 'GET',
      query: { simple: true },
    });
    classrooms.value = response.data;
  } catch {
  }
};

const fetchStudents = async () => {
  if (!selectedClassroomId.value) {
    students.value = [];
    return;
  }

  try {
    const response = await useApi<PaginatedResponse<StudentOption>>('/students', {
      method: 'GET',
      query: {
        kelas_id: selectedClassroomId.value,
        status_aktif: true,
        per_page: 200,
      },
    });
    students.value = response.data;
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data siswa.';
  }
};

const fetchReportCard = async () => {
  if (!selectedStudentId.value) {
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await useApi<{ data: ReportCard }>(`/report-cards/${selectedStudentId.value}`, {
      method: 'GET',
      query: { semester: selectedSemester.value },
    });
    reportCard.value = response.data;
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat data rapot.';
    toast.error(errorMessage.value);
    reportCard.value = null;
  } finally {
    isLoading.value = false;
  }
};

const fetchClassReports = async () => {
  if (!selectedClassroomId.value) {
    classReports.value = [];
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await useApi<{ data: { report_cards: ReportCard[] } }>(`/report-cards/class/${selectedClassroomId.value}`, {
      method: 'GET',
      query: { semester: selectedSemester.value },
    });
    classReports.value = response.data.report_cards;

    if (!classReports.value.length) {
      toast.info('Belum ada siswa aktif di kelas ini.');
    }
  } catch (error: any) {
    errorMessage.value = error?.data?.message || 'Gagal memuat rapot kelas.';
    toast.error(errorMessage.value);
    classReports.value = [];
  } finally {
    isLoading.value = false;
  }
};

const reload = async () => {
  if (mode.value === 'siswa') {
    await fetchReportCard();
  } else {
    await fetchClassReports();
  }
};

const printReportCard = () => {
  window.print();
};

watch(mode, () => {
  reportCard.value = null;
  classReports.value = [];
  errorMessage.value = '';

  if (mode.value === 'kelas' && selectedClassroomId.value) {
    fetchClassReports();
  }
});

watch(selectedClassroomId, async () => {
  selectedStudentId.value = '';
  reportCard.value = null;
  classReports.value = [];
  await fetchStudents();

  if (mode.value === 'kelas' && selectedClassroomId.value) {
    await fetchClassReports();
  }
});

// Mode siswa: muat otomatis begitu siswa & semester terpilih.
watch([selectedStudentId, selectedSemester], async () => {
  if (mode.value === 'siswa') {
    if (!selectedStudentId.value) {
      reportCard.value = null;
      return;
    }

    await fetchReportCard();
  }
});

// Mode kelas: muat ulang otomatis saat ganti semester.
watch(selectedSemester, async () => {
  if (mode.value === 'kelas' && selectedClassroomId.value) {
    await fetchClassReports();
  }
});

onMounted(fetchClassrooms);
</script>

<style>
@media print {
  /* Hilangkan pembatas tinggi & clipping dari layout supaya konten tidak terpotong satu layar. */
  * {
    overflow: visible !important;
    height: auto !important;
    max-height: none !important;
  }

  /* Tampilkan hanya area dokumen rapot. */
  body * {
    visibility: hidden;
  }

  .rapot-print-area,
  .rapot-print-area * {
    visibility: visible;
  }

  /* Tetap di alur normal (bukan absolute) agar bisa memanjang ke beberapa halaman. */
  .rapot-print-area {
    position: static !important;
  }

  .rapot-page {
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
  }

  /* Setiap rapot dimulai di halaman baru saat cetak per kelas. */
  .rapot-page {
    break-inside: avoid;
  }

  .rapot-page + .rapot-page {
    break-before: page;
    page-break-before: always;
  }

  .rapot-no-print {
    display: none !important;
  }
}

@page {
  margin: 1.5cm;
}
</style>
