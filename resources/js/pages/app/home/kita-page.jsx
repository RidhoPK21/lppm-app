import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import AppLayout from "@/layouts/app-layout";
import * as Icon from "@tabler/icons-react";
import { ChevronDown, X, CheckCircle, ChevronLeft, ChevronRight } from "lucide-react"; 
import * as React from "react";
// import { router } from "@inertiajs/react"; // Di-comment karena tidak digunakan di kode yang relevan

// Asumsi Anda memiliki atau dapat menggunakan Calendar icon dari @tabler/icons-react
const IconCalendar = Icon.IconCalendar;

// --- Fungsi utilitas Tanggal ---
const getDaysInMonth = (year, month) => {
    return new Date(year, month + 1, 0).getDate();
};

const getFirstDayOfMonth = (year, month) => {
    // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
    return new Date(year, month, 1).getDay();
};

const months = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];
// --- Akhir Fungsi utilitas Tanggal ---

// Komponen Notifikasi Sukses Sederhana
const SuccessNotification = ({ show, message, onClose }) => {
    React.useEffect(() => {
        if (show) {
            const timer = setTimeout(() => {
                onClose();
            }, 3000); 
            return () => clearTimeout(timer);
        }
    }, [show, onClose]);

    if (!show) return null;

    return (
        <div className="fixed top-5 left-1/2 transform -translate-x-1/2 z-[60]">
            <div className="flex items-center p-4 bg-green-600 text-white rounded-lg shadow-xl max-w-sm">
                <CheckCircle className="h-6 w-6 mr-3" />
                <span className="font-medium">{message}</span>
            </div>
        </div>
    );
};

// Komponen Kalender Minimalis yang Lengkap (Dinamis)
const MinimalistCalendar = ({ onSelectDate, initialDate }) => {
    // State untuk bulan dan tahun yang sedang dilihat
    const [currentDate, setCurrentDate] = React.useState(initialDate || new Date());
    const currentMonthIndex = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    
    const daysOfWeek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    const daysInMonth = getDaysInMonth(currentYear, currentMonthIndex);
    const firstDay = getFirstDayOfMonth(currentYear, currentMonthIndex);

    // Membuat array tanggal dalam bulan
    const calendarDays = [];
    for (let i = 0; i < firstDay; i++) {
        calendarDays.push(null); // Placeholder untuk hari sebelum tanggal 1
    }
    for (let i = 1; i <= daysInMonth; i++) {
        calendarDays.push(i);
    }

    // Fungsi untuk navigasi bulan
    const handlePrevMonth = () => {
        setCurrentDate(prev => new Date(prev.getFullYear(), prev.getMonth() - 1, 1));
    };

    const handleNextMonth = () => {
        setCurrentDate(prev => new Date(prev.getFullYear(), prev.getMonth() + 1, 1));
    };

    const handleDateClick = (day) => {
        if (day) {
            const selectedDateString = new Date(currentYear, currentMonthIndex, day).toISOString().split('T')[0];
            onSelectDate(selectedDateString);
        }
    };
    
    // Anggap tanggal 15 adalah tanggal yang dipilih sebelumnya (untuk styling)
    const simulatedSelectedDay = 15;

    return (
        <div className="bg-white p-4 rounded-lg shadow-2xl border border-gray-100 w-full">
            {/* Header Bulan dan Tahun */}
            <div className="flex justify-between items-center mb-4">
                <button 
                    onClick={handlePrevMonth}
                    className="p-1 rounded-full hover:bg-gray-100 transition"
                    aria-label="Bulan Sebelumnya"
                >
                    <ChevronLeft className="w-4 h-4" />
                </button>
                <span className="font-bold text-base text-gray-800">
                    {months[currentMonthIndex]} {currentYear}
                </span>
                <button 
                    onClick={handleNextMonth}
                    className="p-1 rounded-full hover:bg-gray-100 transition"
                    aria-label="Bulan Berikutnya"
                >
                    <ChevronRight className="w-4 h-4" />
                </button>
            </div>

            {/* Nama Hari */}
            <div className="grid grid-cols-7 text-xs font-semibold text-gray-600 mb-2">
                {daysOfWeek.map(day => (
                    <div key={day} className="text-center">{day}</div>
                ))}
            </div>

            {/* Tanggal */}
            <div className="grid grid-cols-7 gap-1">
                {calendarDays.map((date, index) => (
                    <div 
                        key={index} 
                        className={`text-center text-sm p-1.5 rounded-full cursor-pointer transition 
                            ${date === null ? 'invisible' : ''}
                            ${date === simulatedSelectedDay && currentMonthIndex === new Date().getMonth() && currentYear === new Date().getFullYear() 
                                ? 'bg-black text-white font-bold' 
                                : date !== null ? 'hover:bg-gray-200' : ''}
                        `}
                        onClick={() => handleDateClick(date)}
                    >
                        {date}
                    </div>
                ))}
            </div>
        </div>
    );
};


