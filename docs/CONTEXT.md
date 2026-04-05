# CONTEXT.md

## What This Project Is

This repository is a MyBB 1.8 forum codebase with a substantial set of custom forum/game systems built directly into the same application. It contains both:

- MyBB core code
- custom forum-specific logic and UI

The main custom backend is under `sg/`, but the actual behavior of the site is distributed across custom modules, modified core files, plugins, template references, and MyBB database-stored templates.

For database orientation:

- `docs/DATABASE.md` is the practical schema summary for agents and new contributors.
- `docs/shinobi9_mybb.sql` is the underlying dump/source used to derive that summary.

## How The Repo Is Actually Organized

The practical structure is:

- `sg/`
  Main custom backend modules.
- `sg/admin/`
  Staff and moderation utilities for operational tasks.
- `sg/functions/sg_functions.php`
  Shared helper functions used across custom pages.
- `templates/`
  HTML/CSS/JS reference copies for templates and styling, not necessarily the active runtime source.
- `inc/`
  MyBB internals plus custom plugin and core-level changes.
- `admin/`
  MyBB admin-side code.
- `docs/DATABASE.md`
  Agent-facing working summary of the most important tables and relationships.
- `docs/shinobi9_mybb.sql`
  Raw SQL dump and closest thing to a schema source of truth in the repo.
- `docs/STYLE.md`
  Visual system reference.
- `tests/`
  Likely a lightweight visual sandbox for CSS/template work.

## What Is MyBB And What Is Custom

### Mostly MyBB

- `global.php`
- `showthread.php`
- `newthread.php`
- `xmlhttp.php`
- `hide.php`
- `inc/`
- `admin/`

These are MyBB framework/core areas, but some of them contain project-specific changes.

### Mostly Custom

- `sg/ficha.php`
- `sg/nueva_ficha.php`
- `sg/editar_ficha.php`
- `sg/misiones.php`
- `sg/entrenamientos.php`
- `sg/inventario.php`
- `sg/objetos.php`
- `sg/armas.php`
- `sg/tienda.php`
- `sg/tecnicas*.php`
- `sg/censo.php`
- `sg/npcs.php`
- `sg/promocion.php`
- `sg/peticiones.php`
- `sg/admin/*`

### Mixed / Important Boundary Files

These are especially important because they bridge core forum behavior and custom logic:

- `inc/functions_post.php`
- `inc/plugins/tecnicatag.php`
- `inc/plugins/hidetag.php`
- `inc/plugins/newpoints.php`
- `inc/plugins/newpoints/core/hooks.php`
- `global.php`
- `showthread.php`
- `newthread.php`
- `inc/class_mailhandler.php`
- `inc/tasks/checktables.php`

## Where To Look Depending On The Change

### Character sheet or player progression features

Look in:

- `sg/ficha.php`
- `sg/ficha2.php`
- `sg/nueva_ficha.php`
- `sg/editar_ficha.php`
- `sg/ficha_editada.php`
- `sg/functions/sg_functions.php`
- `docs/DATABASE.md`

### Techniques, training, or missions

Look in:

- `sg/tecnicas.php`
- `sg/tecnicas2.php`
- `sg/tecnicas_lista.php`
- `sg/tecnicas_show.php`
- `sg/tecnicas_show2.php`
- `sg/entrenamientos.php`
- `sg/misiones.php`
- `inc/plugins/tecnicatag.php`
- `docs/DATABASE.md`

### Inventory, objects, weapons, or shop

Look in:

- `sg/inventario.php`
- `sg/objetos.php`
- `sg/armas.php`
- `sg/tienda.php`
- `sg/admin/crear_objetos.php`
- `sg/admin/modificar_objetos.php`
- `docs/DATABASE.md`

### Post rendering, likes, thread character tagging, or posting flow

Look in:

- `showthread.php`
- `newthread.php`
- `xmlhttp.php`
- `hide.php`
- `inc/functions_post.php`
- `inc/plugins/hidetag.php`
- `inc/plugins/tecnicatag.php`

