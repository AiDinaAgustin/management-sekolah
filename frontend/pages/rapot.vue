<template>
  <div class="space-y-6">
    <!-- Panel filter (tidak ikut tercetak) -->
    <div class="rapot-no-print overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
      <div class="border-b border-slate-200/80 p-5">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-600">Kelas</span>
            <select v-model="selectedClassroomId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-300">
              <option value="">Pilih kelas</option>
              <option v-for="classroom in classrooms" :key="classroom.id" :value="String(classroom.id)">
                {{ classroom.nama_kelas }}
              </option>
            </select>
          </label>

          <label class="block">
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

          <div class="flex items-end gap-3">
            <button
              type="button"
              class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 disabled:cursor-not-allowed disabled:opacity-70"
              :disabled="!selectedStudentId || isLoading"
              @click="fetchReportCard"
            >
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 6V3L8 7l4 4V8c2.76 0 5 2.24 5 5a5 5 0 0 1-8.66 3.54l-1.42 1.42A7 7 0 1 0 12 6Z" />
              </svg>
              {{ isLoading ? 'Memuat...' : 'Tampilkan Rapot' }}
            </button>
            <button
              type="button"
              class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="!reportCard"
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
    <div v-if="!reportCard && !isLoading" class="rapot-no-print rounded-[2rem] border border-dashed border-slate-300 bg-white/70 px-6 py-16 text-center text-sm text-slate-400">
      Pilih kelas, siswa, dan semester lalu klik <span class="font-semibold text-slate-500">Tampilkan Rapot</span> untuk melihat laporan hasil belajar.
    </div>

    <!-- Dokumen rapot (area cetak) -->
    <div v-if="reportCard" class="rapot-print-area overflow-hidden rounded-[2rem] border border-white/70 bg-white p-6 shadow-[0_20px_45px_rgba(15,23,42,0.08)] sm:p-10">
      <!-- Kop -->
      <div class="border-b-2 border-slate-800 pb-5 text-center">
        <h1 class="text-2xl font-bold uppercase text-slate-900">{{ reportCard.school.nama_sekolah }}</h1>
        <p v-if="reportCard.school.tagline" class="mt-1 text-sm text-slate-500">{{ reportCard.school.tagline }}</p>
        <p class="mt-3 text-base font-semibold uppercase tracking-wide text-slate-700">Laporan Hasil Belajar Siswa</p>
      </div>

      <!-- Identitas -->
      <div class="mt-6 grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
        <div class="flex">
          <span class="w-36 text-slate-500">Nama Siswa</span>
          <span class="font-semibold text-slate-900">: {{ reportCard.student.nama_lengkap }}</span>
        </div>
        <div class="flex">
          <span class="w-36 text-slate-500">Kelas</span>
          <span class="font-semibold text-slate-900">: {{ reportCard.student.kelas?.nama_kelas || '-' }}</span>
        </div>
        <div class="flex">
          <span class="w-36 text-slate-500">NIS</span>
          <span class="font-semibold text-slate-900">: {{ reportCard.student.nis }}</span>
        </div>
        <div class="flex">
          <span class="w-36 text-slate-500">Tahun Ajaran</span>
          <span class="font-semibold text-slate-900">: {{ reportCard.student.kelas?.tahun_ajaran || '-' }}</span>
        </div>
        <div class="flex">
          <span class="w-36 text-slate-500">Jenis Kelamin</span>
          <span class="font-semibold text-slate-900">: {{ reportCard.student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
        </div>
        <div class="flex">
          <span class="w-36 text-slate-500">Semester</span>
          <span class="font-semibold text-slate-900">: {{ reportCard.semester || '-' }}</span>
        </div>
      </div>

      <!-- Tabel nilai -->
      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
          <thead>
            <tr class="bg-slate-100 text-left text-slate-600">
              <th class="border border-slate-300 px-3 py-2 text-center font-semibold">No</th>
              <th class="border border-slate-300 px-3 py-2 font-semibold">Mata Pelajaran</th>
              <th class="border border-slate-300 px-3 py-2 text-center font-semibold">Tugas</th>
              <th class="border border-slate-300 px-3 py-2 text-center font-semibold">UTS</th>
              <th class="border border-slate-300 px-3 py-2 text-center font-semibold">UAS</th>
              <th class="border border-slate-300 px-3 py-2 text-center font-semibold">Nilai Akhir</th>
              <th class="border border-slate-300 px-3 py-2 text-center font-semibold">Predikat</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(grade, index) in reportCard.grades" :key="grade.id" class="text-slate-700">
              <td class="border border-slate-300 px-3 py-2 text-center">{{ index + 1 }}</td>
              <td class="border border-slate-300 px-3 py-2 font-medium text-slate-900">{{ grade.nama_mapel }}</td>
              <td class="border border-slate-300 px-3 py-2 text-center">{{ grade.tugas }}</td>
              <td class="border border-slate-300 px-3 py-2 text-center">{{ grade.uts }}</td>
              <td class="border border-slate-300 px-3 py-2 text-center">{{ grade.uas }}</td>
              <td class="border border-slate-300 px-3 py-2 text-center font-semibold text-indigo-600">{{ grade.nilai_akhir }}</td>
              <td class="border border-slate-300 px-3 py-2 text-center font-semibold">{{ grade.predikat }}</td>
            </tr>
            <tr v-if="!reportCard.grades.length">
              <td colspan="7" class="border border-slate-300 px-3 py-6 text-center text-slate-400">
                Belum ada nilai yang tercatat untuk semester ini.
              </td>
            </tr>
          </tbody>
          <tfoot v-if="reportCard.grades.length">
            <tr class="bg-slate-50 font-semibold text-slate-800">
              <td colspan="5" class="border border-slate-300 px-3 py-2 text-right">Rata-rata</td>
              <td class="border border-slate-300 px-3 py-2 text-center text-indigo-600">{{ reportCard.average }}</td>
              <td class="border border-slate-300 px-3 py-2 text-center">{{ reportCard.average_predikat }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Ringkasan: rekap absensi + peringkat -->
      <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-300 p-4">
          <h3 class="text-sm font-semibold text-slate-700">Rekap Kehadiran</h3>
          <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Hadir</span><span class="font-semibold text-emerald-600">{{ reportCard.attendance_recap.hadir }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Izin</span><span class="font-semibold text-amber-500">{{ reportCard.attendance_recap.izin }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Sakit</span><span class="font-semibold text-sky-500">{{ reportCard.attendance_recap.sakit }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Alfa</span><span class="font-semibold text-rose-500">{{ reportCard.attendance_recap.alfa }}</span></div>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-300 p-4">
          <h3 class="text-sm font-semibold text-slate-700">Capaian</h3>
          <div class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-slate-500">Rata-rata Nilai</span>
              <span class="font-semibold text-slate-900">{{ reportCard.average ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Peringkat Kelas</span>
              <span class="font-semibold text-slate-900">
                {{ reportCard.ranking.position ? `${reportCard.ranking.position} dari ${reportCard.ranking.total}` : '-' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tanda tangan -->
      <div class="mt-10 flex justify-between gap-6 text-center text-sm text-slate-700">
        <div>
          <p>Orang Tua / Wali</p>
          <div class="mt-16 border-t border-slate-400 px-6 pt-1">&nbsp;</div>
        </div>
        <div>
          <p>Wali Kelas</p>
          <div class="mt-16 border-t border-slate-400 px-6 pt-1 font-semibold">
            {{ reportCard.student.kelas?.wali_kelas || '..................' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Akademik',
  pageTitle: 'Rapot',
  pageDescription: 'Lihat dan cetak laporan hasil belajar siswa per semester.',
});

type ClassroomOption = { id: number; nama_kelas: string };
type StudentOption = { id: number; nis: string; nama_lengkap: string };

type ReportCardGrade = {
  id: number;
  mapel_id: number;
  kode_mapel: string | null;
  nama_mapel: string;
  tugas: number;
  uts: number;
  uas: number;
  nilai_akhir: number;
  predikat: string;
};

type ReportCard = {
  student: {
    id: number;
    nis: string;
    nama_lengkap: string;
    jenis_kelamin: 'L' | 'P';
    kelas: { id: number; nama_kelas: string; wali_kelas: string | null; tahun_ajaran: string | null } | null;
  };
  semester: string | null;
  available_semesters: string[];
  grades: ReportCardGrade[];
  average: number | null;
  average_predikat: string | null;
  ranking: { position: number | null; total: number };
  attendance_recap: { hadir: number; izin: number; sakit: number; alfa: number; total: number };
  school: { nama_sekolah: string; tagline: string | null };
};

type PaginatedResponse<T> = { data: T[] };

const toast = useToast();

const semesters = ['Ganjil', 'Genap'];
const classrooms = ref<ClassroomOption[]>([]);
const students = ref<StudentOption[]>([]);
const selectedClassroomId = ref('');
const selectedStudentId = ref('');
const selectedSemester = ref('Ganjil');
const reportCard = ref<ReportCard | null>(null);
const isLoading = ref(false);
const errorMessage = ref('');

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

const printReportCard = () => {
  window.print();
};

watch(selectedClassroomId, async () => {
  selectedStudentId.value = '';
  reportCard.value = null;
  await fetchStudents();
});

watch(selectedStudentId, () => {
  reportCard.value = null;
});

watch(selectedSemester, () => {
  reportCard.value = null;
});

onMounted(fetchClassrooms);
</script>

<style>
@media print {
  body * {
    visibility: hidden;
  }

  .rapot-print-area,
  .rapot-print-area * {
    visibility: visible;
  }

  .rapot-print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
  }

  .rapot-no-print {
    display: none !important;
  }
}
</style>
