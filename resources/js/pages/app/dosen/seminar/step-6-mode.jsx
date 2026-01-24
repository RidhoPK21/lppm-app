import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react'; // Pakai router, bukan useForm untuk kasus ini
import AppLayout from '@/layouts/app-layout';
import SeminarStepper from '@/components/seminar-stepper';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
// Import Komponen Dialog (Pop-up Bagus)
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { route } from 'ziggy-js';
import { 
    Monitor, 
    MapPin, 
    Clock, 
    ArrowRight
} from 'lucide-react';

export default function Step6Mode({ seminar, isWaiting }) {
    
    // State untuk kontrol Modal & Data
    const [isConfirmOpen, setIsConfirmOpen] = useState(false);
    const [selectedMode, setSelectedMode] = useState(null); // 'online' atau 'onsite'
    const [processing, setProcessing] = useState(false);

    // 1. Trigger saat tombol ditekan (Buka Dialog)
    const handleSelectMode = (mode) => {
        setSelectedMode(mode);
        setIsConfirmOpen(true);
    };

    // 2. Eksekusi Kirim Data ke Backend
    const confirmSelection = () => {
        router.post(route('dosen.seminar.step6.store', seminar.id), {
            mode: selectedMode // Kirim data di Body (Pasti terbaca Controller)
        }, {
            onStart: () => setProcessing(true),
            onFinish: () => {
                setProcessing(false);
                setIsConfirmOpen(false);
            }
        });
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' },
            { title: 'Tahap 6', href: '#' }
        ]}>
            <Head title="Mode Seminar" />

            <div className="w-full max-w-6xl mx-auto px-6 py-6 pb-24">
                
                <div className="text-center mb-8">
                    <h1 className="text-2xl font-bold">Pengajuan Dana Registrasi Seminar</h1>
                    <p className="text-gray-500">Tahap 6: Konfirmasi Pelaksanaan & Surat Tugas</p>
                </div>

                {/* Stepper */}
                <SeminarStepper currentStep={6} />

                <div className="mt-12 max-w-4xl mx-auto">
                    
                    {/* === KONDISI: MENUNGGU HRD (Onsite) === */}
                    {isWaiting ? (
                        <div className="flex flex-col items-center justify-center text-center space-y-6">
                            <div className="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center animate-pulse border-4 border-purple-50">
                                <Clock className="w-12 h-12 text-purple-600" />
                            </div>
                            <div>
                                <h2 className="text-2xl font-bold text-gray-900">Menunggu Surat Tugas HRD</h2>
                                <p className="text-gray-500 mt-2 max-w-lg mx-auto leading-relaxed">
                                    Anda memilih mode <strong>Onsite (Luring)</strong>. Sistem telah mengirim permintaan penerbitan Surat Tugas ke bagian HRD. <br/>
                                    Mohon tunggu notifikasi selanjutnya.
                                </p>
                            </div>
                            <Link href={route('dosen.seminar.index')}>
                                <Button variant="outline" className="mt-4 gap-2">
                                    Kembali ke Dashboard
                                    <ArrowRight className="w-4 h-4" />
                                </Button>
                            </Link>
                        </div>
                    ) : (
                        /* === KONDISI: PILIH MODE === */
                        <div className="grid md:grid-cols-2 gap-6">
                            
                            {/* Opsi 1: ONLINE */}
                            <Card 
                                onClick={() => handleSelectMode('online')}
                                className="group relative overflow-hidden border-2 border-transparent hover:border-blue-500 hover:shadow-xl transition-all cursor-pointer bg-white"
                            >
                                <div className="absolute top-0 left-0 w-full h-1 bg-blue-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left" />
                                <CardContent className="p-8 flex flex-col items-center text-center gap-6 h-full justify-between">
                                    <div className="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <Monitor className="w-10 h-10" />
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-xl font-bold text-gray-900 group-hover:text-blue-700 transition-colors">
                                            Seminar Online (Daring)
                                        </h3>
                                        <p className="text-sm text-gray-500 leading-relaxed">
                                            Saya mengikuti seminar secara virtual melalui Zoom/Gmeet. <br/>
                                            <span className="font-semibold text-blue-600">Tidak memerlukan Surat Tugas.</span>
                                        </p>
                                    </div>
                                    <Button className="w-full bg-blue-600 hover:bg-blue-700 text-white mt-2">
                                        Pilih Online
                                    </Button>
                                </CardContent>
                            </Card>

                            {/* Opsi 2: ONSITE */}
                            <Card 
                                onClick={() => handleSelectMode('onsite')}
                                className="group relative overflow-hidden border-2 border-transparent hover:border-purple-500 hover:shadow-xl transition-all cursor-pointer bg-white"
                            >
                                <div className="absolute top-0 left-0 w-full h-1 bg-purple-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left" />
                                <CardContent className="p-8 flex flex-col items-center text-center gap-6 h-full justify-between">
                                    <div className="w-20 h-20 bg-purple-50 rounded-full flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                        <MapPin className="w-10 h-10" />
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">
                                            Seminar Onsite (Luring)
                                        </h3>
                                        <p className="text-sm text-gray-500 leading-relaxed">
                                            Saya berangkat langsung ke lokasi pelaksanaan seminar. <br/>
                                            <span className="font-semibold text-purple-600">Wajib Request Surat Tugas ke HRD.</span>
                                        </p>
                                    </div>
                                    <Button className="w-full bg-purple-600 hover:bg-purple-700 text-white mt-2">
                                        Pilih Onsite
                                    </Button>
                                </CardContent>
                            </Card>

                        </div>
                    )}
                </div>
            </div>

            {/* --- MODAL DIALOG KONFIRMASI (POP-UP KEREN) --- */}
            <Dialog open={isConfirmOpen} onOpenChange={setIsConfirmOpen}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <div className={`mx-auto h-12 w-12 rounded-full flex items-center justify-center mb-4 ${selectedMode === 'online' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600'}`}>
                            {selectedMode === 'online' ? <Monitor className="h-6 w-6" /> : <MapPin className="h-6 w-6" />}
                        </div>
                        <DialogTitle className="text-center text-xl">
                            Konfirmasi Mode {selectedMode === 'online' ? 'Online' : 'Onsite'}
                        </DialogTitle>
                        <DialogDescription className="text-center pt-2">
                            {selectedMode === 'online' ? (
                                <span>
                                    Anda memilih mode <strong>Daring</strong>. Proses akan langsung <strong>diselesaikan</strong> tanpa penerbitan surat tugas.
                                </span>
                            ) : (
                                <span>
                                    Anda memilih mode <strong>Luring</strong>. Sistem akan mengirimkan permintaan ke <strong>HRD</strong> untuk menerbitkan Surat Tugas. Anda harus menunggu proses tersebut.
                                </span>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter className="sm:justify-center gap-3 mt-4">
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() => setIsConfirmOpen(false)}
                            className="flex-1"
                        >
                            Batal
                        </Button>
                        <Button
                            type="button"
                            onClick={confirmSelection}
                            disabled={processing}
                            className={`flex-1 text-white ${selectedMode === 'online' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700'}`}
                        >
                            {processing ? 'Memproses...' : 'Ya, Konfirmasi'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

        </AppLayout>
    );
}