// Komponen Modal Tanggal Pencairan yang Diperbarui
const DatePickerModal = ({ show, onClose, onConfirm, bukuId }) => {
    const [selectedDate, setSelectedDate] = React.useState('');
    const [showCalendar, setShowCalendar] = React.useState(false);

    if (!show) return null;

    // FUNGSI UNTUK MENGUBAH TANGGAL
    const handleDateSelect = (dateString) => {
        // Menggunakan fungsi bawaan Date untuk memformat
        const dateObj = new Date(dateString);
        // Format: DD/MM/YYYY
        const formattedDate = `${dateObj.getDate().toString().padStart(2, '0')}/${(dateObj.getMonth() + 1).toString().padStart(2, '0')}/${dateObj.getFullYear()}`;
        
        setSelectedDate(formattedDate);
        setShowCalendar(false); // Sembunyikan kalender setelah tanggal dipilih
    };

    return (
        // LATAR BELAKANG yang diperbaiki: Menggunakan bg-gray-900/40 untuk latar belakang semi-transparan 
        // sehingga halaman di belakangnya tetap terlihat namun diblur.
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm"
            onClick={onClose}
        >
            {/* Modal Content */}
            <div
                className="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="p-6">
                    {/* Header Modal */}
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-semibold text-gray-900">
                            Tentukan Tanggal Pencairan Penghargaan
                        </h2>
                        <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
                            <X size={24} />
                        </button>
                    </div>

                    {/* Date Picker Input SIMULASI */}
                    <div className="mb-8 relative">
                        <div
                            className="flex items-center border border-gray-300 rounded-md p-2 w-full cursor-pointer bg-white"
                            onClick={() => setShowCalendar(!showCalendar)}
                        >
                            <IconCalendar size={20} className="text-gray-500 mr-2" />
                            <span className={`flex-1 ${selectedDate ? 'text-gray-900' : 'text-gray-500'}`}>
                                {selectedDate || "Pick a date"}
                            </span>
                        </div>
                        
                        {/* KALENDER LENGKAP MUNCUL */}
                        {showCalendar && (
                            <div className="absolute top-full left-0 mt-2 z-50 w-full">
                                <MinimalistCalendar onSelectDate={handleDateSelect} />
                            </div>
                        )}
                    </div>

                    {/* Footer / Buttons */}
                    <div className="flex justify-end space-x-3">
                        <Button
                            variant="outline"
                            onClick={onClose}
                            className="text-gray-700 border-gray-300 hover:bg-gray-50"
                        >
                            Kembali
                        </Button>
                        <Button
                            onClick={() => {
                                onConfirm(bukuId);
                            }}
                            className="bg-black hover:bg-gray-800 text-white"
                            disabled={!selectedDate} // Nonaktifkan jika tanggal belum dipilih
                        >
                            Kirim
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
};

// Komponen BukuItem tetap sama
const BukuItem = ({ id, judul, penulis, status, tanggal, onClick }) => (
    <div
        className="bg-white rounded-lg shadow-md mb-2 cursor-pointer hover:shadow-lg transition-shadow"
        onClick={() => onClick(id)}
    >
        <div className="flex items-stretch p-4">
            <div className="mr-4 flex items-center justify-center w-10 h-10 rounded-full bg-black">
                <Icon.IconTriangle size={20} fill="white" />
            </div>

            <div className="flex-1 min-w-0 flex flex-col justify-center">
                <div className="font-semibold text-lg truncate">{judul}</div>
                <div className="text-sm text-gray-500 truncate">{penulis}</div>
            </div>

            <div className="text-right ml-4 flex flex-col justify-between h-full">
                <div className="text-gray-500 text-sm">
                    Status :{" "}
                    <span
                        className={`capitalize font-normal ${
                            status === "Disetujui LPPM"
                                ? "text-green-600"
                                : status === "Selesai (Cair)"
                                ? "text-blue-600"
                                : status === "Ditolak/Revisi"
                                ? "text-red-600"
                                : "text-orange-500"
                        }`}
                    >
                        {status}
                    </span>
                </div>
                <div className="text-gray-500 text-xs">{tanggal}</div>
            </div>
        </div>
    </div>
);

// Komponen SelectDropdown tetap sama
const SelectDropdown = ({ label, options, className = "", onChange }) => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <div
                className={`flex items-center justify-between border border-gray-300 rounded-md bg-white text-sm px-3 h-10 cursor-pointer ${className}`}
            >
                {label}
                <ChevronDown className="h-4 w-4 ml-2 opacity-50" />
            </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[120px]">
            {options.map((option) => (
                <DropdownMenuItem
                    key={option}
                    onSelect={() => onChange(option)}
                >
                    {option}
                </DropdownMenuItem>
            ))}
        </DropdownMenuContent>
    </DropdownMenu>
);

