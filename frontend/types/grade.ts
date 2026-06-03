export type TeacherOption = {
  id: number;
  nama: string;
  nip: string;
};

export type GradeSubjectOption = {
  id: number;
  kode_mapel: string;
  nama_mapel: string;
};

export type GradeClassroomOption = {
  kelas: {
    id: number;
    nama_kelas: string;
  } | null;
  subjects: GradeSubjectOption[];
};

export type GradeOptionsResponse = {
  teacher_id: number;
  classrooms: GradeClassroomOption[];
  semesters: string[];
};

export type GradeStudentRow = {
  siswa_id: number;
  nis: string;
  nama_lengkap: string;
  jenis_kelamin: 'L' | 'P';
  grade?: {
    id: number;
    tugas: number;
    uts: number;
    uas: number;
    nilai_akhir: number;
  } | null;
};
