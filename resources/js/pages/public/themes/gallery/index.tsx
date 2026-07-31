import { Head, Link } from '@inertiajs/react';
import { Download, File as FileIcon, Folder } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { formatBytes } from '@/lib/format-bytes';
import PublicLayoutGallery from '../../../../layouts/public-layout-gallery';

interface PublicGroup {
    name: string;
    url: string;
}

interface PublicFile {
    name: string;
    size: number;
    url: string;
    download_url: string;
    thumbnail_url: string | null;
}

interface PublicIndexProps {
    groups: PublicGroup[];
    files: PublicFile[];
}

export default function PublicIndexGallery({ groups, files }: PublicIndexProps) {
    const { t } = useTranslation();

    return (
        <PublicLayoutGallery title={t('Public files')} description={t('Browse publicly shared groups and files below.')}>
            <Head title={t('Public files')} />

            <div className="space-y-10">
                {groups.length > 0 && (
                    <div className="space-y-3">
                        <h2 className="text-muted-foreground text-sm font-medium">{t('Groups')}</h2>
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                            {groups.map((group) => (
                                <Link
                                    key={group.url}
                                    href={group.url}
                                    className="hover:border-violet-400 hover:shadow-md flex flex-col items-center gap-2 rounded-xl border p-6 text-center transition"
                                >
                                    <Folder className="text-violet-600 size-10 shrink-0" strokeWidth={1.5} />
                                    <p className="w-full truncate text-sm font-medium">{group.name}</p>
                                </Link>
                            ))}
                        </div>
                    </div>
                )}

                {files.length > 0 && (
                    <div className="space-y-3">
                        <h2 className="text-muted-foreground text-sm font-medium">{t('Files')}</h2>
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            {files.map((file) => (
                                <div
                                    key={file.url}
                                    className="group hover:border-violet-400 hover:shadow-lg overflow-hidden rounded-xl border transition"
                                >
                                    <Link href={file.url} className="bg-muted block aspect-square overflow-hidden">
                                        {file.thumbnail_url ? (
                                            <img
                                                src={file.thumbnail_url}
                                                alt=""
                                                className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                            />
                                        ) : (
                                            <div className="flex h-full items-center justify-center">
                                                <FileIcon className="text-muted-foreground size-10" strokeWidth={1.25} />
                                            </div>
                                        )}
                                    </Link>
                                    <div className="flex items-center justify-between gap-2 p-3">
                                        <Link href={file.url} className="min-w-0 hover:underline">
                                            <p className="truncate text-sm font-medium">{file.name}</p>
                                            <p className="text-muted-foreground text-xs">{formatBytes(file.size)}</p>
                                        </Link>
                                        <Button variant="ghost" size="sm" asChild>
                                            <a href={file.download_url}>
                                                <Download className="size-4" />
                                                <span className="sr-only">{t('Download')}</span>
                                            </a>
                                        </Button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {groups.length === 0 && files.length === 0 && (
                    <p className="text-muted-foreground text-center text-sm">{t('Nothing is publicly shared yet.')}</p>
                )}
            </div>
        </PublicLayoutGallery>
    );
}
