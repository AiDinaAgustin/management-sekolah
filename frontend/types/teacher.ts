export type TeacherItem = {
  id: number;
  user_id: number | null;
  nip: string;
  nama: string;
  email: string;
  no_hp: string | null;
  alamat: string | null;
  jumlah_kelas?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type PaginatedResponse<T> = {
  data: T[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    path: string;
    per_page: number;
    to: number | null;
    total: number;
  };
};
