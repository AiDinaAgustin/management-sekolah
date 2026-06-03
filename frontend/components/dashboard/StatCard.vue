<template>
  <div class="rounded-[1.75rem] border border-white/70 bg-white/85 p-5 shadow-[0_12px_30px_rgba(99,102,241,0.12)] backdrop-blur">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-sm font-medium text-slate-500">{{ title }}</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ value }}</p>
        <p v-if="description" class="mt-2 text-xs text-slate-400">{{ description }}</p>
      </div>
      <div class="rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-500 p-3 text-white shadow-lg shadow-indigo-200">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 2 4 5v6c0 5.25 3.4 10.74 8 12 4.6-1.26 8-6.75 8-12V5l-8-3Zm1 14h-2v-2h2v2Zm0-4h-2V7h2v5Z" />
        </svg>
      </div>
    </div>
    <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100">
      <div class="h-full w-3/4 rounded-full bg-gradient-to-r from-indigo-500 to-blue-400"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  value: string | number;
  description?: string;
}>();
</script>
