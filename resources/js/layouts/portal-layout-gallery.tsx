import AppLogoIcon from '@/components/app-logo-icon';
import { AppearanceSwitcher } from '@/components/appearance-switcher';
import { IconLocaleSwitcher } from '@/components/locale-switcher';
import { NotificationBell } from '@/components/notification-bell';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { useTranslation } from '@/hooks/use-translation';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';

interface PortalLayoutGalleryProps {
    children: React.ReactNode;
}

/**
 * Cloud-exclusive "Gallery" portal theme's own header — a violet gradient
 * banner matching the public Gallery theme's shell (public-layout-gallery.tsx
 * in this same package), instead of reusing core's plain PortalLayout topbar.
 * The point is brand continuity: a client should see the same "look" whether
 * they're on the guest public pages or their own portal, which the plain
 * white PortalLayout header didn't deliver for this theme.
 *
 * The notification/appearance/locale/user-menu controls sit on a light pill
 * rather than directly on the violet background — those are core-shared
 * components (NotificationBell, AppearanceSwitcher, IconLocaleSwitcher,
 * UserInfo) with their own hardcoded text colors tuned for a light surface,
 * so giving them a light backdrop keeps them legible without forking any of
 * them for a dark-background variant.
 */
export default function PortalLayoutGallery({ children }: PortalLayoutGalleryProps) {
    const { t } = useTranslation();
    const { auth, name, branding } = usePage<SharedData>().props;

    return (
        <div className="bg-background flex min-h-svh flex-col">
            <header className="from-violet-700 to-violet-900 flex items-center justify-between bg-gradient-to-r px-4 py-3 md:px-8">
                <div className="flex items-center gap-6">
                    <Link href={route('home')} className="flex items-center gap-2 font-medium">
                        {branding?.logo_url ? (
                            <img src={branding.logo_url} alt={name} className="h-7 w-auto object-contain brightness-0 invert" />
                        ) : (
                            <AppLogoIcon className="h-7 w-auto text-white" />
                        )}
                        <span className="text-sm font-semibold text-white">{name}</span>
                    </Link>
                    <nav className="flex items-center gap-4 text-sm">
                        <Link href={route('my-files.index')} className="text-white/80 hover:text-white">
                            {t('My files')}
                        </Link>
                        <Link href={route('my-groups.index')} className="text-white/80 hover:text-white">
                            {t('My groups')}
                        </Link>
                        {auth.permissions.includes('upload') && (
                            <Link href={route('my-files.upload.create')} className="text-white/80 hover:text-white">
                                {t('Upload')}
                            </Link>
                        )}
                    </nav>
                </div>

                <div className="flex items-center gap-1 rounded-full bg-white/95 py-1 pr-1 pl-3 shadow-sm">
                    <NotificationBell />
                    <AppearanceSwitcher />
                    <IconLocaleSwitcher />
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="flex items-center gap-2 rounded-full px-2">
                                <UserInfo user={auth.user} />
                                <ChevronDown className="text-muted-foreground size-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="min-w-56 rounded-lg">
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-6 py-10 md:px-10">{children}</main>
        </div>
    );
}
