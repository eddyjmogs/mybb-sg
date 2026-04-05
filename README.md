# mybb-sg

This repository contains a MyBB 1.8 forum codebase with a substantial custom application layer built on top of it. Most forum-specific backend logic lives in `sg/`, but the full project also includes customizations inside MyBB core files, plugins, styles, and template references.

## What This Repository Is

This is not a small plugin or theme-only project. It is a full forum repository based on MyBB 1.8, with custom roleplay/forum systems implemented directly inside the same codebase.

At a high level:

- MyBB provides the base forum platform and runtime.
- `sg/` contains most of the forum's custom domain logic.
- `templates/` stores editable template and style references, but is not the canonical runtime source for many templates.
- Some custom behavior also lives in modified MyBB core files and plugins.

## How It Relates To MyBB

The project follows the MyBB structure and bootstrap flow. Custom pages in `sg/` typically load MyBB by including [global.php](/Users/eddymogollon/Documents/Code/mybb-sg/global.php), then access MyBB globals such as `$db`, `$mybb`, and `$templates`.

Common pattern in custom modules:

- define `IN_MYBB`
- include [global.php](/Users/eddymogollon/Documents/Code/mybb-sg/global.php)
- include `inc/config.php`
- include shared helpers from [sg/functions/sg_functions.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/functions/sg_functions.php)
- run direct SQL queries against MyBB tables and custom `mybb_sg_*` tables
- render through MyBB template objects where applicable

This means changes should be approached as changes to a live MyBB application, not as an isolated PHP app.

## General Architecture

The codebase is split across four practical layers:

1. MyBB core application
   Files such as [global.php](/Users/eddymogollon/Documents/Code/mybb-sg/global.php), [showthread.php](/Users/eddymogollon/Documents/Code/mybb-sg/showthread.php), [newthread.php](/Users/eddymogollon/Documents/Code/mybb-sg/newthread.php), `inc/*`, `admin/*`, and other standard MyBB entry points.

2. Custom forum application layer
   The main custom logic is in [sg](/Users/eddymogollon/Documents/Code/mybb-sg/sg), including modules for character sheets, techniques, missions, training, inventory, NPCs, promotions, requests, and staff tools.

3. Template and style reference layer
   The [templates](/Users/eddymogollon/Documents/Code/mybb-sg/templates) directory contains HTML, CSS, and JS reference files used as editable source material for templates that are often stored in MyBB's database.

4. Customizations inside MyBB core and plugins
   Important forum-specific behavior is also implemented outside `sg/`, especially in selected core files and plugins.

## Where The Custom Code Actually Lives

Most of the custom backend is in [sg](/Users/eddymogollon/Documents/Code/mybb-sg/sg), but not all of it.

Important custom areas include:

- [sg/functions/sg_functions.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/functions/sg_functions.php)
  Shared helper functions used across custom pages.
- [sg/ficha.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/ficha.php)
  Character sheet flow and related stat/view logic.
- [sg/nueva_ficha.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/nueva_ficha.php)
  Character sheet creation flow.
- [sg/misiones.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/misiones.php)
  Mission assignment and completion flow.
- [sg/entrenamientos.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/entrenamientos.php)
  Training workflow and rewards.
- [sg/inventario.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/inventario.php)
  Inventory display logic.
- [sg/objetos.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/objetos.php), [sg/armas.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/armas.php), [sg/tienda.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/tienda.php)
  Item, weapon, and shop-related features.
- [sg/tecnicas.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/tecnicas.php), [sg/tecnicas_lista.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/tecnicas_lista.php), [sg/tecnicas_show.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/tecnicas_show.php)
  Technique trees and technique browsing.
- [sg/censo.php](/Users/eddymogollon/Documents/Code/mybb-sg/sg/censo.php)
  Census, stats, and admin-facing operational summaries.
- [sg/admin](/Users/eddymogollon/Documents/Code/mybb-sg/sg/admin)
  Staff/admin tools for modifying fichas, objects, techniques, NPCs, logs, and moderation-related actions.

Custom behavior also exists outside `sg/`, for example:

- [inc/functions_post.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/functions_post.php)
  Custom postbit behavior, likes, clan/ficha data, and thread character tagging.
- [inc/plugins/tecnicatag.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/plugins/tecnicatag.php)
  Custom MyCode/plugin behavior for forum-specific tags.
- [inc/plugins/hidetag.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/plugins/hidetag.php)
- [inc/plugins/newpoints.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/plugins/newpoints.php)
- [inc/plugins/newpoints/core/hooks.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/plugins/newpoints/core/hooks.php)
- [global.php](/Users/eddymogollon/Documents/Code/mybb-sg/global.php)
- [showthread.php](/Users/eddymogollon/Documents/Code/mybb-sg/showthread.php)
- [newthread.php](/Users/eddymogollon/Documents/Code/mybb-sg/newthread.php)
- [hide.php](/Users/eddymogollon/Documents/Code/mybb-sg/hide.php)
- [xmlhttp.php](/Users/eddymogollon/Documents/Code/mybb-sg/xmlhttp.php)
- [inc/class_mailhandler.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/class_mailhandler.php)
- [inc/tasks/checktables.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/tasks/checktables.php)

Do not assume all forum-specific behavior is isolated to `sg/`.

## Templates And Styles

The [templates](/Users/eddymogollon/Documents/Code/mybb-sg/templates) directory is important, but it is not the canonical runtime source for many forum templates.

MyBB stores many templates in the database. In this repository, `templates/` is used as:

- a reference copy of HTML templates
- a reference copy of CSS files
- a reference copy of JS snippets
- an editing workspace before manual copy/paste into MyBB's template system

This is especially important for:

