import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';

import { ConfirmDialog } from '@/components/confirm-dialog';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import AppLayout from '@/layouts/app-layout';

interface AssetRow {
    id: number;
    title: string;
    language: string;
    surfaces: string[];
    position: string;
    enabled: boolean;
    can_edit: boolean;
    can_delete: boolean;
}

interface CustomAssetsIndexProps {
    assets: AssetRow[];
}

export default function CustomAssetsIndex({ assets }: CustomAssetsIndexProps) {
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Settings'), href: '/system/settings' },
        { title: t('Custom assets'), href: '/system/settings/custom-assets' },
    ];

    const toggle = (asset: AssetRow) => {
        router.patch(route('custom-assets.toggle', asset.id), {}, { preserveScroll: true });
    };

    const destroy = (asset: AssetRow) => {
        router.delete(route('custom-assets.destroy', asset.id), { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Custom assets')} />

            <div className="px-4 py-6">
                <div className="flex items-center justify-between">
                    <Heading
                        title={t('Custom assets')}
                        description={t('Inject custom HTML, CSS, or JavaScript into the public, portal, or staff pages.')}
                    />
                    <Button asChild>
                        <Link href={route('custom-assets.create')}>{t('New asset')}</Link>
                    </Button>
                </div>

                <div className="mt-6 overflow-x-auto rounded-lg border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="bg-muted/50 border-b text-left">
                                <th className="px-4 py-2.5 font-medium">{t('Title')}</th>
                                <th className="px-4 py-2.5 font-medium">{t('Language')}</th>
                                <th className="px-4 py-2.5 font-medium">{t('Surfaces')}</th>
                                <th className="px-4 py-2.5 font-medium">{t('Position')}</th>
                                <th className="px-4 py-2.5 font-medium">{t('Status')}</th>
                                <th className="px-4 py-2.5" />
                            </tr>
                        </thead>
                        <tbody>
                            {assets.length === 0 && (
                                <tr>
                                    <td colSpan={6} className="text-muted-foreground px-4 py-6 text-center">
                                        {t('There are no assets yet.')}
                                    </td>
                                </tr>
                            )}
                            {assets.map((asset) => (
                                <tr key={asset.id} className="border-b last:border-0">
                                    <td className="px-4 py-2.5 font-medium">{asset.title}</td>
                                    <td className="px-4 py-2.5 uppercase">{asset.language}</td>
                                    <td className="px-4 py-2.5">{asset.surfaces.join(', ')}</td>
                                    <td className="px-4 py-2.5">{asset.position}</td>
                                    <td className="px-4 py-2.5">
                                        {asset.enabled ? (
                                            <Badge variant="secondary">{t('Enabled')}</Badge>
                                        ) : (
                                            <Badge variant="outline">{t('Disabled')}</Badge>
                                        )}
                                    </td>
                                    <td className="px-4 py-2.5 text-right">
                                        <div className="flex justify-end gap-2">
                                            {asset.can_edit && (
                                                <>
                                                    <Button variant="ghost" size="sm" onClick={() => toggle(asset)}>
                                                        {asset.enabled ? t('Disable') : t('Enable')}
                                                    </Button>
                                                    <Button variant="outline" size="sm" asChild>
                                                        <Link href={route('custom-assets.edit', asset.id)}>{t('Edit')}</Link>
                                                    </Button>
                                                </>
                                            )}
                                            {asset.can_delete && (
                                                <ConfirmDialog
                                                    trigger={
                                                        <Button variant="outline" size="sm">
                                                            {t('Delete')}
                                                        </Button>
                                                    }
                                                    title={t('Delete this asset?')}
                                                    description={t('This cannot be undone.')}
                                                    confirmLabel={t('Delete')}
                                                    onConfirm={() => destroy(asset)}
                                                />
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
