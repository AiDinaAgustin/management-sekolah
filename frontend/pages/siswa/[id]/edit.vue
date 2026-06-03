<template>
  <div class="space-y-4">
    <NuxtLink to="/siswa" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="m14.7 6.3-1.4-1.4L6.2 12l7.1 7.1 1.4-1.4-4.7-4.7H20v-2H10l4.7-4.7Z" />
      </svg>
      Kembali
    </NuxtLink>

    <StudentFormPanel mode="edit" :student-id="studentId" />
  </div>
</template>

<script setup lang="ts">
import StudentFormPanel from '~/components/students/StudentFormPanel.vue';

definePageMeta({
  middleware: 'auth',
  pageEyebrow: 'Master Data',
  pageTitle: 'Edit Siswa',
  pageDescription: 'Perbarui data siswa yang sudah tersimpan.',
});

const route = useRoute();
const studentId = computed(() => Number(route.params.id));
</script>
