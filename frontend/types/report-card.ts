export type ReportCardGrade = {
  id: number;
  mapel_id: number;
  kode_mapel: string | null;
  nama_mapel: string;
  tugas: number;
  uts: number;
  uas: number;
  nilai_akhir: number;
  predikat: string;
};

export type ReportCard = {
  student: {
    id: number;
    nis: string;
    nama_lengkap: string;
    jenis_kelamin: 'L' | 'P';
    kelas: { id: number; nama_kelas: string; wali_kelas: string | null; tahun_ajaran: string | null } | null;
  };
  semester: string | null;
  available_semesters: string[];
  grades: ReportCardGrade[];
  average: number | null;
  average_predikat: string | null;
  ranking: { position: number | null; total: number };
  attendance_recap: { hadir: number; izin: number; sakit: number; alfa: number; total: number };
  school: { nama_sekolah: string; tagline: string | null };
};
