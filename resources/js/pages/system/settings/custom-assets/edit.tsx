import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import AppLayout from '@/layouts/app-layout';

import { CustomAssetFields, type Option } from './custom-asset-fields';

interface AssetDetail {
    id: number;
    title: string;
    language: string;
    content: string;
    surfaces: string[];
    position: string;
    enabled: boolean;
}

interface CustomAssetsEditProps {
    asset: AssetDetail;
    languages: Option[];
    surfaces: Option[];
    positions: Option[];
}

export default function CustomAssetsEdit({ asset, languages, surfaces, positions }: CustomAssetsEditProps) {
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Settings'), href: '/system/settings' },
        { title: t('Custom assets'), href: '/system/settings/custom-assets' },
        { title: asset.title, href: `/system/settings/custom-assets/${asset.id}` },
    ];

    const { data, setData, patch, processing, recentlySuccessful, errors } = useForm({
        title: asset.title,
        language: asset.language,
        content: asset.content,
        surfaces: asset.surfaces,
        position: asset.position,
        enabled: asset.enabled,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('custom-assets.update', asset.id));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={asset.title} />

            <div className="px-4 py-6">
                <Heading title={asset.title} />

                <form onSubmit={submit} className="mt-6 max-w-xl space-y-6">
                    <CustomAssetFields
                        data={data}
                        setData={setData}
                        errors={errors}
                        languages={languages}
                        surfaces={surfaces}
                        positions={positions}
                        languageLocked
                    />

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {t('Save')}
                        </Button>
                        {recentlySuccessful && <p className="text-sm text-neutral-600">{t('Saved')}</p>}
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
