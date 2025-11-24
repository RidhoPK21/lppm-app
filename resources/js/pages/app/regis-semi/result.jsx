import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import AppLayout from "@/layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import { ArrowLeft, Medal, Share2 } from "lucide-react";
import { route } from "ziggy-js";

export default function ResultPage({ id }) {
    return (
        <AppLayout>
            <Head title="Sertifikat Penghargaan" />
            <div className="max-w-3xl mx-auto p-6 space-y-6">
                <Link
                    href={route("regis-semi.detail", id)}
                    className="flex items-center text-muted-foreground hover:text-foreground transition-colors w-fit"
                >
                    <ArrowLeft className="h-4 w-4 mr-2" /> Kembali
                </Link>

                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            Sertifikat & Hasil
                        </h1>
                        <p className="text-muted-foreground">
                            Dokumen bukti penghargaan.
                        </p>
                    </div>
                    <Button variant="secondary" size="icon">
                        <Share2 className="h-4 w-4" />
                    </Button>
                </div>

                <Card className="overflow-hidden border-4 border-double border-primary/20">
                    {/* Dummy Sertifikat Visual */}
                    <div className="bg-white p-12 text-center space-y-6 text-black min-h-[500px] flex flex-col items-center justify-center">
                        <Medal className="h-24 w-24 text-yellow-500 mx-auto" />
                        <div className="space-y-2">
                            <h2 className="text-4xl font-serif font-bold text-primary">
                                Sertifikat Penghargaan
                            </h2>
                            <p className="text-lg text-gray-600">
                                Diberikan Kepada
                            </p>
                            <h3 className="text-2xl font-bold underline decoration-yellow-500 underline-offset-4">
                                Ridho PK
                            </h3>
                        </div>
                        <p className="max-w-md mx-auto text-gray-600">
                            Atas pencapaian luar biasa sebagai Juara 1 dalam
                            kompetisi Inovasi Teknologi Pendidikan Tahun 2025.
                        </p>

                        <div className="pt-12 flex gap-4">
                            <div className="border-t border-black w-32 pt-2">
                                <p className="text-sm font-bold">Rektor</p>
                            </div>
                            <div className="border-t border-black w-32 pt-2">
                                <p className="text-sm font-bold">Ketua LPPM</p>
                            </div>
                        </div>
                    </div>
                </Card>

                <div className="flex justify-end">
                    <Button className="gap-2">
                        <DownloadIcon /> Download Sertifikat Asli
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}

function DownloadIcon() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
        >
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" x2="12" y1="15" y2="3" />
        </svg>
    );
}
