"""Scénario de test de l'éditeur de blocs : régions, sections autorisées, _max, rendu front.

Usage : lando browser tests/browser/editor.py
Les attentes sont lues dans window.carloRegions (donc dans structure.yml). Les pages créées
sont supprimées à la fin ; les captures sont dans tests/browser/shots/.
"""
import sys

from wp import ADMIN, SITE, admin_page, shot

TEXT = "Bonjour depuis le test navigateur"
failures = []


def check(label, ok, detail=None):
    print(("✓ " if ok else "✗ ") + label + (f" : {detail}" if detail is not None and not ok else ""))
    if not ok:
        failures.append(label)


def open_new_page(page, template=""):
    """Ouvre une nouvelle page dans l'éditeur ; renvoie (iframe du contenu, ID du brouillon)."""
    query = f"&carlo_template={template}" if template else ""
    page.goto(f"{ADMIN}/post-new.php?post_type=page{query}", wait_until="domcontentloaded")
    page.wait_for_function("window.wp?.data?.select('core/block-editor').getBlocks().length > 0", timeout=30000)
    page.keyboard.press("Escape")  # guide de bienvenue éventuel
    return page.frame_locator('iframe[name="editor-canvas"]'), page.evaluate("wp.data.select('core/editor').getCurrentPostId()")


def regions(page):
    return page.evaluate("""() => wp.data.select('core/block-editor').getBlocks().map(b => ({
        id: b.clientId, name: b.attributes.region, ...window.carloRegions[b.attributes.region] }))""")


def can_insert(page, block, root):
    return page.evaluate("([b, r]) => wp.data.select('core/block-editor').canInsertBlockType(b, r)", [block, root])


created = []
with admin_page() as page:
    nonce = None
    try:
        # 1. Modèle à plusieurs régions, choisi à la création puis figé
        canvas, post_id = open_new_page(page, "landing")
        created.append(post_id)
        nonce = page.evaluate("wp.apiFetch.nonceMiddleware.nonce")
        rs = regions(page)
        check("une région par région du modèle", [r["name"] for r in rs] == ["header", "content"], [r["name"] for r in rs])
        labels = canvas.locator(".carlo-region__header > span:first-child").all_text_contents()
        check("régions nommées", labels == [r["label"] for r in rs], labels)
        be = "wp.data.select('core/block-editor')"
        check("régions ni déplaçables ni supprimables",
              page.evaluate(f"ids => ids.every(id => !{be}.canMoveBlock(id) && !{be}.canRemoveBlock(id))", [r["id"] for r in rs]))
        check("aucune insertion à la racine", not any(can_insert(page, b, "") for r in rs for b in r["allowedBlocks"]))
        for r in rs:
            others = {b for o in rs for b in o["allowedBlocks"]} - set(r["allowedBlocks"])
            check(f"région « {r['label']} » : seules ses sections sont acceptées",
                  all(can_insert(page, b, r["id"]) for b in r["allowedBlocks"])
                  and not any(can_insert(page, b, r["id"]) for b in others))
        check("pas de sélecteur de modèle",
              not page.evaluate("Object.keys(wp.data.select('core/editor').getEditorSettings().availableTemplates || {}).length"))
        shot(page, "landing")

        # 2. Modèle par défaut : choix des sections et maximum
        canvas, post_id = open_new_page(page)
        created.append(post_id)
        region = regions(page)[0]
        appender = canvas.locator(".carlo-region .block-editor-button-block-appender")
        appender.first.click()
        titles = page.locator(".block-editor-block-types-list__item-title").all_text_contents()
        check("le sélecteur ne propose que les sections de la région", len(titles) == len(region["allowedBlocks"]), titles)
        shot(page, "inserter")
        page.keyboard.press("Escape")
        for i in range(region["max"] or len(titles)):
            appender.first.click()
            page.locator(".block-editor-block-types-list__item").nth(i % len(titles)).click()
            page.wait_for_timeout(1500)
        if region["max"]:
            counter = canvas.locator(".carlo-region__count").text_content()
            check("compteur au maximum", f"{region['max']} / {region['max']}" in counter, counter)
            check("bouton d'ajout masqué au maximum", appender.count() == 0)
            check("insertion bloquée au maximum (duplication, collage…)",
                  not any(can_insert(page, b, region["id"]) for b in region["allowedBlocks"]))
        check("message sur les sections vides", canvas.locator(".carlo-placeholder").count() > 0)

        # 3. Saisie d'une section Text, publication, rendu front
        text_block = canvas.locator("[data-type='acf/carlo-sections-text']").first
        if text_block.count():
            text_block.click()
            field = page.locator(".block-editor-block-inspector .acf-field[data-name='content']")
            field.locator(".switch-html").click()
            field.locator("textarea").fill(f"<p>{TEXT}</p>")
            field.locator("textarea").dispatch_event("change")
            page.wait_for_timeout(3000)
            check("aperçu mis à jour après saisie", TEXT in text_block.inner_text())
            shot(page, "editor")

            page.evaluate("wp.data.dispatch('core/editor').editPost({ title: 'Test navigateur', status: 'publish' })")
            page.evaluate("wp.data.dispatch('core/editor').savePost()")
            page.wait_for_function("!wp.data.select('core/editor').isSavingPost()", timeout=30000)
            front = page.context.new_page()
            front.goto(page.evaluate("wp.data.select('core/editor').getPermalink()"), wait_until="domcontentloaded")
            check("rendu front de la section saisie", TEXT in front.inner_text("main"))
            check("sections vides absentes du front", front.locator("main section").count() == 1)
            shot(front, "front", full_page=True)
            front.close()
        else:
            print("- pas de section Text dans la région : saisie et rendu front non testés")

        # Une ressource en 502 signale le plus souvent un serveur Vite arrêté (lando logs -s front).
        check("aucune erreur JS ni ressource en échec", not page.errors, page.errors)
    finally:
        for post_id in created:
            response = nonce and page.request.delete(
                f"{SITE}/?rest_route=/wp/v2/pages/{post_id}&force=true", headers={"X-WP-Nonce": nonce}
            )
            if not response or not response.ok:
                print(f"! page de test {post_id} non supprimée : à supprimer à la main")

print(f"\n{len(failures)} échec(s)" if failures else "\nTout est vert.")
sys.exit(1 if failures else 0)
