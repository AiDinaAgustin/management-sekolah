export type ToastType = 'success' | 'error' | 'info';

export type ToastItem = {
  id: number;
  type: ToastType;
  message: string;
};

let toastSeq = 0;

export const useToast = () => {
  const toasts = useState<ToastItem[]>('toasts', () => []);

  const remove = (id: number) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
  };

  const push = (type: ToastType, message: string, duration = 3500) => {
    toastSeq += 1;
    const id = toastSeq;
    toasts.value = [...toasts.value, { id, type, message }];

    if (import.meta.client && duration > 0) {
      setTimeout(() => remove(id), duration);
    }

    return id;
  };

  const success = (message: string, duration?: number) => push('success', message, duration);
  const error = (message: string, duration?: number) => push('error', message, duration);
  const info = (message: string, duration?: number) => push('info', message, duration);

  return { toasts, push, success, error, info, remove };
};
