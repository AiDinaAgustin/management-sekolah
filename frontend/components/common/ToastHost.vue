<template>
  <Teleport to="body">
    <div class="pointer-events-none fixed inset-x-0 top-4 z-[200] flex flex-col items-center gap-3 px-4 sm:items-end sm:px-6">
      <TransitionGroup
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-[-12px] opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-x-4 opacity-0"
        move-class="transition duration-200"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border px-4 py-3 shadow-[0_18px_40px_rgba(15,23,42,0.18)] backdrop-blur"
          :class="styleFor(toast.type)"
        >
          <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full" :class="iconWrapFor(toast.type)">
            <svg v-if="toast.type === 'success'" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M9.55 18.2 3.9 12.55l1.4-1.4 4.25 4.25 9.15-9.15 1.4 1.4Z" />
            </svg>
            <svg v-else-if="toast.type === 'error'" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M11 7h2v7h-2V7Zm0 9h2v2h-2v-2Z" />
              <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 2a8 8 0 1 1 0 16 8 8 0 0 1 0-16Z" clip-rule="evenodd" />
            </svg>
            <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M11 10h2v7h-2v-7Zm0-3h2v2h-2V7Z" />
              <path fill-rule="evenodd" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 2a8 8 0 1 1 0 16 8 8 0 0 1 0-16Z" clip-rule="evenodd" />
            </svg>
          </span>
          <p class="flex-1 text-sm font-medium leading-snug">{{ toast.message }}</p>
          <button type="button" class="shrink-0 rounded-lg p-1 text-current/70 transition hover:text-current" aria-label="Tutup notifikasi" @click="remove(toast.id)">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="m18.3 5.71-1.41-1.42L12 9.17 7.11 4.29 5.7 5.71 10.59 10.6 5.7 15.49l1.41 1.42L12 12l4.89 4.91 1.41-1.42-4.89-4.89 4.89-4.89Z" />
            </svg>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import type { ToastType } from '~/composables/useToast';

const { toasts, remove } = useToast();

const styleFor = (type: ToastType) => {
  if (type === 'success') {
    return 'border-emerald-200 bg-emerald-50/95 text-emerald-700';
  }

  if (type === 'error') {
    return 'border-rose-200 bg-rose-50/95 text-rose-700';
  }

  return 'border-indigo-200 bg-indigo-50/95 text-indigo-700';
};

const iconWrapFor = (type: ToastType) => {
  if (type === 'success') {
    return 'bg-emerald-100 text-emerald-600';
  }

  if (type === 'error') {
    return 'bg-rose-100 text-rose-600';
  }

  return 'bg-indigo-100 text-indigo-600';
};
</script>
