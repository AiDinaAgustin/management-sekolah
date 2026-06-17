<template>
  <div class="rapot-doc overflow-hidden rounded-[2rem] border border-white/70 bg-white p-6 shadow-[0_20px_45px_rgba(15,23,42,0.08)] sm:p-10">
    <!-- Kop -->
    <div class="border-b-2 border-slate-800 pb-5 text-center">
      <h1 class="text-2xl font-bold uppercase text-slate-900">{{ report.school.nama_sekolah }}</h1>
      <p v-if="report.school.tagline" class="mt-1 text-sm text-slate-500">{{ report.school.tagline }}</p>
      <p class="mt-3 text-base font-semibold uppercase tracking-wide text-slate-700">Laporan Hasil Belajar Siswa</p>
    </div>

    <!-- Identitas -->
    <div class="mt-6 grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2">
      <div class="flex">
        <span class="w-36 text-slate-500">Nama Siswa</span>
        <span class="font-semibold text-slate-900">: {{ report.student.nama_lengkap }}</span>
      </div>
      <div class="flex">
        <span class="w-36 text-slate-500">Kelas</span>
        <span class="font-semibold text-slate-900">: {{ report.student.kelas?.nama_kelas || '-' }}</span>
      </div>
      <div class="flex">
        <span class="w-36 text-slate-500">NIS</span>
        <span class="font-semibold text-slate-900">: {{ report.student.nis }}</span>
      </div>
      <div class="flex">
        <span class="w-36 text-slate-500">Tahun Ajaran</span>
        <span class="font-semibold text-slate-900">: {{ report.student.kelas?.tahun_ajaran || '-' }}</span>
      </div>
      <div class="flex">
        <span class="w-36 text-slate-500">Jenis Kelamin</span>
        <span class="font-semibold text-slate-900">: {{ report.student.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
      </div>
      <div class="flex">
        <span class="w-36 text-slate-500">Semester</span>
        <span class="font-semibold text-slate-900">: {{ report.semester || '-' }}</span>
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
          <tr v-for="(grade, index) in report.grades" :key="grade.id" class="text-slate-700">
            <td class="border border-slate-300 px-3 py-2 text-center">{{ index + 1 }}</td>
            <td class="border border-slate-300 px-3 py-2 font-medium text-slate-900">{{ grade.nama_mapel }}</td>
            <td class="border border-slate-300 px-3 py-2 text-center">{{ grade.tugas }}</td>
            <td class="border border-slate-300 px-3 py-2 text-center">{{ grade.uts }}</td>
            <td class="border border-slate-300 px-3 py-2 text-center">{{ grade.uas }}</td>
            <td class="border border-slate-300 px-3 py-2 text-center font-semibold text-indigo-600">{{ grade.nilai_akhir }}</td>
            <td class="border border-slate-300 px-3 py-2 text-center font-semibold">{{ grade.predikat }}</td>
          </tr>
          <tr v-if="!report.grades.length">
            <td colspan="7" class="border border-slate-300 px-3 py-6 text-center text-slate-400">
              Belum ada nilai yang tercatat untuk semester ini.
            </td>
          </tr>
        </tbody>
        <tfoot v-if="report.grades.length">
          <tr class="bg-slate-50 font-semibold text-slate-800">
            <td colspan="5" class="border border-slate-300 px-3 py-2 text-right">Rata-rata</td>
            <td class="border border-slate-300 px-3 py-2 text-center text-indigo-600">{{ report.average }}</td>
            <td class="border border-slate-300 px-3 py-2 text-center">{{ report.average_predikat }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Ringkasan: rekap absensi + peringkat -->
    <div class="mt-6 grid gap-6 sm:grid-cols-2">
      <div class="rounded-2xl border border-slate-300 p-4">
        <h3 class="text-sm font-semibold text-slate-700">Rekap Kehadiran</h3>
        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
          <div class="flex justify-between"><span class="text-slate-500">Hadir</span><span class="font-semibold text-emerald-600">{{ report.attendance_recap.hadir }}</span></div>
          <div class="flex justify-between"><span class="text-slate-500">Izin</span><span class="font-semibold text-amber-500">{{ report.attendance_recap.izin }}</span></div>
          <div class="flex justify-between"><span class="text-slate-500">Sakit</span><span class="font-semibold text-sky-500">{{ report.attendance_recap.sakit }}</span></div>
          <div class="flex justify-between"><span class="text-slate-500">Alfa</span><span class="font-semibold text-rose-500">{{ report.attendance_recap.alfa }}</span></div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-300 p-4">
        <h3 class="text-sm font-semibold text-slate-700">Capaian</h3>
        <div class="mt-3 space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-slate-500">Rata-rata Nilai</span>
            <span class="font-semibold text-slate-900">{{ report.average ?? '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Peringkat Kelas</span>
            <span class="font-semibold text-slate-900">
              {{ report.ranking.position ? `${report.ranking.position} dari ${report.ranking.total}` : '-' }}
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
          {{ report.student.kelas?.wali_kelas || '..................' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReportCard } from '~/types/report-card';

defineProps<{ report: ReportCard }>();
</script>