### Layout, templates, or styles

Look in:

- `templates/html/`
- `templates/css/`
- `templates/js/`
- `docs/STYLE.md`
- `tests/`

## How `sg/`, Templates, MyBB Core, And The Database Fit Together

Many custom pages in `sg/` follow this pattern:

1. define `IN_MYBB`
2. include `./../global.php`
3. include `./../inc/config.php`
4. include helpers from `sg/functions/sg_functions.php`
5. query custom `mybb_sg_*` tables directly
6. render through MyBB template calls or page output

That means:

- `sg/` contains most custom domain logic.
- MyBB core still provides the bootstrap, globals, session, templating, and forum runtime.
- `templates/` contains reference files, but many live templates are stored in MyBB's database.
- some active behavior is implemented in modified core files and plugins, not just custom pages.
- `docs/DATABASE.md` should be treated as the fast schema map, while `docs/shinobi9_mybb.sql` remains the raw schema source.

Do not assume filesystem template files are the runtime source of truth.

## Implicit Project Conventions

- The codebase is procedural and legacy-oriented.
- SQL is often inline and directly tied to page behavior.
- Template rendering is tightly coupled to PHP variables and MyBB's template system.
- Custom forum mechanics are table-driven through `mybb_sg_` and `mybb_sg_sg_` tables.
- Staff tooling is built directly into the same application under `sg/admin/`.
- Reference templates are often edited in files, then copied manually into MyBB-managed templates.
- Schema relationships are often enforced by application convention rather than explicit foreign keys.

## Common Custom Data Areas

The project heavily uses custom tables such as:

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

There are also standard MyBB tables in use with the project's prefix, such as `mybb_sg_users`, `mybb_sg_posts`, `mybb_sg_threads`, and `mybb_sg_forums`.

## Frequent Editing Risks

- Editing `templates/` and assuming production will update automatically.
- Changing only `sg/` when part of the behavior actually lives in a plugin or core file.
- Treating `inc/functions_post.php` as stock MyBB code.
- Changing SQL without understanding how fields are used in templates.
- Refactoring legacy procedural code too aggressively.
- Missing database-stored template dependencies when changing markup.
- Assuming duplicated flows are safe to consolidate without tracing them first.

## Quick Truths

- `sg/` is the main custom backend, but not the whole custom system.
- `templates/` is a reference/editing layer, not guaranteed runtime truth.
- MyBB database templates matter here.
- Core files contain custom patches.
- `inc/functions_post.php` is a high-value file for many forum UI behaviors.
- `docs/DATABASE.md` is the fastest way to orient around custom tables.
- `docs/shinobi9_mybb.sql` is the deeper schema source when field-level certainty matters.
- `docs/STYLE.md` is the clearest design reference in the repo.
- `tests/` looks useful for visual iteration, not as a full test suite.

## Rules For An AI Before Proposing Changes

- Search by `sg_` and `mybb_sg_` first.
- Find the runtime entry point before suggesting edits.
- Read `docs/DATABASE.md` before making assumptions about custom tables.
- Fall back to `docs/shinobi9_mybb.sql` if column names, defaults, or table shape need confirmation.
- Check whether the behavior spans PHP, plugin hooks, templates, and CSS.
- Preserve existing procedural style unless there is a strong reason not to.
- Do not assume modern tooling, CI, or a reliable local setup exists.
- Do not assume template files and DB templates are synchronized.
- If a change touches template references, say whether manual copy into MyBB is still required.
- If a change touches core files or SQL, call out the risk explicitly.

## Short Operational View

If you need to orient quickly:

- Start in `sg/` for custom feature logic.
- Check `inc/functions_post.php` for post/thread rendering behavior.
- Check `inc/plugins/` for parser, tag, hide, or hook-based behavior.
- Check `templates/` for reference markup/styles.
- Assume final rendering may still depend on MyBB database templates.
