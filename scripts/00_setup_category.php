<?php
/**
 * 00_setup_category.php — Création de la catégorie Moodle du parcours
 *
 * Usage :
 *   php /path/to/scripts/00_setup_category.php --moodle=/var/www/moodle
 *   php /path/to/scripts/00_setup_category.php --moodle=/var/www/moodle --force
 *
 * Options :
 *   --moodle   Chemin absolu vers la racine Moodle (obligatoire)
 *   --force    Supprime et recrée la catégorie si elle existe déjà
 *   --dry-run  Affiche ce qui serait fait sans modifier la base de données
 */

define('CLI_SCRIPT', true);

// --- Lecture des arguments ---
$opts = getopt('', ['moodle:', 'force', 'dry-run']);

if (empty($opts['moodle'])) {
    echo "Erreur : --moodle est obligatoire.\n";
    echo "Usage : php 00_setup_category.php --moodle=/var/www/moodle\n";
    exit(1);
}

$moodle_path = rtrim($opts['moodle'], '/');
$force       = isset($opts['force']);
$dry_run     = isset($opts['dry-run']);

if (!file_exists($moodle_path . '/config.php')) {
    echo "Erreur : config.php introuvable dans {$moodle_path}\n";
    exit(1);
}

// --- Bootstrap Moodle ---
require_once($moodle_path . '/config.php');
require_once($CFG->libdir . '/clilib.php');
// core_course_category est autoloadée en Moodle 4.x (plus de coursecatlib.php)

// Charger les utilitaires du parcours
require_once(__DIR__ . '/lib.php');

// --- Exécution ---
parcours_log("=== Setup catégorie — Parcours Gestion des Réseaux ===");
$dry_run && parcours_log("MODE DRY-RUN — aucune modification en base de données", 'warning');

$def = parcours_get_category_definition();

// Vérifier si la catégorie existe déjà
$existing = $DB->get_record('course_categories', ['idnumber' => $def['idnumber']]);

if ($existing) {
    if ($force) {
        parcours_log("Catégorie existante trouvée (id={$existing->id}) — --force activé, suppression...", 'warning');
        if (!$dry_run) {
            $cat = core_course_category::get($existing->id);
            $cat->delete_full(false);
            parcours_log("Catégorie supprimée.", 'warning');
        }
        $existing = null;
    } else {
        parcours_log("La catégorie '{$def['name']}' existe déjà (id={$existing->id}, idnumber={$existing->idnumber}).", 'warning');
        parcours_log("Utilisez --force pour la recréer. Sortie sans modification.", 'warning');
        exit(0);
    }
}

// Créer la catégorie
parcours_log("Création de la catégorie : {$def['name']}");

if (!$dry_run) {
    $data = (object) [
        'name'        => $def['name'],
        'idnumber'    => $def['idnumber'],
        'description' => $def['description'],
        'descriptionformat' => FORMAT_HTML,
        'parent'      => $def['parent'],
        'visible'     => $def['visible'],
    ];

    $category = core_course_category::create($data);
    parcours_log("Categorie creee avec succes — id={$category->id}, idnumber={$category->idnumber}", 'success');

    // Écrire l'ID dans un fichier de référence pour les étapes suivantes
    $ref_file = __DIR__ . '/../.deploy_state.json';
    $state = file_exists($ref_file) ? json_decode(file_get_contents($ref_file), true) : [];
    $state['category_id']       = $category->id;
    $state['category_idnumber'] = $category->idnumber;
    $state['category_name']     = $category->name;
    $state['deployed_at']       = date('c');
    $state['moodle_path']       = $moodle_path;
    file_put_contents($ref_file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    parcours_log("État sauvegardé dans .deploy_state.json", 'info');
} else {
    parcours_log("[DRY-RUN] Aurait créé : {$def['name']} (idnumber={$def['idnumber']})", 'info');
}

parcours_log("=== Étape 00 terminée ===", 'success');
