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
import { router } from "@inertiajs/react"; // ✅ PENTING: Tambahkan ini untuk submit data

const IconCalendar = Icon.IconCalendar;

// --- Fungsi utilitas Tanggal ---
const getDaysInMonth = (year, month) => {
    return new Date(year, month + 1, 0).getDate();
};

const getFirstDayOfMonth = (year, month) => {
    return new Date(year, month, 1).getDay();
};

const months = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

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
    const [currentDate, setCurrentDate] = React.useState(initialDate || new Date());
    const currentMonthIndex = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    
    const daysOfWeek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    const daysInMonth = getDaysInMonth(currentYear, currentMonthIndex);
    const firstDay = getFirstDayOfMonth(currentYear, currentMonthIndex);

    const calendarDays = [];
    for (let i = 0; i < firstDay; i++) {
        calendarDays.push(null);
    }
    for (let i = 1; i <= daysInMonth; i++) {
        calendarDays.push(i);
    }

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
    
    const today = new Date();
    const isToday = (day) => {
        return day === today.getDate() && 
               currentMonthIndex === today.getMonth() && 
               currentYear === today.getFullYear();
    };

    return (
        <div className="bg-white p-4 rounded-lg shadow-2xl border border-gray-100 w-full">
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

            <div className="grid grid-cols-7 text-xs font-semibold text-gray-600 mb-2">
                {daysOfWeek.map(day => (
                    <div key={day} className="text-center">{day}</div>
                ))}
            </div>

            <div className="grid grid-cols-7 gap-1">
                {calendarDays.map((date, index) => (
                    <div 
                        key={index} 
                        className={`text-center text-sm p-1.5 rounded-full cursor-pointer transition 
                            ${date === null ? 'invisible' : ''}
                            ${isToday(date) 
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
    const [selectedDateRaw, setSelectedDateRaw] = React.useState(''); // ✅ Simpan format YYYY-MM-DD
    const [showCalendar, setShowCalendar] = React.useState(false);

    if (!show) return null;

    // ✅ FUNGSI UNTUK MENGUBAH TANGGAL
    const handleDateSelect = (dateString) => {
        // Simpan format asli untuk dikirim ke backend
        setSelectedDateRaw(dateString);
        
        // Format untuk tampilan: DD/MM/YYYY
        const dateObj = new Date(dateString);
        const formattedDate = `${dateObj.getDate().toString().padStart(2, '0')}/${(dateObj.getMonth() + 1).toString().padStart(2, '0')}/${dateObj.getFullYear()}`;
        
        setSelectedDate(formattedDate);
        setShowCalendar(false);
    };

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm"
            onClick={onClose}
        >
            <div
                className="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="p-6">
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-xl font-semibold text-gray-900">
                            Tentukan Tanggal Pencairan Penghargaan
                        </h2>
                        <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
                            <X size={24} />
                        </button>
                    </div>

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
                        
                        {showCalendar && (
                            <div className="absolute top-full left-0 mt-2 z-50 w-full">
                                <MinimalistCalendar onSelectDate={handleDateSelect} />
                            </div>
                        )}
                    </div>

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
                                // ✅ Kirim tanggal dalam format YYYY-MM-DD
                                onConfirm(bukuId, selectedDateRaw);
                            }}
                            className="bg-black hover:bg-gray-800 text-white"
                            disabled={!selectedDate}
                        >
                            Kirim
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
};

// Komponen BukuItem
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

// Komponen SelectDropdown
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
    
    // ✅ FUNGSI UNTUK SUBMIT DATA KE BACKEND
   // ✅ FUNGSI UNTUK SUBMIT DATA KE BACKEND
const handleConfirm = (id, tanggalPencairan) => {
    // Tutup modal
    closeModal();
    
    // Kirim data ke backend menggunakan Inertia router
    router.post('/hrd/pencairan', {
        book_id: id,
        payment_date: tanggalPencairan
    }, {
        preserveState: false, // ✅ Force reload halaman
        onSuccess: (page) => {
            // Tampilkan notifikasi sukses
            triggerSuccessNotification();
            
            // ✅ Reload halaman setelah 2 detik
            setTimeout(() => {
                router.reload({ only: ['submissions'] });
            }, 2000);
        },
        onError: (errors) => {
            console.error('Error submitting payment:', errors);
            alert('Gagal mengirim data pencairan. Silakan coba lagi.');
        }
    });
};

    const handleBukuClick = (id) => {
        openModal(id);
    };

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

            <DatePickerModal
                show={showModal}
                onClose={closeModal}
                onConfirm={handleConfirm}
                bukuId={selectedBukuId}
            />

            <SuccessNotification 
                show={showSuccess}
                message="Berhasil dikirim"
                onClose={() => setShowSuccess(false)}
            />
        </AppLayout>
    );
}