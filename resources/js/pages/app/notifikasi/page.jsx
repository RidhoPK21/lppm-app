import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import AppLayout from "@/layouts/app-layout";
import { Head, router } from "@inertiajs/react";
import { Bell } from "lucide-react";
import { useState } from "react";
import PropTypes from 'prop-types';

export default function NotificationPage({ notifications, filters }) {
    const [searchValue, setSearchValue] = useState(filters.search || '');
    const [filterValue, setFilterValue] = useState(filters.filter || 'semua');
    const [sortValue, setSortValue] = useState(filters.sort || 'terbaru');

    const handleSearch = () => {
        router.get('/notifikasi', {
            search: searchValue,
            filter: filterValue,
            sort: sortValue
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const handleFilterChange = (value) => {
        setFilterValue(value);
        router.get('/notifikasi', {
            search: searchValue,
            filter: value,
            sort: sortValue
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const handleSortChange = (value) => {
        setSortValue(value);
        router.get('/notifikasi', {
            search: searchValue,
            filter: filterValue,
            sort: value
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const handleNotificationClick = (notification) => {
        if (!notification.is_read) {
            router.post(`/notifikasi/${notification.id}/read`, {}, {
                preserveScroll: true
            });
        }
    };

    const getTypeColor = (type) => {
        if (type === "Info") return "text-blue-500";
        if (type === "Sukses") return "text-green-500";
        if (type === "Peringatan") return "text-yellow-500";
        if (type === "Error") return "text-red-500";
        return "text-gray-500";
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day} / ${month} / ${year}`;
    };

    return (
        <AppLayout>
            <Head title="Notifikasi" />

            <div className="flex flex-col gap-6 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        Notifikasi
                    </h1>
                    <p className="text-muted-foreground mt-1">
                        Pantau semua aktivitas dan pemberitahuan terbaru Anda di sini.
                    </p>
                </div>

                <div className="flex flex-col md:flex-row gap-3 w-full">
                    <div className="flex-1 flex gap-2">
                        <Input
                            placeholder="Cari notifikasi..."
                            className="bg-white dark:bg-sidebar"
                            value={searchValue}
                            onChange={(e) => setSearchValue(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                        />
                        <Button
                            variant="secondary"
                            className="bg-gray-100 dark:bg-muted hover:bg-gray-200"
                            onClick={handleSearch}
                        >
                            Cari
                        </Button>
                    </div>
                    <div className="flex gap-2">
                        <Select value={filterValue} onValueChange={handleFilterChange}>
                            <SelectTrigger className="w-[140px] bg-white dark:bg-sidebar">
                                <SelectValue placeholder="Filter" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="semua">Semua</SelectItem>
                                <SelectItem value="belum_dibaca">Belum Dibaca</SelectItem>
                                <SelectItem value="Info">Info</SelectItem>
                                <SelectItem value="Sukses">Sukses</SelectItem>
                                <SelectItem value="Peringatan">Peringatan</SelectItem>
                                <SelectItem value="Error">Error</SelectItem>
                                <SelectItem value="System">System</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={sortValue} onValueChange={handleSortChange}>
                            <SelectTrigger className="w-[140px] bg-white dark:bg-sidebar">
                                <SelectValue placeholder="Urutkan" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="terbaru">Terbaru</SelectItem>
                                <SelectItem value="terlama">Terlama</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div className="flex flex-col gap-3 w-full">
                    {notifications.map((item) => (
                        <Card
                            key={item.id}
                            className={`w-full p-4 flex flex-row items-center justify-between gap-4 hover:bg-accent/5 transition-colors cursor-pointer ${
                                !item.is_read
                                    ? "bg-muted/30 border-l-4 border-l-primary"
                                    : ""
                            }`}
                            onClick={() => handleNotificationClick(item)}
                        >
                            <div className="flex items-center gap-4 min-w-0 flex-1 text-left">
                                <div className="shrink-0">
                                    <div className="h-10 w-10 rounded-full bg-black flex items-center justify-center text-white dark:bg-white dark:text-black">
                                        <Bell className="h-5 w-5" />
                                    </div>
                                </div>

                                <div className="flex flex-col min-w-0">
                                    <h3 className="font-semibold text-base truncate">
                                        {item.title}
                                    </h3>
                                    <p className="text-sm text-muted-foreground truncate">
                                        {item.message}
                                    </p>
                                </div>
                            </div>

                            <div className="text-right shrink-0">
                                <p className={`text-xs font-medium ${getTypeColor(item.type)}`}>
                                    {item.type}
                                </p>
                                <p className="text-xs text-muted-foreground mt-1">
                                    {formatDate(item.created_at)}
                                </p>
                            </div>
                        </Card>
                    ))}

                    {notifications.length === 0 && (
                        <div className="text-center py-10 text-muted-foreground bg-muted/10 rounded-lg border border-dashed w-full">
                            Tidak ada notifikasi baru.
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

NotificationPage.propTypes = {
    notifications: PropTypes.arrayOf(
        PropTypes.shape({
            id: PropTypes.number.isRequired,
            user_id: PropTypes.string.isRequired,
            title: PropTypes.string.isRequired,
            message: PropTypes.string.isRequired,
            type: PropTypes.oneOf(['Info', 'Sukses', 'Peringatan', 'Error', 'System']).isRequired,
            is_read: PropTypes.bool.isRequired,
            created_at: PropTypes.string.isRequired
        })
    ).isRequired,
    filters: PropTypes.shape({
        search: PropTypes.string,
        filter: PropTypes.string,
        sort: PropTypes.string
    })
};

NotificationPage.defaultProps = {
    filters: {
        search: '',
        filter: 'semua',
        sort: 'terbaru'
    }
};