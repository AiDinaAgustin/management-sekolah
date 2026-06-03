export type SubjectItem = {
  id: number;
  kode_mapel: string;
  nama_mapel: string;
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
