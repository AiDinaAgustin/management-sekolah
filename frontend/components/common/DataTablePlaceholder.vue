<template>
  <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_45px_rgba(15,23,42,0.08)] backdrop-blur">
    <div class="flex flex-col gap-4 border-b border-slate-200/80 p-5 md:flex-row md:items-start md:justify-between">
      <div>
        <h3 class="text-xl font-semibold text-slate-900">{{ title }}</h3>
        <p class="text-sm text-slate-500">Tampilan tabel modern siap dihubungkan ke REST API.</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button class="rounded-xl bg-gradient-to-r from-indigo-600 to-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-200">
          Tambah
        </button>
        <button class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600">
          Export
        </button>
      </div>
    </div>
    <div class="border-b border-slate-100 px-5 py-4">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4 text-sm text-slate-500">
          <button class="rounded-xl bg-indigo-50 px-4 py-2 font-semibold text-indigo-600">{{ title }}</button>
          <button class="rounded-xl px-4 py-2 font-medium text-slate-400">Semua data</button>
        </div>
        <div class="flex gap-3">
          <input
            type="text"
            placeholder="Cari data..."
            class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm outline-none ring-0"
          />
          <button class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600">
            Filter
          </button>
        </div>
      </div>
    </div>
    <div class="p-5">
      <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50/70 p-8 text-center text-sm text-slate-500">
        Tabel data {{ title.toLowerCase() }} siap dihubungkan ke REST API.
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ title: string }>();
</script>
