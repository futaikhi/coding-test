export const parseAuditLog = (log: any): string => {
    if (log.event === 'created') return 'Membuat data baru ke dalam sistem.';
    if (log.event === 'deleted') return 'Menghapus data dari sistem.';
    if (log.event === 'imported') return log.new_values?.summary || 'Berhasil memproses dokumen impor CSV.';
    if (log.event === 'exported') return log.new_values?.info || 'Mengekspor data ke file Excel/CSV.';
    
    if (log.event === 'updated' && log.new_values) {
        // Ambil nama properti objek JSON yang mengalami perubahan nilai
        const fields = Object.keys(log.new_values).filter(k => !['updated_at', 'created_at'].includes(k));
        return `Mengubah spesifikasi pada kolom: [ ${fields.join(', ')} ]`;
    }
    return 'Aktivitas sistem internal.';
};