<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="state.open"
        class="fixed inset-0 z-[210] flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm"
        @click.self="cancel"
      >
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="translate-y-3 scale-95 opacity-0"
          enter-to-class="translate-y-0 scale-100 opacity-100"
        >
          <div
            v-if="state.open"
            class="w-full max-w-md rounded-[2rem] border border-white/80 bg-white p-6 shadow-[0_25px_70px_rgba(15,23,42,0.25)]"
            role="alertdialog"
            aria-modal="true"
          >
            <div class="flex items-start gap-4">
              <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" :class="iconWrap">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M12 2 1 21h22L12 2Zm0 5 7.53 13H4.47L12 7Zm-1 4v4h2v-4h-2Zm0 6v2h2v-2h-2Z" />
                </svg>
              </span>
              <div class="flex-1">
                <h3 class="text-lg font-bold text-slate-900">{{ state.title }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ state.message }}</p>
              </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
              <button
                ref="cancelButton"
                type="button"
                class="rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:bg-slate-50"
                @click="cancel"
              >
                {{ state.cancelLabel }}
              </button>
              <button
                type="button"
                class="rounded-2xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition"
                :class="confirmClass"
                @click="accept"
              >
                {{ state.confirmLabel }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
const state = useConfirmState();
const { settle } = useConfirm();
const cancelButton = ref<HTMLButtonElement | null>(null);

const iconWrap = computed(() => (state.value.tone === 'primary' ? 'bg-indigo-100 text-indigo-600' : 'bg-rose-100 text-rose-600'));

const confirmClass = computed(() => (state.value.tone === 'primary'
  ? 'bg-gradient-to-r from-indigo-600 to-blue-500 shadow-indigo-200'
  : 'bg-gradient-to-r from-rose-500 to-rose-600 shadow-rose-200'));

const accept = () => settle(true);
const cancel = () => settle(false);

const handleKeydown = (event: KeyboardEvent) => {
  if (!state.value.open) {
    return;
  }

  if (event.key === 'Escape') {
    cancel();
  } else if (event.key === 'Enter') {
    accept();
  }
};

watch(() => state.value.open, async (open) => {
  if (open) {
    await nextTick();
    cancelButton.value?.focus();
  }
});

onMounted(() => document.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => document.removeEventListener('keydown', handleKeydown));
</script>
