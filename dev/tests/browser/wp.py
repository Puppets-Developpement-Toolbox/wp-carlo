"""Outils communs aux tests navigateur : Chromium headless sur le site local, connecté ou non."""
import os
from contextlib import contextmanager

from playwright.sync_api import sync_playwright

SITE = "https://carlo-dev.lndo.site"
ADMIN = f"{SITE}/wp/wp-admin"
SHOTS = os.path.join(os.path.dirname(os.path.abspath(__file__)), "shots")


@contextmanager
def public_page(width=1440, height=1000):
    """Page d'un visiteur non connecté. page.errors liste les erreurs JS."""
    with sync_playwright() as p:
        browser = p.chromium.launch(executable_path="/usr/bin/chromium", args=["--no-sandbox"])
        context = browser.new_context(ignore_https_errors=True, viewport={"width": width, "height": height})
        page = context.new_page()
        page.errors = []
        page.on("console", lambda m: m.type == "error" and page.errors.append(m.text))
        page.on("pageerror", lambda e: page.errors.append(str(e)))
        try:
            yield page
        finally:
            browser.close()


@contextmanager
def admin_page(width=1440, height=1000):
    """Page connectée en admin (admin / admin, local uniquement). page.errors liste les erreurs JS."""
    with public_page(width, height) as page:
        page.goto(f"{SITE}/wp/wp-login.php")
        page.fill("#user_login", "admin")
        page.fill("#user_pass", "admin")
        page.click("#wp-submit", no_wait_after=True)
        # "commit" : le tableau de bord met longtemps à déclencher "load", inutile de l'attendre.
        page.wait_for_url("**/wp-admin/**", wait_until="commit", timeout=60000)
        yield page


def shot(page, name, **kwargs):
    """Capture d'écran dans tests/browser/shots/<name>.png ; renvoie son chemin."""
    os.makedirs(SHOTS, exist_ok=True)
    path = os.path.join(SHOTS, f"{name}.png")
    page.screenshot(path=path, **kwargs)
    return path
