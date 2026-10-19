import { resolve } from "node:path";
import tailwindcss from "@tailwindcss/vite";
import { defineConfig } from "vite";

/**
 * Carlo (inc/assets.php) déduit les chemins d'assets de l'URL du thème :
 * "app/themes/<thème>/js/main.js". La racine de Vite est donc web/ :
 * - en développement, le serveur répond sous ces mêmes chemins, et
 *   /@vite/client est servi à la racine, là où Carlo l'injecte ;
 * - en production, les clés du manifest sont ces mêmes chemins relatifs,
 *   que Carlo recherche telles quelles.
 */
const root = resolve(import.meta.dirname, "web");
const theme = "app/themes/carlo-test";

export default defineConfig({
  root,
  plugins: [tailwindcss()],
  server: {
    host: true,
    port: 80,
    allowedHosts: true,
    cors: true,
    // Lando expose le serveur en https sur le port 443 : c'est là que le client HMR doit se connecter.
    hmr: { protocol: "wss", clientPort: 443 },
    // Inutile (et coûteux sur un volume Docker) de surveiller le cœur WordPress et les plugins.
    watch: { ignored: ["**/wp/**", "**/plugins/**", "**/uploads/**"] },
  },
  build: {
    manifest: true,
    outDir: resolve(root, theme, "dist"),
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(root, theme, "js/main.js"),
    },
  },
});