- [templates/html](/Users/eddymogollon/Documents/Code/mybb-sg/templates/html)
- [templates/css](/Users/eddymogollon/Documents/Code/mybb-sg/templates/css)
- [templates/js](/Users/eddymogollon/Documents/Code/mybb-sg/templates/js)

Examples:

- [templates/html/sg_ficha.html](/Users/eddymogollon/Documents/Code/mybb-sg/templates/html/sg_ficha.html)
- [templates/html/sg_nueva_ficha.html](/Users/eddymogollon/Documents/Code/mybb-sg/templates/html/sg_nueva_ficha.html)
- [templates/js/sg_nueva_ficha_script.html](/Users/eddymogollon/Documents/Code/mybb-sg/templates/js/sg_nueva_ficha_script.html)
- [templates/css/sg_global.css](/Users/eddymogollon/Documents/Code/mybb-sg/templates/css/sg_global.css)
- [templates/css/customSG.css](/Users/eddymogollon/Documents/Code/mybb-sg/templates/css/customSG.css)

Important workflow note:

- Editing a file in `templates/` does not necessarily change what production renders immediately.
- Many changes must be copied manually into MyBB's template or theme management system.
- Before assuming a file is active at runtime, verify whether the live template is stored in the database, in the filesystem, or split across both.

## Key Directories

- [sg](/Users/eddymogollon/Documents/Code/mybb-sg/sg)
  Main custom forum logic.
- [sg/admin](/Users/eddymogollon/Documents/Code/mybb-sg/sg/admin)
  Staff/admin utilities.
- [sg/functions](/Users/eddymogollon/Documents/Code/mybb-sg/sg/functions)
  Shared helper functions.
- [templates](/Users/eddymogollon/Documents/Code/mybb-sg/templates)
  Reference HTML/CSS/JS for MyBB-managed templates and styles.
- [inc](/Users/eddymogollon/Documents/Code/mybb-sg/inc)
  MyBB internals plus custom modifications and plugins.
- [admin](/Users/eddymogollon/Documents/Code/mybb-sg/admin)
  Admin control panel code and modules.
- [docs](/Users/eddymogollon/Documents/Code/mybb-sg/docs)
  Documentation, including the design system.
- [docs/STYLE.md](/Users/eddymogollon/Documents/Code/mybb-sg/docs/STYLE.md)
  Visual system and style guidance.
- [tests](/Users/eddymogollon/Documents/Code/mybb-sg/tests)
  Lightweight visual/testing sandbox for template and CSS work.
- [images](/Users/eddymogollon/Documents/Code/mybb-sg/images)
  Forum assets, including project-specific art and static resources.
- [jscripts](/Users/eddymogollon/Documents/Code/mybb-sg/jscripts)
  Frontend scripts used by MyBB and custom behavior.

## Data Model Notes

The custom system relies heavily on direct SQL queries against MyBB tables and custom tables, typically prefixed with `mybb_sg_` and `mybb_sg_sg_`.

Examples seen throughout the codebase:

- `mybb_sg_sg_fichas`
- `mybb_sg_sg_tecnicas`
- `mybb_sg_sg_objetos`
- `mybb_sg_sg_misiones_lista`
- `mybb_sg_sg_misiones_usuarios`
- `mybb_sg_sg_entrenamientos_usuarios`
- `mybb_sg_sg_thread_personaje`
- `mybb_sg_sg_likes`
- `mybb_sg_sg_clanes`
- `mybb_sg_sg_villas`
- `mybb_sg_sg_npcs`

Because many features are query-driven and procedural, schema awareness matters before changing PHP behavior.

## Recommended Change Workflow

When making changes, treat the repository as a mixed MyBB + custom application.

Recommended order of investigation:

1. Identify the runtime entry point.
   For example, a post view may involve [showthread.php](/Users/eddymogollon/Documents/Code/mybb-sg/showthread.php), [inc/functions_post.php](/Users/eddymogollon/Documents/Code/mybb-sg/inc/functions_post.php), plugins, templates, and CSS.

2. Check whether the feature is implemented in `sg/`, a core file, a plugin, or all three.

3. If UI is involved, inspect both:
   - the reference files under [templates](/Users/eddymogollon/Documents/Code/mybb-sg/templates)
   - the PHP path that calls `$templates->get(...)`

4. If data is involved, inspect the related `mybb_sg_*` queries first.

5. If the feature touches forum posting, thread display, likes, or parsing, review plugin and core hooks before changing only the page file.

6. If a template change is made in `templates/`, note whether it still needs manual copy/paste into MyBB's template system.

If you need to run the project locally, assume a standard PHP/MySQL-backed MyBB environment may be required, but this repository does not document a guaranteed one-command local setup.

## Important Warnings

- `templates/` is a reference layer, not necessarily the live rendering source.
- Not all custom code lives in `sg/`.
- Some MyBB core files appear to contain project-specific modifications.
- SQL is often written inline and directly coupled to page behavior.
- Procedural PHP and template-driven rendering are tightly linked.
- Changing a template file alone may not change the live forum unless the corresponding database template is also updated.
- Changing core files without checking related plugins or template hooks can create partial fixes.

## Documentation Status And Limitations

Current documentation in the repository is limited.

What exists:

- a minimal root README was previously present
- [docs/STYLE.md](/Users/eddymogollon/Documents/Code/mybb-sg/docs/STYLE.md) documents the visual system in detail
- the repository structure itself is the main source of truth for behavior

What is not clearly documented here:

- a full database schema
- a reliable local setup workflow
- deployment steps
- a definitive map of which templates are still database-managed versus filesystem-managed
- a list of all custom patches against upstream MyBB

Because of that, the safest way to work in this codebase is to trace behavior from entry point to SQL to template output before making changes.