// Komponen KitaPage
export default function KitaPage({ submissions = [] }) {
    const [search, setSearch] = React.useState("");
    const [searchBy, setSearchBy] = React.useState("Search by");
    const [sortBy, setSortBy] = React.useState("Sort by");

    // === STATE UNTUK MODAL DAN NOTIFIKASI ===
    const [showModal, setShowModal] = React.useState(false);
    const [selectedBukuId, setSelectedBukuId] = React.useState(null);
    const [showSuccess, setShowSuccess] = React.useState(false); 

    const openModal = (id) => {
        setSelectedBukuId(id);
        setShowModal(true);
    };

    const closeModal = () => {
        setShowModal(false);
        setSelectedBukuId(null);
    };
    
    const triggerSuccessNotification = () => {
        setShowSuccess(true);
    };
    
    const handleConfirm = (id) => {
        // 1. Tutup modal
        closeModal();
        
        // 2. Tampilkan notifikasi "Berhasil dikirim"
        triggerSuccessNotification();

        // Logika pengiriman data ke backend dilakukan di sini
        // Misalnya: 
        // router.post('/api/pencairan', { buku_id: id, tanggal: selectedDate });
    };

    const handleBukuClick = (id) => {
        openModal(id);
    };
    // =======================================

    // Filter dan format data untuk ditampilkan
    const formattedSubmissions = submissions
        .filter(item => item.status === "APPROVED_CHIEF" || item.status_label === "Disetujui (Ke HRD)")
        .map(item => ({
            ...item,
            status_label: item.status_label === "Disetujui (Ke HRD)" 
                ? "Disetujui LPPM" 
                : item.status_label
        }));

    return (
        <AppLayout>
            <Card className="h-full border-none shadow-none">
                <CardHeader className="p-0 space-y-4">
                    

                    <div className="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 items-center px-4">
                        <div className="flex-1 flex border border-gray-300 rounded-md overflow-hidden h-10 w-full">
                            <input
                                type="text"
                                placeholder="Cari judul atau nama dosen..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="flex-1 p-2 focus:outline-none placeholder:text-gray-400 text-sm border-none"
                            />
                            <Button
                                variant="default"
                                className="h-full px-4 bg-gray-100 text-gray-800 hover:bg-gray-200 rounded-l-none border-l border-gray-300 shadow-none font-normal text-sm"
                            >
                                Search
                            </Button>
                        </div>

                        <div className="w-full md:w-[150px]">
                            <SelectDropdown
                                label={searchBy}
                                options={["Judul", "Dosen"]}
                                className="w-full h-10"
                                onChange={setSearchBy}
                            />
                        </div>

                        <div className="w-full md:w-[120px]">
                            <SelectDropdown
                                label={sortBy}
                                options={["Terbaru", "Judul"]}
                                className="w-full h-10"
                                onChange={setSortBy}
                            />
                        </div>
                    </div>

                    <hr className="mt-4 mb-0" />
                </CardHeader>

                <CardContent className="p-0 px-4">
                    <div className="space-y-3">
                        {formattedSubmissions.length === 0 && (
                            <div className="text-center py-10 text-gray-500">
                                Belum ada pengajuan penghargaan buku yang masuk.
                            </div>
                        )}

                        {formattedSubmissions.map((item) => (
                            <BukuItem
                                key={item.id}
                                id={item.id}
                                judul={item.judul}
                                penulis={item.nama_dosen}
                                status={item.status_label}
                                tanggal={item.tanggal_pengajuan}
                                onClick={handleBukuClick}
                            />
                        ))}
                    </div>
                </CardContent>
            </Card>

            {/* Komponen Modal */}
            <DatePickerModal
                show={showModal}
                onClose={closeModal}
                onConfirm={handleConfirm}
                bukuId={selectedBukuId}
            />

            {/* Komponen Notifikasi Sukses */}
            <SuccessNotification 
                show={showSuccess}
                message="Berhasil dikirim"
                onClose={() => setShowSuccess(false)}
            />
        </AppLayout>
    );
}