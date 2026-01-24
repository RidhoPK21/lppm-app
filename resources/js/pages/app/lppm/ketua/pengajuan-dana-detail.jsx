import AppLayout from "@/layouts/app-layout";

export default function KetuaPengajuanDanaDetailPage({ paper, dokumen }) {
    return (
        <AppLayout pageName="Detail Paper">
            <div className="w-full max-w-5xl mx-auto px-6 py-10 space-y-10">

                <div className="border rounded-lg p-6 bg-white shadow-sm">
                    <h1 className="text-xl font-bold">Detail Paper</h1>
                </div>

                <div className="border rounded-lg bg-white shadow-sm p-6 space-y-4">
                    <h2 className="font-semibold text-lg">
                        Informasi Umum Paper
                    </h2>
                    <hr />

                    <div className="grid grid-cols-3 gap-y-3">
                        <span className="font-medium">Dosen Pengaju</span>
                        <span className="col-span-2">{paper.pengaju}</span>

                        <span className="font-medium">Judul Paper</span>
                        <span className="col-span-2">{paper.judul}</span>

                        <span className="font-medium">Tanggal</span>
                        <span className="col-span-2">{paper.tanggal}</span>
                    </div>
                </div>

                <div className="border rounded-lg bg-white shadow-sm p-6">
                    <h2 className="font-semibold text-lg mb-4">
                        Dokumen Wajib
                    </h2>
                    <hr className="mb-4" />

                    <table className="w-full border text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="border px-3 py-2 text-left">Dokumen</th>
                                <th className="border px-3 py-2 text-left">Status</th>
                                <th className="border px-3 py-2 text-left">File</th>
                            </tr>
                        </thead>
                        <tbody>
                            {dokumen.map((row) => (
                                <tr key={row.nama}>
                                    <td className="border px-3 py-2">{row.nama}</td>
                                    <td className="border px-3 py-2">{row.status}</td>
                                    <td className="border px-3 py-2 text-blue-500 cursor-pointer">
                                        {row.file}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="flex justify-start">
                <button
                    onClick={() => window.history.back()}
                    className="px-6 py-2 border rounded-md"
                >
                    Kembali
                </button>
            </div>

            </div>
        </AppLayout>
    );
}
