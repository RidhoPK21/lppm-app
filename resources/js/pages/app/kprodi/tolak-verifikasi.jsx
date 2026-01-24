import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { AlertCircle } from 'lucide-react'; // ArrowLeft dihapus
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { route } from 'ziggy-js';

export default function TolakVerifikasi({ id }) {
    const { data, setData, post, processing, errors } = useForm({
        alasan_penolakan: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('kprodi.verifikasi.store-tolak', id));
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/kprodi/dashboard' },
            { title: 'Daftar Pengajuan', href: '/kprodi/verifikasi' },
            { title: 'Detail', href: `/kprodi/verifikasi/${id}` },
            { title: 'Tolak', href: '#' }
        ]}>
            <Head title="Tolak Verifikasi" />

            <div className="w-full max-w-3xl mx-auto p-6 space-y-8">
                
                {/* Header: Hanya Judul Besar (Tombol Kembali Dihapus) */}
                <div className="flex items-center gap-4">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">
                        Catatan Penolakan Kepala Prodi
                    </h1>
                </div>

                <Card className="border shadow-sm">
                    <form onSubmit={submit}>
                        <CardContent className="space-y-6 pt-6">
                            
                            {/* Alert Info */}
                            <Alert variant="destructive" className="bg-red-50 text-red-900 border-red-200">
                                <AlertCircle className="h-4 w-4" />
                                <AlertTitle>Perhatian</AlertTitle>
                                <AlertDescription>
                                    Anda wajib memberikan alasan penolakan yang jelas agar dosen dapat melakukan revisi.
                                </AlertDescription>
                            </Alert>

                            {/* Input Area */}
                            <div className="space-y-3">
                                <Label htmlFor="alasan" className="text-base font-semibold">
                                    Alasan Penolakan <span className="text-red-500">*</span>
                                </Label>
                                <Textarea
                                    id="alasan"
                                    placeholder="Tuliskan alasan penolakan di sini..."
                                    className="min-h-[200px] text-base resize-y"
                                    value={data.alasan_penolakan}
                                    onChange={(e) => setData('alasan_penolakan', e.target.value)}
                                    required
                                />
                                {errors.alasan_penolakan && (
                                    <p className="text-sm text-red-500">{errors.alasan_penolakan}</p>
                                )}
                            </div>
                        </CardContent>

                        <CardFooter className="flex justify-end gap-3 border-t bg-gray-50/50 p-6">
                            {/* Tombol Batal tetap ada sebagai opsi cancel form */}
                            <Button 
                                type="button" 
                                variant="outline" 
                                asChild
                                disabled={processing}
                                className="h-10 px-6"
                            >
                                <Link href={route('kprodi.verifikasi.detail', id)}>Batal</Link>
                            </Button>
                            
                            <Button 
                                type="submit" 
                                variant="destructive" 
                                disabled={processing || !data.alasan_penolakan}
                                className="h-10 px-6 bg-red-600 hover:bg-red-700"
                            >
                                {processing ? 'Menyimpan...' : 'Kirim Penolakan'}
                            </Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}