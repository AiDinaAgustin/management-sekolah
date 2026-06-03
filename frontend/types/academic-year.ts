export type AcademicYearItem = {
  id: number;
  nama_tahun_ajaran: string;
  semester: 'Ganjil' | 'Genap';
  status_aktif: boolean;
  jumlah_kelas?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type AcademicYearOption = {
  id: number;
  nama_tahun_ajaran: string;
  semester: 'Ganjil' | 'Genap';
  status_aktif: boolean;
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
