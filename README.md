# OAP Instance Example — a blueprint instance extension for open-oap

A **copy-me skeleton** for building the *instance layer* of an
[Open Application Platform](https://github.com/openoap/open-oap) (open-oap)
installation.

open-oap ships the application logic — proposals, applicants, dashboard,
notifications, backend modules. What it deliberately does **not** ship is the
per-installation configuration: which sysfolders hold which records, which
frontend pages carry which plugin, the sender address for account mails, your
branding. Its own constants say as much:

> `# They must be set by the site integrator in the TYPO3 constants editor.`

What the package does not say is *where* to keep them. Today it registers an
instance-specific PageTSConfig file of its own
(`Configuration/TSconfig/oap--developpp.tsconfig`) from
`Configuration/TCA/Overrides/tx_openoap_domain_model_call.php` — the UIDs of one
particular installation, shipped in the vendor package. A pull request is pending
that removes that registration and leaves a pointer in its place:

> `// use this file to register PageTSConfig Files for your instance in an instance template extension`

This repository is the instance template extension that pointer refers to,
generalized so you can copy it, rename it and fill in your values. (Until the
pull request is merged, the line above is not yet part of open-oap.)

> **Scope.** This blueprint covers the **configuration / override layer** only.
> It does not reproduce any single production instance: site theme, custom
> frontend build and changes to upstream PHP (done via
> [`cweagans/composer-patches`](https://github.com/cweagans/composer-patches))
> live elsewhere. See [What this does *not* cover](#what-this-does-not-cover).

---

## What an instance extension is for

| It provides                                                        | It does **not** provide                                            |
|--------------------------------------------------------------------|--------------------------------------------------------------------|
| Instance constants (storage PIDs, page IDs, mail identity, limits) | PHP classes (controllers, repositories, domain models)             |
| Fluid partial overrides with transparent fallback to open-oap      | Database tables or plugins                                         |
| PageTSConfig (backend record-selector restrictions)                | Backend modules                                                    |
| Instance TCA defaults for the call model                           | Patches against upstream (those live in your project's `patches/`) |
| Branding (favicon, fonts, logo, theme CSS)                         | A frontend build toolchain                                         |

Rule of thumb for changing OAP behaviour: **Constant → Partial → Patch.** Reach
for a patch only when the first two cannot express the change — patches are the
most expensive option to carry over upstream updates.

---

## Directory layout

```
oap_instance_example/
├── composer.json                     # package name + extension-key
├── ext_emconf.php                    # depends: open_oap
├── Configuration/
│   ├── TCA/Overrides/
│   │   ├── sys_template.php           # registers the static TypoScript template
│   │   └── tx_openoap_domain_model_call.php   # registers instance PageTSConfig
│   ├── TSconfig/
│   │   └── oap--default.tsconfig       # backend record-selector restrictions
│   └── TypoScript/
│       ├── constants.typoscript        # include hub
│       ├── setup.typoscript            # branding (favicon, fonts, CSS)
│       └── Extensions/
│           ├── open_oap/constants.typoscript   # all tx_openoap_* instance values
│           └── femanager/constants.typoscript  # FE-user storage + mail identity
└── Resources/
    ├── Private/
    │   ├── Partials/
    │   │   └── IncludeJs.html          # example override (loads instance scripts.js)
    │   └── Templates/
    │       ├── headerIncludes.html     # font preloads
    │       └── CssIncludes.html        # instance stylesheet(s)
    └── Public/                         # your compiled/branding assets (empty here)
        ├── Css/  Favicon/  Fonts/  Images/logos/  JavaScript/
```

---

## Quick start

### 1. Get the files into your project

Either add it as a Composer path repository (recommended while you adapt it):

```jsonc
// composer.json (project root)
"repositories": [
    { "type": "path", "url": "packages/*" }
],
"require": {
    "openoap/oap-instance-example": "@dev"
}
```

…or just copy the folder into your extensions directory.

### 2. Rename it to your instance

Pick a key, e.g. `oap_acme`. Replace **every** occurrence of the placeholder
name — it appears as three spellings:

| Spelling                                 | Example →       | Where                                                                    |
|------------------------------------------|-----------------|--------------------------------------------------------------------------|
| `oap_instance_example` (snake_case key)  | `oap_acme`      | file names, `EXT:` paths, TypoScript, `ext_emconf.php`, composer `extra` |
| `openoap/oap-instance-example` (package) | `acme/oap-acme` | `composer.json` `name`                                                   |
| `OapInstanceExample` (UpperCamelCase)    | `OapAcme`       | `extensionName` in Fluid `f:uri.resource`                                |

A quick sanity grep after renaming:

```bash
grep -rn "oap_instance_example\|OapInstanceExample\|oap-instance-example" .
```

Then:

```bash
ddev composer install
```

### 3. Include the static template — order matters

In your root page's `sys_template` record, tab **Includes → Include static (from
extensions)**, add the templates in this order:

```
1. Open Application Platform (open_oap)
2. OAP Instance: Example            ← this extension, AFTER open_oap
```

> ⚠️ **If the instance template is included before `open_oap`, open-oap
> overwrites your instance constants with empty defaults** (e.g. `uploadFolder`
> becomes empty and uploads break). Instance last, always.

### 4. Fill in your IDs

Every numeric ID in the TypoScript constants and in `oap--default.tsconfig` is a
placeholder (`0`). Replace them with the real UIDs from your installation —
see the two sections below.

### 5. Register the PageTSConfig

Root page → **Page properties → Resources → Page TSconfig → TSconfig files** →
add **“OAP Example Base - Page Configuration”**.

---

## Sysfolder structure

Create the sysfolders your instance needs and note their UIDs — you will
reference them from the TypoScript constants and the PageTSConfig. A typical OAP
instance separates:

- **Main data storage** — proposals & FE-users (`storagePid`)
- **FE-users** (femanager `persistence.storagePid`) — often its own folder
- **Calls**, **Proposals**, **Answers**, **Comments**
- **Form Items**, **Form Groups**, **Form Pages**, **Item Options**,
  **Item Validators**, **Modificators**
- A **FAL storage** for uploads (referenced as `uploadFolder = <uid>:/`)

You also need two FE-user groups (applicants, testers) — put their UIDs into
`generalFeGroupsId` / `testerFeGroupsId`.

---

## The instance constants

All live in
`Configuration/TypoScript/Extensions/open_oap/constants.typoscript` (plus the
femanager file). The important ones:

| Constant                                                                    | Meaning                                |
|-----------------------------------------------------------------------------|----------------------------------------|
| `plugin.tx_openoap_dashboard.persistence.storagePid`                        | Main storage (proposals + users)       |
| `settings.dashboardPageId` … `notificationsPageId`                          | Frontend page UIDs per plugin          |
| `settings.callPoolId`, `proposalsPoolId`, `answersPoolId`, `commentsPoolId` | Record-pool sysfolders                 |
| `settings.pidForm*`, `pidItem*`, `pidFormModificator`                       | Form-builder sysfolders                |
| `settings.uploadFolder`                                                     | `"<storageUid>:/"` FAL upload location |
| `settings.generalFeGroupsId` / `testerFeGroupsId`                           | FE-user groups                         |
| `settings.proposalsActiveLimit` / `proposalsArchivedLimit`                  | Dashboard list limits                  |
| `plugin.tx_femanager.persistence.storagePid`                                | FE-user (applicant) storage            |
| `plugin.tx_femanager.settings.adminEmail` / `adminName`                     | Sender identity for account mails      |

The same file also sets the `partialRootPath` for all six OAP plugins — see next.

---

## How the override mechanisms work

### Fluid partials (transparent fallback)

The `open_oap` constants set, for every plugin:

```typoscript
plugin.tx_openoap_<plugin>.view.partialRootPath = EXT:oap_instance_example/Resources/Private/Partials/
```

Upstream setup reads this as `partialRootPaths.1`, i.e. **higher priority** than
open-oap's own `.0`. Effect: a partial that exists in your `Partials/` folder
overrides the upstream one; every partial you do **not** provide falls back
silently to `EXT:open_oap/Resources/Private/Partials/`. So the folder need not be
complete — ship only what you change. The bundled `IncludeJs.html` is an example
that loads your own `scripts.js`; delete it to fall back to upstream.

### PageTSConfig

`Configuration/TCA/Overrides/tx_openoap_domain_model_call.php` registers
`oap--default.tsconfig`, which pins the backend record browsers of the OAP models
to the right sysfolders (`PAGE_TSCONFIG_ID` / `PAGE_TSCONFIG_IDLIST`). This is the
place the pointer quoted at the top of this README refers to: the registration
belongs in your own extension, not in the vendor package.

### TCA / static template

`sys_template.php` registers the static template so you can include it in the
backend; `tx_openoap_domain_model_call.php` is also where you would add
instance-specific TCA defaults for the call model (without changing its
structure).

### Branding

`setup.typoscript` wires favicon, font preloads and instance CSS into
`page.headerData`. All three blocks are optional and self-contained; delete what
you do not need. If your instance sits on a site package that already declares
`page.headerData.*`, override just the `.file` instead of the object type.

---

## Frontend assets

`Resources/Public/` ships **empty** on purpose (only `.gitkeep` files). It is the
drop zone for your compiled CSS/JS and branding — the blueprint has no build
toolchain and no fonts/logos (avoid shipping licensed assets in a template).

Put here: your compiled `Css/theme.css`, `JavaScript/scripts.js`, a
`Favicon/` set, web `Fonts/`, and `Images/logos/`. The Fluid templates and
`setup.typoscript` reference these paths with placeholder file names — adjust
them to what your build actually produces.

> Never hand-edit files under `Resources/Public/Css` or
> `Resources/Public/JavaScript` if they are build output — they get overwritten
> on the next build.

---

## What this does *not* cover

- **Site theme / page templates.** How your pages look (header, navigation,
  footer) is a separate concern — a TYPO3 site package or theme. This blueprint
  only touches the OAP plugin output and the page head.
- **Upstream PHP changes.** Behaviour changes to open-oap classes are done in
  your project via `cweagans/composer-patches`, not here.
- **Reusable add-on features.** Functionality that is useful across OAP projects
  but does not belong in open-oap core belongs in a *separate, installable*
  companion extension (`composer require …`), not baked into this copy-me
  skeleton — otherwise renaming forks it and it can never receive updates.

---

## License

GPL-2.0-or-later, matching TYPO3 and open-oap. Replace the author metadata in
`composer.json` and `ext_emconf.php` with your own when you adopt it.
