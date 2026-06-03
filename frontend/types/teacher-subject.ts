export type TeacherSubjectItem = {
  id: number;
  teacher_id: number;
  subject_id: number;
  teacher?: {
    id: number;
    nip: string;
    nama: string;
    email: string;
  } | null;
  subject?: {
    id: number;
    kode_mapel: string;
    nama_mapel: string;
  } | null;
  created_at?: string | null;
  updated_at?: string | null;
};

export type TeacherOption = {
  id: number;
  nama: string;
  nip: string;
  email?: string | null;
};

export type SubjectOption = {
  id: number;
  kode_mapel: string;
  nama_mapel: string;
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
