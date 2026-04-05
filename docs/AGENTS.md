# AGENTS.md

## Project Summary

This repository contains a MyBB 1.8 forum codebase with a significant custom application layer for the forum's domain-specific features. The main custom backend lives in `sg/`, but the full behavior of the forum is distributed across:

- MyBB core files
- custom PHP modules in `sg/`
- modified plugins in `inc/plugins/`
- template references in `templates/`
- database-stored MyBB templates and styles

Treat this as a hybrid MyBB + custom legacy application, not as a cleanly separated plugin project.

Database reference:

- Use `docs/DATABASE.md` as the first-stop schema guide before changing SQL-heavy code or custom table flows.
- Treat `docs/shinobi9_mybb.sql` as the underlying schema source, and `docs/DATABASE.md` as the practical working summary for agents.

## Architecture Rules Before Editing

- Understand the runtime entry point before changing anything.
- Do not assume a feature is implemented in only one place.
- Search by route, template name, and database table prefix before editing.
- Search for both `sg_` and `mybb_sg_` references first.
- Check `docs/DATABASE.md` before changing queries, table assumptions, or joins involving custom SG tables.
- Assume UI behavior may be split between PHP, DB-managed templates, reference files under `templates/`, CSS, and MyBB plugins.
- Assume posting and thread rendering may involve core files, hooks, custom tags, and template calls together.

Common custom page pattern in `sg/`:

- define `IN_MYBB`
- include `./../global.php`
- include `./../inc/config.php`
- include shared helpers from `sg/functions/sg_functions.php`
- query `mybb_sg_*` or `mybb_sg_sg_*` tables directly
- render via `$templates->get(...)` or other MyBB flows

## Where To Look First By Change Type

### Character sheets, user progression, or profile-like game data

Check:

- `sg/ficha.php`
- `sg/ficha2.php`
- `sg/nueva_ficha.php`
- `sg/editar_ficha.php`
- `sg/ficha_editada.php`
- `sg/functions/sg_functions.php`
- `docs/DATABASE.md`

### Techniques, training, missions, rewards

Check:

- `sg/tecnicas.php`
- `sg/tecnicas2.php`
- `sg/tecnicas_lista.php`
- `sg/tecnicas_show.php`
- `sg/tecnicas_show2.php`
- `sg/entrenamientos.php`
- `sg/misiones.php`
- `inc/plugins/tecnicatag.php`
- `docs/DATABASE.md`

### Inventory, objects, weapons, shop

Check:

- `sg/inventario.php`
- `sg/objetos.php`
- `sg/armas.php`
- `sg/tienda.php`
- `sg/admin/crear_objetos.php`
- `sg/admin/modificar_objetos.php`
- `docs/DATABASE.md`

### Thread/post rendering, likes, character tags, forum UI behavior

Check:

- `showthread.php`
- `newthread.php`
- `xmlhttp.php`
- `hide.php`
- `inc/functions_post.php`
- `inc/plugins/hidetag.php`
- `inc/plugins/tecnicatag.php`
- template references under `templates/`

### Staff/admin workflows

Check:

- `sg/admin/`
- `admin/`
- any related audit/log table usage

### Styling or layout

Check:

- `templates/css/`
- `templates/html/`
- `templates/js/`
- `docs/STYLE.md`
- `tests/`

## Rules For Template Changes

- `templates/` is a reference layer, not necessarily the active runtime source.
- Do not assume editing a file under `templates/` will change production output immediately.
- MyBB stores many templates in the database, so some changes must still be copied manually into MyBB's template manager or corresponding DB-managed template.
- Before editing a template reference, identify the PHP path that calls `$templates->get(...)`.
- If a change affects markup, check the related CSS and JS references too.
- If you change anything under `templates/`, explicitly state whether the change still requires manual copy/paste into MyBB.
- Do not move logic between PHP and templates unless you understand the DB template dependency and the active render path.

## Rules For Custom PHP Changes

