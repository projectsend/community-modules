import { Head, Link } from '@inertiajs/react';
import { Download, File as FileIcon } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { formatBytes } from '@/lib/format-bytes';
import PublicLayoutGallery from '../../../../layouts/public-layout-gallery';

interface PublicFile {
    name: string;
    size: number;
    url: string;
    download_url: string;
    thumbnail_url: string | null;
}

interface PublicGroupShowProps {
    group: {
        name: string;
        description: string | null;
    };
    files: PublicFile[];
}

export default function PublicGroupShowGallery({ group, files }: PublicGroupShowProps) {
    const { t } = useTranslation();

    return (
        <PublicLayoutGallery title={group.name} description={group.description ?? undefined}>
            <Head title={group.name} />

            {files.length === 0 ? (
                <p className="text-muted-foreground text-center text-sm">{t('No files here yet.')}</p>
            ) : (
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    {files.map((file) => (
                        <div key={file.url} className="group hover:border-violet-400 hover:shadow-lg overflow-hidden rounded-xl border transition">
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
            )}
        </PublicLayoutGallery>
    );
}
