import { javascript } from '@codemirror/lang-javascript';
import { css as cssLang } from '@codemirror/lang-css';
import { html as htmlLang } from '@codemirror/lang-html';
import CodeMirror from '@uiw/react-codemirror';

import InputError from '@/components/input-error';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';

export interface Option {
    value: string;
    label: string;
}

export interface CustomAssetFormData {
    title: string;
    language: string;
    content: string;
    surfaces: string[];
    position: string;
    enabled: boolean;
}

interface CustomAssetFieldsProps {
    data: CustomAssetFormData;
    setData: <K extends keyof CustomAssetFormData>(key: K, value: CustomAssetFormData[K]) => void;
    errors: Partial<Record<keyof CustomAssetFormData, string>>;
    languages: Option[];
    surfaces: Option[];
    positions: Option[];
    /** Fixed once an asset already exists — the language a snippet is
     *  written in isn't something you change after the fact, only its
     *  content/targeting. */
    languageLocked: boolean;
}

function languageExtension(language: string) {
    switch (language) {
        case 'css':
            return cssLang();
        case 'js':
            return javascript();
        case 'html':
        default:
            return htmlLang();
    }
}

export function CustomAssetFields({ data, setData, errors, languages, surfaces, positions, languageLocked }: CustomAssetFieldsProps) {
    const { t } = useTranslation();

    const toggleSurface = (value: string, checked: boolean) => {
        setData('surfaces', checked ? [...data.surfaces, value] : data.surfaces.filter((surface) => surface !== value));
    };

    return (
        <div className="space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="title">{t('Title')}</Label>
                <Input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                <InputError message={errors.title} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="language">{t('Language')}</Label>
                <select
                    id="language"
                    className="border-input bg-background flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs disabled:cursor-not-allowed disabled:opacity-50"
                    value={data.language}
                    disabled={languageLocked}
                    onChange={(e) => setData('language', e.target.value)}
                >
                    {languages.map((language) => (
                        <option key={language.value} value={language.value}>
                            {t(language.label)}
                        </option>
                    ))}
                </select>
                <InputError message={errors.language} />
            </div>

            <div className="grid gap-2">
                <Label>{t('Content')}</Label>
                <div className="overflow-hidden rounded-md border">
                    <CodeMirror
                        value={data.content}
                        height="300px"
                        extensions={[languageExtension(data.language)]}
                        onChange={(value) => setData('content', value)}
                    />
                </div>
                <p className="text-muted-foreground text-sm">
                    {t('Do not add the wrapping <style>/<script> tags for CSS/JS — they are added automatically.')}
                </p>
                <InputError message={errors.content} />
            </div>

            <div className="grid gap-2">
                <Label>{t('Surfaces')}</Label>
                <div className="space-y-2">
                    {surfaces.map((surface) => (
                        <label key={surface.value} className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={data.surfaces.includes(surface.value)}
                                onCheckedChange={(checked) => toggleSurface(surface.value, checked === true)}
                            />
                            {t(surface.label)}
                        </label>
                    ))}
                </div>
                <InputError message={errors.surfaces} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="position">{t('Position')}</Label>
                <select
                    id="position"
                    className="border-input bg-background flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs"
                    value={data.position}
                    onChange={(e) => setData('position', e.target.value)}
                >
                    {positions.map((position) => (
                        <option key={position.value} value={position.value}>
                            {t(position.label)}
                        </option>
                    ))}
                </select>
                <InputError message={errors.position} />
            </div>

            <label className="flex items-center gap-2 text-sm">
                <Checkbox checked={data.enabled} onCheckedChange={(checked) => setData('enabled', checked === true)} />
                {t('Enabled')}
            </label>
        </div>
    );
}