- Preserve the existing procedural style unless the task clearly requires a different approach.
- Do not modernize legacy MyBB-era PHP indiscriminately.
- Prefer small, localized changes over architectural rewrites.
- Read the surrounding SQL carefully before editing behavior.
- Cross-check custom table purpose and important columns in `docs/DATABASE.md` before changing field assumptions.
- Expect direct coupling between SQL fields, template variables, and output markup.
- Reuse `sg/functions/sg_functions.php` patterns when touching shared custom logic.
- If a feature reads or writes `mybb_sg_*` tables, verify the exact field names in nearby code before changing assumptions.
- If a page updates user/game state, check for related audit tables, admin tools, or mirrored logic elsewhere.

## Rules For MyBB Core Or Plugin Changes

- Be extra cautious when touching `global.php`, `showthread.php`, `newthread.php`, `xmlhttp.php`, `hide.php`, `inc/functions_post.php`, or plugin files under `inc/plugins/`.
- Assume a core file may already contain project-specific patches.
- Do not overwrite or refactor broad sections just because the code looks old.
- Check whether a behavior is also implemented in a hook, plugin, or DB template before changing core code.
- Document risks whenever you touch:
  - core files
  - plugin hooks
  - SQL used in rendering paths
  - posting/thread display behavior
- If changing post/thread behavior, inspect both the render path and the associated template path.

## Assumptions You Must Not Make

- Do not assume all custom code lives in `sg/`.
- Do not assume `templates/` is the active source of truth at runtime.
- Do not assume there is automatic synchronization between `templates/` files and MyBB database templates.
- Do not assume there is a modern build pipeline, test runner, or CI workflow.
- Do not assume the schema is fully documented.
- Do not assume `docs/DATABASE.md` replaces checking the real SQL dump and nearby PHP usage.
- Do not assume an apparently duplicated flow is actually safe to remove.
- Do not assume MyBB core files are stock upstream versions.
- Do not assume a UI issue is only CSS or only template related.

## Before Closing A Task

- Confirm the actual entry point(s) involved.
- Confirm whether the behavior lives in `sg/`, core, plugin, template references, or more than one.
- Confirm whether any `templates/` change still requires manual copy into MyBB.
- Re-scan for related `sg_` and `mybb_sg_` references.
- Re-check `docs/DATABASE.md` if the task touched custom tables, progression logic, inventory, missions, or post-linked custom mechanics.
- Check for nearby audit/log side effects when changing stateful features.
- Check whether the change could affect thread/post rendering, staff tools, or DB-stored templates indirectly.
- Avoid leaving unexplained partial changes across PHP and templates.

## Output Checklist For The Human

When reporting work back, include:

- what changed at a high level
- which files were changed
- whether the change touches `sg/`, MyBB core, plugins, or template references
- whether manual copy/paste into MyBB templates is still required
- whether any table assumptions came from `docs/DATABASE.md`, the SQL dump, or direct code inspection
- any risks or uncertainty around runtime templates or SQL behavior
- whether anything could not be verified locally

## Practical Search Heuristics

- Search for route names first.
- Search for `$templates->get("...")` to find render paths.
- Search `docs/DATABASE.md` and then `docs/shinobi9_mybb.sql` before changing custom-table logic.
- Search for `mybb_sg_` and `mybb_sg_sg_` table references before changing data logic.
- Search `inc/functions_post.php` whenever the issue touches posts, postbits, likes, or thread-level character behavior.
- Search `inc/plugins/` whenever the issue looks like custom MyCode, parser, hide, or posting hook behavior.

## Known Repo-Specific Anchors

- Main custom backend: `sg/`
- Shared helper file: `sg/functions/sg_functions.php`
- Staff tooling: `sg/admin/`
- Template references: `templates/html/`, `templates/css/`, `templates/js/`
- Visual documentation: `docs/STYLE.md`
- Database working reference: `docs/DATABASE.md`
- Raw schema source: `docs/shinobi9_mybb.sql`
- Visual sandbox: `tests/`

If you are unsure where a feature is active, trace it from entry point to SQL to template output before proposing a change.
