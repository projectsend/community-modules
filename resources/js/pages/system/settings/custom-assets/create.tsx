import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import AppLayout from '@/layouts/app-layout';

import { CustomAssetFields, type Option } from './custom-asset-fields';

interface CustomAssetsCreateProps {
    languages: Option[];
    surfaces: Option[];
    positions: Option[];
}

export default function CustomAssetsCreate({ languages, surfaces, positions }: CustomAssetsCreateProps) {
    const { t } = useTranslation();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('Settings'), href: '/system/settings' },
        { title: t('Custom assets'), href: '/system/settings/custom-assets' },
        { title: t('New asset'), href: '/system/settings/custom-assets/create' },
    ];

    const { data, setData, post, processing, errors } = useForm({
        title: '',
        language: languages[0]?.value ?? 'html',
        content: '',
        surfaces: [] as string[],
        position: positions[0]?.value ?? 'head',
        enabled: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('custom-assets.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('New asset')} />

            <div className="px-4 py-6">
                <Heading title={t('New asset')} description={t('Snippets only apply once enabled and saved.')} />

                <form onSubmit={submit} className="mt-6 max-w-xl space-y-6">
                    <CustomAssetFields
                        data={data}
                        setData={setData}
                        errors={errors}
                        languages={languages}
                        surfaces={surfaces}
                        positions={positions}
                        languageLocked={false}
                    />

                    <Button type="submit" disabled={processing}>
                        {t('Create asset')}
                    </Button>
                </form>
            </div>
        </AppLayout>
    );
}
