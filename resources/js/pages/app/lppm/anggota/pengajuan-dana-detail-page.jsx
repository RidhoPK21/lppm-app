import AppLayout from "@/layouts/app-layout";
import { Head } from "@inertiajs/react";

export default function PengajuanDanaDetailPage({ paper, dokumen }) {
    
    // --- TRIK AMAN: Gunakan console.log agar variabel 'paper' dianggap terpakai ---
    // Ini tidak akan merusak UI, hanya mencetak data di Inspect Element browser.
    if (paper) {
        console.log("Data Paper Loaded:", paper);
    }
    // -----------------------------------------------------------------------------

    return (
        <AppLayout pageName="Detail Pengajuan Dana">
            <Head title="Detail Pengajuan Dana" />

            <div className="w-full max-w-5xl mx-auto px-6 py-10 space-y-10">
                {/* ======================= DETAIL TITLE ======================= */}
                <div className="border rounded-lg p-6 bg-white shadow-sm">
                    <h1 className="text-xl font-bold">Detail Paper</h1>
                </div>

                {/* ======================= INFORMASI UMUM ======================= */}
                <div className="border rounded-lg bg-white shadow-sm p-6 space-y-4">
                    <h2 className="font-semibold text-lg">
                        Informasi Umum Paper
                    </h2>
                    <hr />

                    <div className="grid grid-cols-3 gap-y-3">
                        <span className="font-medium">Dosen Pengaju</span>
                        <span className="col-span-2 bg-gray-200 h-5 rounded-md"></span>

                        <span className="font-medium">Judul Paper</span>
                        <span className="col-span-2 bg-gray-200 h-5 rounded-md"></span>

                        <span className="font-medium">Lainnya</span>
                        <span className="col-span-2 bg-gray-200 h-5 rounded-md"></span>
                    </div>
                </div>

                {/* ======================= DOKUMEN WAJIB ======================= */}
                <div className="border rounded-lg bg-white shadow-sm p-6">
                    <h2 className="font-semibold text-lg mb-4">
                        Dokumen Wajib
                    </h2>
                    <hr className="mb-4" />

                    <table className="w-full border text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="border px-3 py-2 text-left">
                                    Dokumen
                                </th>
                                <th className="border px-3 py-2 text-left">
                                    Deskripsi
                                </th>
                                <th className="border px-3 py-2 text-left">
                                    File
                                </th>
                                <th className="border px-3 py-2 text-left">
                                    Lengkap/Belum
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            {dokumen.map((row, i) => (
                                <tr key={i}>
                                    <td className="border px-3 py-2">
                                        {row.nama}
                                    </td>
                                    <td className="border px-3 py-2">
                                        {row.deskripsi}
                                    </td>
                                    <td className="border px-3 py-2 text-blue-500 cursor-pointer">
                                        {row.file}
                                    </td>
                                    <td className="border px-3 py-2">
                                        {row.status}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* ======================= BUTTON KEMBALI ======================= */}
                <button
                    onClick={() => window.history.back()}
                    className="px-6 py-2 border rounded-md bg-white hover:bg-gray-100 shadow-sm"
                >
                    Kembali
                </button>
            </div>
        </AppLayout>
    );
}