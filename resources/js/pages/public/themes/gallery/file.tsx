import { Head } from '@inertiajs/react';
import { Download, File as FileIcon } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { formatBytes } from '@/lib/format-bytes';
import { isThumbnailable } from '@/lib/thumbnails';
import PublicLayoutGallery from '../../../../layouts/public-layout-gallery';

interface PublicFileShowProps {
    file: {
        name: string;
        description: string | null;
        original_name: string;
        size: number;
        mime_type: string;
    };
    thumbnail_url: string | null;
    download_url: string;
}

export default function PublicFileShowGallery({ file, thumbnail_url, download_url }: PublicFileShowProps) {
    const { t } = useTranslation();

    return (
        <PublicLayoutGallery title={file.name} description={`${file.original_name} · ${formatBytes(file.size)}`}>
            <Head title={file.name} />

            <div className="mx-auto flex max-w-2xl flex-col items-center gap-6">
                <div className="bg-muted shadow-lg flex w-full items-center justify-center overflow-hidden rounded-xl border">
                    {thumbnail_url && isThumbnailable(file.mime_type) ? (
                        <img src={thumbnail_url} alt="" className="max-h-[32rem] w-full object-contain" />
                    ) : (
                        <FileIcon className="text-muted-foreground m-24 size-20" strokeWidth={1.25} />
                    )}
                </div>

                {file.description && <p className="text-muted-foreground text-center text-sm">{file.description}</p>}

                <Button size="lg" asChild>
                    <a href={download_url}>
                        <Download className="size-4" />
                        {t('Download')}
                    </a>
                </Button>
            </div>
        </PublicLayoutGallery>
    );
}
