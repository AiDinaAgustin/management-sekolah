<template>
  <div class="rounded-[1.5rem] border border-white/70 bg-white/85 p-5 shadow-[0_18px_40px_rgba(15,23,42,0.08)] backdrop-blur">
    <p class="text-sm font-medium text-slate-500">{{ title }}</p>
    <p class="mt-2 text-2xl font-bold text-slate-900">{{ value }}</p>
    <p class="mt-1 text-xs text-slate-400">{{ description }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
  value: string | number
  description: string
}>();
</script>
