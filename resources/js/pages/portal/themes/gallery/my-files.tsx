import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Archive, ChevronRight, Download, File as FileIcon, Folder as FolderIcon, Upload, X } from 'lucide-react';
import { useEffect, useState } from 'react';

import { FilePreviewDialog } from '@/components/file-preview-dialog';
import Heading from '@/components/heading';
import { FilterField, ListToolbar } from '@/components/list-toolbar';
import { Pagination, PaginationMeta } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ZipDownloadDialog } from '@/components/zip-download-dialog';
import { ALL, useListQuery } from '@/hooks/use-list-query';
import { useTranslation } from '@/hooks/use-translation';
import { useZipDownload } from '@/hooks/use-zip-download';
import { categoryColor } from '@/lib/category-colors';
import { formatBytes } from '@/lib/format-bytes';
import { isThumbnailable } from '@/lib/thumbnails';

import PortalLayoutGallery from '../../../../layouts/portal-layout-gallery';

interface Crumb {
    id: number;
    name: string;
}

interface CategoryTag {
    id: number;
    name: string;
    color: string;
}

interface FileRow {
    id: number;
    name: string;
    description: string | null;
    original_name: string;
    mime_type: string;
    size: number;
    created_at: string | null;
    is_mine: boolean;
    categories: CategoryTag[];
}

interface MyFilesProps {
    folder: Crumb | null;
    breadcrumb: Crumb[];
    folders: Crumb[];
    files: FileRow[];
    pagination: PaginationMeta;
    search: string;
    searching: boolean;
    category: number | null;
    categories: CategoryTag[];
    owner: 'mine' | 'shared' | null;
    sort: 'name' | 'size' | 'date';
    direction: 'asc' | 'desc';
    can_upload: boolean;
}

const SORT_OPTIONS = [
    ['date-desc', 'Newest first'],
    ['date-asc', 'Oldest first'],
    ['name-asc', 'Name (A–Z)'],
    ['name-desc', 'Name (Z–A)'],
    ['size-desc', 'Largest first'],
    ['size-asc', 'Smallest first'],
] as const;

