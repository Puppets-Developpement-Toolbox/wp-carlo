// Le CSS passe par ce point d'entrée : c'est ce qui le met dans le manifest de production.
import "../css/global.css";

// Repère visible dans l'inspecteur : prouve que les assets sont bien chargés,
// et par quel chemin (serveur Vite ou build).
document.documentElement.dataset.vite = import.meta.env.DEV ? "dev" : "build";
