import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import AppLayout from "@/layouts/app-layout";
import { Head, Link } from "@inertiajs/react";
import { ArrowLeft, Download, MailOpen } from "lucide-react";
import { route } from "ziggy-js";

export default function InvitePage({ id }) {
    return (
        <AppLayout>
            <Head title="Undangan Penghargaan" />
            <div className="max-w-3xl mx-auto p-6 space-y-6">
                <Link
                    href={route("regis-semi.detail", id)}
                    className="flex items-center text-muted-foreground hover:text-foreground transition-colors w-fit"
                >
                    <ArrowLeft className="h-4 w-4 mr-2" /> Kembali
                </Link>

                <div className="text-center space-y-2 py-4">
                    <div className="mx-auto bg-primary/10 w-16 h-16 flex items-center justify-center rounded-full mb-4 text-primary">
                        <MailOpen className="h-8 w-8" />
                    </div>
                    <h1 className="text-3xl font-bold">Surat Undangan</h1>
                    <p className="text-muted-foreground">
                        Undangan resmi penerimaan penghargaan.
                    </p>
                </div>

                <Card className="p-8 border-2 border-dashed bg-muted/20 min-h-[400px] flex flex-col items-center justify-center text-center space-y-4">
                    {/* Area Preview PDF Dummy */}
                    <div className="text-muted-foreground">
                        <p className="font-medium">Preview Dokumen</p>
                        <p className="text-sm">Undangan_Penghargaan_2025.pdf</p>
                    </div>
                    <Button variant="outline" className="mt-4 gap-2">
                        <Download className="h-4 w-4" />
                        Download PDF
                    </Button>
                </Card>
            </div>
        </AppLayout>
    );
}
