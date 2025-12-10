// File: resources/js/Pages/app/notifikasi/ReviewNotificationCard.jsx
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { router } from "@inertiajs/react";
import { BookOpen, Calendar, User, Send, ExternalLink, X } from "lucide-react";
import { useState } from "react";
import PropTypes from 'prop-types';

export default function ReviewNotificationCard({ notification, bookDetails, onClose }) {
    const [reviewNote, setReviewNote] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmitReview = () => {
        if (!reviewNote.trim()) {
            alert('Mohon isi catatan review terlebih dahulu');
            return;
        }

        setIsSubmitting(true);

        // Extract book_id dari reference_key format: REVIEWER_INVITE_{book_id}_{reviewer_id}
        const bookId = notification.reference_key?.split('_')[2];

        router.post(`/review/submit/${bookId}`, {
            note: reviewNote,
            notification_id: notification.id,
            book_submission_id: bookDetails?.id
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setReviewNote('');
                setIsSubmitting(false);
                if (onClose) onClose();
            },
            onError: (errors) => {
                setIsSubmitting(false);
                alert('Gagal mengirim review. Silakan coba lagi.');
                console.error('Submit review error:', errors);
            }
        });
    };

    // Parse drive_link JSON
    const driveLinks = bookDetails?.drive_link ? JSON.parse(bookDetails.drive_link) : {};

    return (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <Card className="w-full max-w-3xl max-h-[90vh] overflow-y-auto border-4 border-black dark:border-white bg-white dark:bg-gray-900 shadow-2xl">
                <div className="p-6">
                    {/* Header with Close Button */}
                    <div className="flex items-start justify-between mb-6 border-b-2 border-black dark:border-white pb-4">
                        <div className="flex items-center gap-3 flex-1">
                            <div className="h-12 w-12 rounded-full bg-black dark:bg-white flex items-center justify-center shrink-0">
                                <BookOpen className="h-6 w-6 text-white dark:text-black" />
                            </div>
                            <div>
                                <h3 className="font-bold text-xl text-black dark:text-white">
                                    {notification.title}
                                </h3>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {notification.message}
                                </p>
                            </div>
                        </div>
                        <button
                            onClick={onClose}
                            className="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors ml-2"
                            aria-label="Tutup"
                        >
                            <X className="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </button>
                    </div>

                    {/* Book Details Section */}
                    {bookDetails && (
                        <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-5 mb-6 border-2 border-black dark:border-white">
                            <h4 className="font-bold text-lg mb-4 text-black dark:text-white">
                                Detail Buku
                            </h4>
                            
                            <div className="space-y-3">
                                <div className="flex items-start gap-3">
                                    <span className="text-sm font-semibold text-gray-700 dark:text-gray-300 min-w-[120px]">
                                        Judul:
                                    </span>
                                    <span className="text-sm font-bold text-black dark:text-white flex-1">
                                        {bookDetails.title}
                                    </span>
                                </div>

                                {bookDetails.isbn && (
                                    <div className="flex items-start gap-3">
                                        <span className="text-sm font-semibold text-gray-700 dark:text-gray-300 min-w-[120px]">
                                            ISBN:
                                        </span>
                                        <span className="text-sm text-black dark:text-white">
                                            {bookDetails.isbn}
                                        </span>
                                    </div>
                                )}

                                {bookDetails.publisher && (
                                    <div className="flex items-start gap-3">
                                        <span className="text-sm font-semibold text-gray-700 dark:text-gray-300 min-w-[120px]">
                                            Penerbit:
                                        </span>
                                        <span className="text-sm text-black dark:text-white">
                                            {bookDetails.publisher}
                                        </span>
                                    </div>
                                )}

                                {bookDetails.user_name && (
                                    <div className="flex items-center gap-3">
                                        <User className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                        <span className="text-sm text-black dark:text-white">
                                            Penulis: <span className="font-semibold">{bookDetails.user_name}</span>
                                        </span>
                                    </div>
                                )}

                                {bookDetails.created_at && (
                                    <div className="flex items-center gap-3">
                                        <Calendar className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                                        <span className="text-sm text-black dark:text-white">
                                            Diajukan: {new Date(bookDetails.created_at).toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric'
                                            })}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Drive Links Section */}
                            {Object.keys(driveLinks).length > 0 && (
                                <div className="mt-5 pt-4 border-t-2 border-gray-300 dark:border-gray-600">
                                    <h5 className="font-semibold text-sm mb-3 text-black dark:text-white">
                                        Dokumen Pendukung:
                                    </h5>
                                    <div className="space-y-2">
                                        {Object.entries(driveLinks).map(([key, url]) => {
                                            // Format key menjadi label yang lebih readable
                                            const label = key
                                                .replace(/_/g, ' ')
                                                .replace(/\b\w/g, l => l.toUpperCase());
                                            
                                            return (
                                                <a
                                                    key={key}
                                                    href={url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="flex items-center gap-2 p-3 bg-white dark:bg-gray-900 border-2 border-black dark:border-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group"
                                                >
                                                    <ExternalLink className="h-4 w-4 text-gray-600 dark:text-gray-400 group-hover:text-black dark:group-hover:text-white" />
                                                    <span className="text-sm font-medium text-black dark:text-white flex-1">
                                                        {label}
                                                    </span>
                                                    <span className="text-xs text-gray-500 dark:text-gray-400">
                                                        Buka Link
                                                    </span>
                                                </a>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Review Form Section */}
                    <div className="space-y-4">
                        <label className="block">
                            <span className="text-sm font-bold text-black dark:text-white mb-2 block">
                                Catatan Review <span className="text-red-600">*</span>
                            </span>
                            <textarea
                                placeholder="Tulis catatan review Anda di sini... (wajib diisi)"
                                value={reviewNote}
                                onChange={(e) => setReviewNote(e.target.value)}
                                className="w-full min-h-[140px] resize-none p-4 border-2 border-black dark:border-white rounded-lg bg-white dark:bg-gray-900 text-black dark:text-white placeholder:text-gray-500 focus:ring-4 focus:ring-black/20 dark:focus:ring-white/20 focus:border-black dark:focus:border-white disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                disabled={isSubmitting}
                            />
                            <span className="text-xs text-gray-600 dark:text-gray-400 mt-2 block">
                                Berikan penilaian dan saran Anda untuk buku ini
                            </span>
                        </label>

                        <Button
                            onClick={handleSubmitReview}
                            disabled={isSubmitting || !reviewNote.trim()}
                            className="w-full bg-black dark:bg-white text-white dark:text-black hover:bg-gray-800 dark:hover:bg-gray-200 font-semibold py-6 text-base disabled:opacity-50 disabled:cursor-not-allowed border-2 border-black dark:border-white transition-all"
                        >
                            <Send className="h-5 w-5 mr-2" />
                            {isSubmitting ? 'Mengirim Review...' : 'Kirim Review'}
                        </Button>
                    </div>

                    {/* Timestamp */}
                    <div className="mt-6 pt-4 border-t-2 border-gray-200 dark:border-gray-700">
                        <p className="text-xs text-gray-600 dark:text-gray-400">
                            Diundang pada: {new Date(notification.created_at).toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}
                        </p>
                    </div>
                </div>
            </Card>
        </div>
    );
}

ReviewNotificationCard.propTypes = {
    notification: PropTypes.shape({
        id: PropTypes.number.isRequired,
        title: PropTypes.string.isRequired,
        message: PropTypes.string.isRequired,
        type: PropTypes.string.isRequired,
        created_at: PropTypes.string.isRequired,
        reference_key: PropTypes.string
    }).isRequired,
    bookDetails: PropTypes.shape({
        id: PropTypes.number,
        title: PropTypes.string,
        isbn: PropTypes.string,
        publisher: PropTypes.string,
        user_name: PropTypes.string,
        created_at: PropTypes.string,
        drive_link: PropTypes.string
    }),
    onClose: PropTypes.func.isRequired
};