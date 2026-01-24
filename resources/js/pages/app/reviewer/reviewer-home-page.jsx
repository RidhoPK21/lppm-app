import AppLayout from "@/layouts/app-layout";
import { Head } from "@inertiajs/react";

export default function ReviewerHomePage({ pageName }) {
    return (
        <AppLayout pageName={pageName}>
            <Head title="Dashboard Reviewer" />

            <div className="space-y-6">
                <div className="border rounded-lg p-6 shadow-sm bg-white">
                    <h1 className="text-2xl font-semibold">
                        Hay, Reviewer 
                    </h1>

                    <p className="text-sm text-gray-500 mt-1">
                        Selamat datang di aplikasi ITDel Reviewer!
                    </p>
                </div>

                {/* <div className="grid grid-cols-2 gap-4">
                    <div className="bg-white p-6 rounded-xl shadow text-center">
                        <h2 className="font-semibold text-lg">Jurnal</h2>
                        <p className="text-sm text-gray-500">
                            Review jurnal yang masuk
                        </p>
                    </div>

                    <div className="bg-white p-6 rounded-xl shadow text-center">
                        <h2 className="font-semibold text-lg">Seminar</h2>
                        <p className="text-sm text-gray-500">
                            Review seminar yang masuk
                        </p>
                    </div>
                </div> */}
            </div>
        </AppLayout>
    );
}
