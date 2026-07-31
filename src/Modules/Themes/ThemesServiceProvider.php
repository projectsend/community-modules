<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\Themes;

use Illuminate\Support\ServiceProvider;

/**
 * Community-exclusive theme options: "Gallery" for the public pages/client
 * portal, "Branded" for outgoing emails — registered into the host app's
 * own PublicThemeRegistry/EmailThemeRegistry singletons, ungated (no
 * capability requirement): this package's mere presence is the gate, the
 * same way Custom Assets works. Moved here from the private cloud-modules
 * package (2026-07-31) — Gallery/Branded were previously Cloud-exclusive,
 * gated behind Capability::PremiumThemes, but that classification was
 * wrong: this is exactly the kind of thing that belongs in the free/
 * self-hosted edition, not paywalled behind Cloud.
 *
 * One real behavior change from the cloud-modules version: "Branded"
 * there pulled its logo from the Cloud-exclusive Branding module's
 * uploaded logo (BrandingSetting). That module stays in cloud-modules —
 * this package must never depend on it — so here "Branded" always shows
 * the stock ProjectSend mark, same as the fallback the old version used
 * when no custom logo was set.
 *
 * PublicThemeRegistry/EmailThemeRegistry only exist inside the real host
 * app — never in this package's own isolated Testbench suite, which is
 * why the registrations are guarded by class_exists() (same reasoning as
 * CustomAssetsServiceProvider's host-integration contract).
 */
class ThemesServiceProvider extends ServiceProvider
{
    private const PUBLIC_THEME_REGISTRY = 'App\Modules\Platform\Theming\PublicThemeRegistry';

    private const EMAIL_THEME_REGISTRY = 'App\Modules\Platform\Theming\EmailThemeRegistry';

    public function boot(): void
    {
        if (class_exists(self::PUBLIC_THEME_REGISTRY)) {
            $this->app->make(self::PUBLIC_THEME_REGISTRY)->register('gallery', 'Gallery');
        }

        if (class_exists(self::EMAIL_THEME_REGISTRY)) {
            $this->app->make(self::EMAIL_THEME_REGISTRY)->register(
                'branded',
                'Branded',
                null,
                fn (): array => ['logo_url' => asset('apple-touch-icon.png')],
            );
        }

        // 'branded.css' lives in this package, not the host app — extend
        // the markdown-mail lookup path so ThemedMailChannel's Markdown
        // renderer finds it the same way it finds the host's own
        // default/minimal theme CSS. Must happen before the Markdown
        // singleton is first resolved (lazy, at actual send time), so
        // doing it here in boot() is early enough.
        $paths = config('mail.markdown.paths', []);
        $paths[] = __DIR__.'/../../../resources/views/vendor/mail';
        config(['mail.markdown.paths' => $paths]);
    }
}