export default function MyFilesGallery({
    folder,
    breadcrumb,
    folders,
    files,
    pagination,
    search,
    searching,
    category,
    categories,
    owner,
    sort,
    direction,
    can_upload,
}: MyFilesProps) {
    const { t } = useTranslation();
    const { locale } = usePage<SharedData>().props;

    const zip = useZipDownload();
    const [selectedFileIds, setSelectedFileIds] = useState<Set<number>>(new Set());
    const [selectedFolderIds, setSelectedFolderIds] = useState<Set<number>>(new Set());
    const selectionCount = selectedFileIds.size + selectedFolderIds.size;

    // A new folder/search/filter/sort context (or a different page) means a
    // different set of rows on screen — stale selections would silently zip
    // items no longer visible.
    useEffect(() => {
        setSelectedFileIds(new Set());
        setSelectedFolderIds(new Set());
    }, [folder?.id, search, category, owner, sort, direction, pagination.page]);

    const toggleFile = (id: number) =>
        setSelectedFileIds((current) => {
            const next = new Set(current);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    const toggleFolder = (id: number) =>
        setSelectedFolderIds((current) => {
            const next = new Set(current);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    const clearSelection = () => {
        setSelectedFileIds(new Set());
        setSelectedFolderIds(new Set());
    };
    const downloadSelectionAsZip = () => zip.start({ file_ids: [...selectedFileIds], folder_ids: [...selectedFolderIds] });

    const { values, set, setMany, reset } = useListQuery(
        'my-files.index',
        { search, category: category === null ? ALL : String(category), owner: owner ?? ALL, sort, direction },
        { search: '', category: ALL, owner: ALL, sort: 'date', direction: 'desc' },
    );
    const hasFilters = values.search !== '' || values.category !== ALL || values.owner !== ALL;

    const folderUrl = (id: number | null) => (id === null ? route('my-files.index') : route('my-files.index', { folder: id }));
    const formatDate = (iso: string | null) => (iso === null ? '' : new Date(iso).toLocaleDateString(locale, { dateStyle: 'medium' }));

    return (
        <PortalLayoutGallery>
            <Head title={t('My files')} />

            <div>
                <div className="flex items-start justify-between">
                    <Heading title={folder?.name ?? t('My files')} description={t('The files shared with you')} />
                    <div className="flex items-center gap-2">
                        {can_upload && (
                            <Button asChild>
                                <Link href={route('my-files.upload.create')}>
                                    <Upload className="size-4" />
                                    {t('Upload a file')}
                                </Link>
                            </Button>
                        )}
                        {folder !== null && !searching && (
                            <Button variant="outline" onClick={() => zip.start({ folder_ids: [folder.id] })}>
                                <Archive className="size-4" />
                                {t('Download as zip')}
                            </Button>
                        )}
                    </div>
                </div>

                <div className="max-w-3xl">
                    <ListToolbar showClear={hasFilters} onClear={reset}>
                        <FilterField label={t('Search')} htmlFor="my-files-search">
                            <Input
                                id="my-files-search"
                                type="search"
                                placeholder={t('Search your files')}
                                className="w-56"
                                value={values.search}
                                onChange={(e) => set('search', e.target.value, true)}
                            />
                        </FilterField>
                        <FilterField label={t('Category')} htmlFor="my-files-category">
                            <Select value={values.category} onValueChange={(v) => set('category', v)}>
                                <SelectTrigger id="my-files-category" className="w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>{t('All categories')}</SelectItem>
                                    {categories.map((c) => (
                                        <SelectItem key={c.id} value={String(c.id)}>
                                            <span className="flex items-center gap-2">
                                                <span className={`size-2 shrink-0 rounded-full ${categoryColor(c.color).swatch}`} />
                                                {c.name}
                                            </span>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FilterField>
                        <FilterField label={t('Owner')} htmlFor="my-files-owner">
                            <Select value={values.owner} onValueChange={(v) => set('owner', v)}>
                                <SelectTrigger id="my-files-owner" className="w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL}>{t('Everyone')}</SelectItem>
                                    <SelectItem value="mine">{t('Uploaded by me')}</SelectItem>
                                    <SelectItem value="shared">{t('Shared with me')}</SelectItem>
                                </SelectContent>
                            </Select>
                        </FilterField>
                        <FilterField label={t('Sort by')} htmlFor="my-files-sort">
                            <Select
                                value={`${values.sort}-${values.direction}`}
                                onValueChange={(v) => {
                                    const [field, dir] = v.split('-');
                                    setMany({ sort: field, direction: dir });
                                }}
                            >
                                <SelectTrigger id="my-files-sort" className="w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {SORT_OPTIONS.map(([value, label]) => (
                                        <SelectItem key={value} value={value}>
                                            {t(label)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </FilterField>
                    </ListToolbar>
                </div>

                {searching ? (
                    <p className="text-muted-foreground mb-4 text-sm">{t('Showing matches from everything shared with you')}</p>
                ) : (
                    <nav className="text-muted-foreground mb-4 flex flex-wrap items-center gap-1 text-sm">
                        <Link href={folderUrl(null)} className="hover:text-foreground">
                            {t('My files')}
                        </Link>
                        {breadcrumb.map((crumb) => (
                            <span key={crumb.id} className="flex items-center gap-1">
                                <ChevronRight className="size-3.5" />
                                <Link href={folderUrl(crumb.id)} className="hover:text-foreground">
                                    {crumb.name}
                                </Link>
                            </span>
                        ))}
                    </nav>
                )}

                {selectionCount > 0 && (
                    <div className="bg-muted/40 mb-4 flex max-w-3xl items-center justify-between gap-3 rounded-lg border px-4 py-2">
                        <p className="text-sm font-medium">{t(':count selected', { count: selectionCount })}</p>
                        <div className="flex items-center gap-2">
                            <Button size="sm" onClick={downloadSelectionAsZip}>
                                <Archive className="size-4" />
                                {t('Download as zip')}
                            </Button>
                            <Button variant="ghost" size="sm" onClick={clearSelection}>
                                <X className="size-4" />
                                {t('Clear')}
                            </Button>
                        </div>
                    </div>
                )}

                {folders.length === 0 && files.length === 0 && (
                    <p className="text-muted-foreground rounded-lg border px-4 py-10 text-center text-sm">
                        {searching ? t('No files or folders match your search.') : t('No files have been shared with you yet.')}
                    </p>
                )}

                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                    {folders.map((row) => (
                        <div
                            key={`folder-${row.id}`}
                            className="hover:border-violet-400 hover:shadow-md relative flex flex-col items-center justify-center gap-2 rounded-xl border p-6 text-center transition"
                        >
                            <Checkbox
                                checked={selectedFolderIds.has(row.id)}
                                onCheckedChange={() => toggleFolder(row.id)}
                                aria-label={t('Select :name', { name: row.name })}
                                className="absolute top-3 left-3"
                            />
                            <Link href={folderUrl(row.id)} className="flex w-full flex-col items-center gap-2">
                                <FolderIcon className="text-violet-600 size-10 shrink-0" strokeWidth={1.5} />
                                <p className="w-full truncate text-sm font-medium">{row.name}</p>
                            </Link>
                        </div>
                    ))}

                    {files.map((file) => (
                        <div
                            key={`file-${file.id}`}
                            className="group hover:border-violet-400 hover:shadow-lg relative overflow-hidden rounded-xl border transition"
                        >
                            <Checkbox
                                checked={selectedFileIds.has(file.id)}
                                onCheckedChange={() => toggleFile(file.id)}
                                aria-label={t('Select :name', { name: file.name })}
                                className="bg-background/80 absolute top-3 left-3 z-10"
                            />

                            {isThumbnailable(file.mime_type) ? (
                                <FilePreviewDialog
                                    fileId={file.id}
                                    fileName={file.original_name}
                                    className="bg-muted block aspect-square overflow-hidden"
                                >
                                    <img
                                        src={route('files.thumbnail', file.id)}
                                        alt=""
                                        className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                    />
                                </FilePreviewDialog>
                            ) : (
                                <div className="bg-muted flex aspect-square items-center justify-center">
                                    <FileIcon className="text-muted-foreground size-10" strokeWidth={1.25} />
                                </div>
                            )}

                            <div className="flex items-center justify-between gap-2 p-3">
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-medium">{file.name}</p>
                                    <p className="text-muted-foreground truncate text-xs">
                                        {formatBytes(file.size)} · {formatDate(file.created_at)}
                                    </p>
                                    {file.categories.length > 0 && (
                                        <div className="mt-1 flex flex-wrap gap-1">
                                            {file.categories.map((category) => (
                                                <Badge
                                                    key={category.id}
                                                    variant="outline"
                                                    className={`text-[11px] font-normal ${categoryColor(category.color).badge}`}
                                                >
                                                    {category.name}
                                                </Badge>
                                            ))}
                                        </div>
                                    )}
                                </div>
                                <Button variant="ghost" size="sm" asChild>
                                    <a href={route('files.download', file.id)}>
                                        <Download className="size-4" />
                                        <span className="sr-only">{t('Download')}</span>
                                    </a>
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="mt-6">
                    <Pagination meta={pagination} />
                </div>
            </div>
            <ZipDownloadDialog status={zip.status} error={zip.error} onClose={zip.close} />
        </PortalLayoutGallery>
    );
}
