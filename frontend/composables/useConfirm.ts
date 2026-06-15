export type ConfirmTone = 'danger' | 'primary';

export type ConfirmOptions = {
  title?: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  tone?: ConfirmTone;
};

export type ConfirmState = ConfirmOptions & {
  open: boolean;
  resolve: ((value: boolean) => void) | null;
};

export const useConfirmState = () => {
  return useState<ConfirmState>('confirm-dialog', () => ({
    open: false,
    message: '',
    resolve: null,
  }));
};

export const useConfirm = () => {
  const state = useConfirmState();

  const confirm = (options: ConfirmOptions | string): Promise<boolean> => {
    const normalized: ConfirmOptions = typeof options === 'string' ? { message: options } : options;

    return new Promise<boolean>((resolve) => {
      state.value = {
        open: true,
        title: normalized.title || 'Konfirmasi',
        message: normalized.message,
        confirmLabel: normalized.confirmLabel || 'Hapus',
        cancelLabel: normalized.cancelLabel || 'Batal',
        tone: normalized.tone || 'danger',
        resolve,
      };
    });
  };

  const settle = (result: boolean) => {
    state.value.resolve?.(result);
    state.value = { ...state.value, open: false, resolve: null };
  };

  return { confirm, settle };
};
