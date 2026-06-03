export const useSchoolProfile = () => {
  return useState('school-profile', () => ({
    namaSekolah: 'School Admin',
    tagline: 'Sistem Informasi Sekolah',
  }));
};
