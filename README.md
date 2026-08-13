# projectsend/community-modules

Community-exclusive feature modules for ProjectSend — the mirror image of
the private `projectsend/cloud-modules` package. That package holds premium
code that can't leak into the open-source community edition; this one holds
code that must never ship in the hosted Cloud edition. A module belongs
here when it's judged too risky or irrelevant for a hosted, multi-tenant
deployment to have present at all, not merely worth capability-gating off.

Public and free software, unlike its Cloud counterpart: the reason this
code lives outside the core repository is operational risk on a hosted,
multi-tenant platform, not secrecy. A self-hoster is welcome to all of it.

Structured exactly like `cloud-modules`: one `<Name>ServiceProvider` per
module under `src/Modules/`, registered from `CommunityModulesServiceProvider`.
Built and tested fully standalone via Orchestra Testbench — there is no real
host application to install this into yet (the open-source Community v2 app
doesn't exist as a running repo). `tests/TestCase.php` and `tests/Support/*`
fake the concerns a real host would provide.

## Modules

### CustomAssets

Lets staff inject arbitrary HTML/CSS/JS snippets into page `<head>`,
top-of-`<body>`, or before `</body>`, targeting any combination of the
public (guest), portal (client), and staff (admin) surfaces. v1 parity for
the `create_assets` / `edit_assets` / `delete_assets` permissions.

## Host integration contract

A host application must provide all of the following before requiring this
package:

1. **`staff` route middleware alias**, resolving to whatever the host uses
   to gate its own staff-only routes (see the Cloud repo's
   `App\Modules\Identity\Http\Middleware\EnsureStaff` for the reference
   implementation this assumes).
2. **Gates for `create_assets`, `edit_assets`, `delete_assets`** (and any
   other module's ability strings). The Cloud repo's
   `App\Modules\Identity\IdentityServiceProvider` already registers a Gate
   for every `Permission` enum case generically — a Community host sharing
   that same core `app/Modules/Identity` code gets this for free.
3. **A `User` model (or whatever the host's `Authenticatable` is) that also
   implements `Illuminate\Contracts\Auth\Access\Authorizable`** — true of
   any stock Laravel user model using the framework's default
   `Authorizable` trait.
4. **Root Blade view integration** — call the renderer at the three
   injection points:

   ```blade
   @php
       $customAssets = app(\ProjectSend\CommunityModules\Modules\CustomAssets\Rendering\CustomAssetRenderer::class);
       $assetSurface = \ProjectSend\CommunityModules\Modules\CustomAssets\AssetSurface::current(auth()->user(), isStaff: auth()->user()?->isStaff() ?? false);
   @endphp
   <head>
       ...
       {!! $customAssets->render(\ProjectSend\CommunityModules\Modules\CustomAssets\AssetPosition::Head, $assetSurface) !!}
   </head>
   <body>
       {!! $customAssets->render(\ProjectSend\CommunityModules\Modules\CustomAssets\AssetPosition::BodyTop, $assetSurface) !!}
       @inertia
       {!! $customAssets->render(\ProjectSend\CommunityModules\Modules\CustomAssets\AssetPosition::BodyBottom, $assetSurface) !!}
   </body>
   ```

5. **Frontend npm dependencies**, added to the host's own `package.json`
   (this package ships no `package.json` of its own — a package's `.tsx`
   pages are bundled by whichever host's Vite build includes them, exactly
   like `cloud-modules`' theme pages are today):

   ```json
   "@uiw/react-codemirror": "^4",
   "@codemirror/lang-html": "^6",
   "@codemirror/lang-css": "^6",
   "@codemirror/lang-javascript": "^6"
   ```

   The host's root Blade view must also already glob-discover package pages
   under `packages/*/resources/js/pages/{component}.tsx` (or equivalent),
   the same way the Cloud repo's `resources/views/app.blade.php` does —
   expected to be inherited from the same core if/when the Community host
   is split out of that repo.

## Development

```bash
composer install
vendor/bin/pest
vendor/bin/phpstan analyse
```

No host application is required for any of the above — everything runs
against a throwaway Testbench app.

## Licensing

**GNU General Public License v2, or (at your option) any later version** —
see [LICENSE](LICENSE). The same terms as ProjectSend itself, because this
is part of the free edition; being a separate repository is a deployment
decision, not a licensing one.

Contributions require signing a CLA, as they do in the core repository:
[CLA-INDIVIDUAL.md](CLA-INDIVIDUAL.md) for yourself, or
[CLA-ENTITY.md](CLA-ENTITY.md) if you're contributing on behalf of an
employer. The commercial-licensing option is explained once, for the
project as a whole, in the core repository's `LICENSING.md`.
