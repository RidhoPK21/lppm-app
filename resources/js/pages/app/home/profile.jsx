import AppLayout from "@/layouts/app-layout";

// Komponen helper untuk baris data
const DataRow = ({ label, value, editable = false }) => (
    <div className="flex justify-between items-center py-4 px-4 border-b border-gray-200">
        <div className="text-gray-600 font-medium">{label}</div>
        <div className="flex items-center space-x-2">
            <span className="text-gray-800">{value || '...'}</span> 
            {editable && (
                // Ikon Pensil SVG inline
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    className="h-4 w-4 text-gray-400 cursor-pointer hover:text-gray-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    strokeWidth={2}
                >
                    <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                    />
                </svg>
            )}
        </div>
    </div>
);

export default function Profile({ user, error }) {
    const userData = user || {}; 

    const { 
        name, 
        email, 
        NIDN,
        ProgramStudi,
        SintaID,
        ScopusID,
        photo
    } = userData;

    return (
        <AppLayout>
            {/* PERUBAHAN UTAMA:
               Menghapus 'max-w-3xl' dari div utama dan menggantinya dengan 'max-w-screen-xl mx-auto' 
               atau 'max-w-full' untuk lebar yang jauh lebih besar. 
               Saya akan menggunakan 'max-w-full' agar hanya dibatasi oleh AppLayout dan padding.
            */}
            <div className="p-6"> 
                {/* Kontainer baru untuk memastikan konten tetap memiliki batas maksimal yang besar, 
                   misalnya 6xl atau 7xl, atau bahkan 8xl. Saya pilih max-w-7xl untuk sangat lebar. */}
                <div className="max-w-7xl mx-auto"> 

                    {/* Judul Halaman: Pengaturan Akun */}
                    <h1 className="text-lg font-medium text-gray-800 flex items-center mb-8">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-5 w-5 mr-2 text-gray-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            strokeWidth={2}
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"
                            />
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        Pengaturan Akun
                    </h1>

                    {error && (
                        <div className="bg-red-100 text-red-700 p-3 rounded-md mb-4">
                            {error}
                        </div>
                    )}

                    {/* Bagian Foto Profil */}
                    <div className="flex flex-col items-center mb-10">
                        <p className="text-sm text-gray-500 mb-4">Add profile picture</p>
                        <div className="relative group">
                            <img
                                src={photo || "/images/default-profile.png"} 
                                alt="Foto Profil"
                                className="w-24 h-24 rounded-full object-cover border-2 border-gray-300 shadow"
                            />
                            {/* Ikon Pensil di atas foto profil */}
                            <div className="absolute top-0 right-0 p-1 bg-white rounded-full border border-gray-300 cursor-pointer shadow-md">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    className="h-4 w-4 text-gray-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    strokeWidth={2}
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <p className="text-sm text-gray-500 mt-2">Edit user profile</p>
                    </div>

                    {/* Bagian Data Pengguna */}
                    <div className="border border-gray-200 rounded-lg bg-white shadow-sm divide-y divide-gray-200">
                        <DataRow label="Nama" value={name} />
                        <DataRow label="Akun Email" value={email} /> 
                        <DataRow label="NIDN" value={NIDN} /> 
                        <DataRow label="Program Studi" value={ProgramStudi} />
                        <DataRow label="Sinta ID" value={SintaID} editable={true} />
                        <DataRow label="Scopus ID" value={ScopusID} editable={true} />
                    </div>

                    {/* Tombol Aksi */}
                    <div className="flex justify-between items-center mt-8">
                        <button className="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-150">
                            Kembali
                        </button>
                        <button className="px-6 py-2 bg-black text-white rounded-lg font-medium hover:bg-gray-800 transition duration-150 shadow-md">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}