import React from 'react';
import { Check } from 'lucide-react';
import { cn } from '@/lib/utils';

export default function SeminarStepper({ currentStep }) {
    const steps = [
        { id: 1, label: "Submit Data" },
        { id: 2, label: "Submit Paper" },
        { id: 3, label: "Finalisasi Paper" },
        { id: 4, label: "Submit Artefak" },
        { id: 5, label: "Pencairan Dana" },
        { id: 6, label: "Surat Izin Kerja" },
    ];

    // Hitung persentase width garis hitam
    const progressWidth = Math.min(((currentStep - 1) / (steps.length - 1)) * 100, 100);

    return (
        <div className="w-full py-8">
            <div className="relative flex justify-between items-center w-full max-w-5xl mx-auto">
                
                {/* ============================================ */}
                {/* GARIS PROGRESS BAR (BACKGROUND & FOREGROUND) */}
                {/* ============================================ */}
                
                {/* 1. Garis Abu-abu (Background) - Full Width */}
                <div className="absolute top-1/2 left-0 w-full h-2 bg-gray-200 -translate-y-1/2 z-0 rounded-full" />

                {/* 2. Garis Hitam (Active) - Dynamic Width */}
                <div 
                    className="absolute top-1/2 left-0 h-2 bg-black -translate-y-1/2 z-0 transition-all duration-700 ease-in-out rounded-full"
                    style={{ width: `${progressWidth}%` }} 
                />

                {/* ============================================ */}
                {/* LINGKARAN / STEPS */}
                {/* ============================================ */}
                {steps.map((step) => {
                    // Logika Status
                    const isCompleted = step.id < currentStep; // Sudah lewat
                    const isCurrent = step.id === currentStep; // Sedang aktif
                    
                    // HAPUS baris isPending karena tidak dipakai
                    // const isPending = step.id > currentStep; 

                    return (
                        <div key={step.id} className="relative z-10 flex flex-col items-center">
                            {/* Lingkaran */}
                            <div
                                className={cn(
                                    "flex items-center justify-center w-10 h-10 rounded-full border-[3px] transition-all duration-300 bg-white",
                                    // Style jika Completed atau Current: Border Hitam
                                    (isCompleted || isCurrent) ? "border-black" : "border-gray-300",
                                    // Style khusus Current: Mungkin bisa diperbesar sedikit atau beda warna ikon
                                    isCurrent && "scale-110 shadow-lg" 
                                )}
                            >
                                {/* Icon di dalam Lingkaran */}
                                {isCompleted ? (
                                    <Check className="w-6 h-6 text-black stroke-[3]" />
                                ) : isCurrent ? (
                                    <Check className="w-6 h-6 text-black stroke-[3]" />
                                ) : (
                                    <Check className="w-6 h-6 text-gray-300" />
                                )}
                            </div>

                            {/* Label Text (Di Bawah) */}
                            <div className="absolute top-12 w-32 text-center">
                                <span className={cn(
                                    "text-xs font-bold leading-tight block",
                                    (isCompleted || isCurrent) ? "text-black" : "text-gray-400"
                                )}>
                                    {step.label}
                                </span>
                            </div>
                        </div>
                    );
                })}
            </div>
            
            {/* Spacer bawah agar label tidak terpotong */}
            <div className="h-10" /> 
        </div>
    );
}