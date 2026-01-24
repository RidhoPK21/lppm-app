import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Plus, Trash2, Save } from 'lucide-react';
import { route } from 'ziggy-js';

export default function RegistrasiAwal() {
    // 1. Setup Form State (Kategori Luaran ditambahkan)
    const { data, setData, post, processing, errors } = useForm({
        judul_makalah: "", 
        // Input Logika Pedoman (Wajib)
        jumlah_penulis: "1", 
        tipe_penulis: "", // Wajib dipilih
        urutan_penulis: "1", 
        kategori_luaran: "", // <-- HARUS ADA DAN WAJIB DIISI
        
        // Array Penulis
        penulis: [
            { id: 1, nama: '' }, 
        ],
        
        // Kewajiban
        kewajiban: {
            internal: true,
            abdi: false
        },
        ketidaklengkapan: ""
    });

    // --- HANDLERS ---
    const handleChange = (field, value) => {
        setData(field, value);
    };

    const updatePenulis = (index, value) => {
        const newPenulis = [...data.penulis];
        newPenulis[index].nama = value;
        setData('penulis', newPenulis);
    };

    const addAuthor = () => {
        setData(prev => ({
            ...prev,
            penulis: [...prev.penulis, { id: Date.now(), nama: '' }],
            jumlah_penulis: (parseInt(prev.jumlah_penulis) || 0) + 1
        }));
    };

    const removeAuthor = (index) => {
        const newPenulis = [...data.penulis];
        newPenulis.splice(index, 1);
        setData(prev => ({
            ...prev,
            penulis: newPenulis,
            jumlah_penulis: (parseInt(prev.jumlah_penulis) > 1 ? parseInt(prev.jumlah_penulis) - 1 : 1).toString()
        }));
    };

    const handleCheckbox = (field, checked) => {
        setData('kewajiban', { 
            ...data.kewajiban, 
            [field]: checked 
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('dosen.seminar.store'));
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Beranda', href: '/dosen/home' },
            { title: 'Registrasi Seminar', href: '/dosen/seminar' }
        ]}>
            <Head title="Registrasi Pengajuan Dana" />

            <div className="w-full max-w-4xl mx-auto p-6 pt-10 pb-24">
                
                <h1 className="text-3xl font-bold tracking-tight mb-10 text-gray-900">
                    Registrasi Pengajuan Dana
                </h1>

                <form onSubmit={handleSubmit} className="space-y-6">
                    
                    {/* INPUT JUDUL MAKALAH (WAJIB) */}
                    <div className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-semibold text-gray-900">Judul Makalah <span className="text-red-500">*</span></Label>
                            <div className="md:col-span-3">
                                <Input 
                                    value={data.judul_makalah}
                                    onChange={(e) => handleChange('judul_makalah', e.target.value)}
                                    className="bg-white border-gray-300"
                                    placeholder="Masukkan judul makalah..."
                                />
                                {errors.judul_makalah && <p className="text-red-500 text-sm mt-1">{errors.judul_makalah}</p>}
                            </div>
                        </div>
                    </div>
                    
                    {/* 1. Metadata Penulis */}
                    <div className="space-y-4">
                        
                        {/* Kategori Luaran (WAJIB BARU) */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4 border-b pb-4">
                            <Label className="md:text-left font-semibold text-gray-900">Target Luaran <span className="text-red-500">*</span></Label>
                            <div className="md:col-span-3">
                                <Select 
                                    value={data.kategori_luaran} 
                                    onValueChange={(val) => handleChange('kategori_luaran', val)}
                                >
                                    <SelectTrigger className="bg-white border-gray-300">
                                        <SelectValue placeholder="Pilih Kategori Luaran (Untuk Pagu Dana)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Prosiding Nasional">Prosiding Nasional</SelectItem>
                                        <SelectItem value="Prosiding Internasional">Prosiding Internasional</SelectItem>
                                        <SelectItem value="Jurnal Nasional">Jurnal Nasional</SelectItem>
                                        <SelectItem value="Jurnal Nasional Terakreditasi">Jurnal Nasional Terakreditasi</SelectItem>
                                        <SelectItem value="Jurnal Internasional">Jurnal Internasional</SelectItem>
                                        <SelectItem value="Jurnal Internasional Terindeks">Jurnal Internasional Terindeks</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.kategori_luaran && <p className="text-red-500 text-sm mt-1">Kategori luaran wajib dipilih.</p>}
                            </div>
                        </div>

                        {/* Jumlah Penulis */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-semibold text-gray-900">Jumlah Penulis <span className="text-red-500">*</span></Label>
                            <div className="md:col-span-3">
                                <Input 
                                    type="number" 
                                    min="1"
                                    value={data.jumlah_penulis}
                                    onChange={(e) => handleChange('jumlah_penulis', e.target.value)}
                                    className="bg-white border-gray-300" 
                                />
                                {errors.jumlah_penulis && <p className="text-red-500 text-sm mt-1">Jumlah penulis wajib diisi.</p>}
                            </div>
                        </div>

                        {/* Tipe Penulis */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-semibold text-gray-900">Tipe Penulis <span className="text-red-500">*</span></Label>
                            <div className="md:col-span-3">
                                <Select 
                                    value={data.tipe_penulis} 
                                    onValueChange={(val) => handleChange('tipe_penulis', val)}
                                >
                                    <SelectTrigger className="bg-white border-gray-300">
                                        <SelectValue placeholder="Pilih Tipe Penulis" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Penulis Pertama">Penulis Pertama</SelectItem>
                                        <SelectItem value="Corresponding Author">Corresponding Author</SelectItem>
                                        <SelectItem value="Co-Author">Co-Author (Anggota)</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.tipe_penulis && <p className="text-red-500 text-sm mt-1">Tipe penulis wajib dipilih.</p>}
                            </div>
                        </div>

                        {/* Urutan Penulis */}
                        <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                            <Label className="md:text-left font-semibold text-gray-900">Urutan Penulis <span className="text-red-500">*</span></Label>
                            <div className="md:col-span-3">
                                <Input 
                                    type="number" 
                                    min="1"
                                    value={data.urutan_penulis}
                                    onChange={(e) => handleChange('urutan_penulis', e.target.value)}
                                    className="bg-white border-gray-300" 
                                />
                                {errors.urutan_penulis && <p className="text-red-500 text-sm mt-1">Urutan penulis wajib diisi.</p>}
                            </div>
                        </div>

                        {/* Dynamic Authors List */}
                        {data.penulis.map((author, index) => (
                            <div key={author.id} className="grid grid-cols-1 md:grid-cols-4 items-center gap-4">
                                <Label className="md:text-left font-semibold text-gray-900">
                                    Penulis {index + 1}
                                </Label>
                                <div className="md:col-span-3 flex gap-2">
                                    <Input 
                                        value={author.nama}
                                        onChange={(e) => updatePenulis(index, e.target.value)}
                                        className={`bg-white ${errors[`penulis.${index}.nama`] ? 'border-red-500' : 'border-gray-300'}`}
                                        placeholder={`Nama Penulis ${index + 1}`}
                                    />
                                    {data.penulis.length > 1 && (
                                        <Button 
                                            type="button" 
                                            variant="ghost" 
                                            size="icon"
                                            onClick={() => removeAuthor(index)}
                                            className="text-red-500 hover:text-red-700 hover:bg-red-50"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ))}

                        {/* Tombol Tambah Penulis */}
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="md:col-span-1"></div>
                            <div className="md:col-span-3 flex justify-end">
                                <Button 
                                    type="button" 
                                    variant="outline" 
                                    onClick={addAuthor}
                                    className="gap-2"
                                >
                                    <Plus className="w-4 h-4" /> Add
                                </Button>
                            </div>
                        </div>
                    </div>

                    {/* 2. Kewajiban Penelitian */}
                    <div className="space-y-3 pt-4">
                        <Label className="text-base font-semibold text-gray-900">Kewajiban Penelitian</Label>
                        
                        <div className="space-y-3 pl-1">
                            <div className="flex items-center space-x-3">
                                <Checkbox 
                                    id="internal" 
                                    className="data-[state=checked]:bg-black border-gray-400" 
                                    checked={data.kewajiban.internal}
                                    onCheckedChange={(checked) => handleCheckbox('internal', checked)}
                                />
                                <label htmlFor="internal" className="text-sm font-medium leading-none cursor-pointer text-gray-600">
                                    Sudah melakukan penelitian internal
                                </label>
                            </div>
                            <div className="flex items-center space-x-3">
                                <Checkbox 
                                    id="abdi" 
                                    className="data-[state=checked]:bg-black border-gray-400" 
                                    checked={data.kewajiban.abdi}
                                    onCheckedChange={(checked) => handleCheckbox('abdi', checked)}
                                />
                                <label htmlFor="abdi" className="text-sm font-medium leading-none cursor-pointer text-gray-600">
                                    Sudah mengabdi
                                </label>
                            </div>
                        </div>
                    </div>

                    {/* 3. Ketidaklengkapan */}
                    <div className="grid grid-cols-1 md:grid-cols-4 items-center gap-4 pt-4">
                        <Label className="md:text-left font-semibold text-gray-900">Ketidaklengkapan</Label>
                        <div className="md:col-span-3">
                            <Input 
                                value={data.ketidaklengkapan}
                                onChange={(e) => handleChange('ketidaklengkapan', e.target.value)}
                                className="bg-white border-gray-300" 
                            />
                        </div>
                    </div>

                    {/* Tombol Submit */}
                    <div className="flex justify-end pt-8 gap-4">
                        <Button asChild variant="outline" type="button">
                            <Link href={route('dosen.seminar.index')}>Batal</Link>
                        </Button>
                        
                        <Button 
                            type="submit" 
                            disabled={processing}
                            className="bg-black text-white hover:bg-gray-800 px-8 min-w-[120px]"
                        >
                            {processing ? (
                                'Menyimpan...'
                            ) : (
                                <><Save className="w-4 h-4 mr-2" /> Submit</>
                            )}
                        </Button>
                    </div>

                </form>
            </div>
        </AppLayout>
    );
}