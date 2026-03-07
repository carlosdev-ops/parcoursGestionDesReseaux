<?php
/**
 * 01_create_courses.php — Création des 8 cours du parcours dans Moodle
 *
 * Usage :
 *   php /path/to/scripts/01_create_courses.php --moodle=/var/www/moodle
 *   php /path/to/scripts/01_create_courses.php --moodle=/var/www/moodle --force
 *   php /path/to/scripts/01_create_courses.php --moodle=/var/www/moodle --course=1
 *
 * Options :
 *   --moodle   Chemin absolu vers la racine Moodle (obligatoire)
 *   --force    Supprime et recrée les cours qui existent déjà
 *   --course   Numéro du cours à créer uniquement (1-8), sinon tous
 *   --dry-run  Affiche ce qui serait fait sans modifier la base de données
 *
 * Prérequis : 00_setup_category.php doit avoir été exécuté.
 */

define('CLI_SCRIPT', true);

// --- Lecture des arguments ---
$opts = getopt('', ['moodle:', 'force', 'dry-run', 'course:']);

if (empty($opts['moodle'])) {
    echo "Erreur : --moodle est obligatoire.\n";
    echo "Usage : php 01_create_courses.php --moodle=/var/www/moodle\n";
    exit(1);
}

$moodle_path  = rtrim($opts['moodle'], '/');
$force        = isset($opts['force']);
$dry_run      = isset($opts['dry-run']);
$only_course  = isset($opts['course']) ? (int)$opts['course'] : null;

if (!file_exists($moodle_path . '/config.php')) {
    echo "Erreur : config.php introuvable dans {$moodle_path}\n";
    exit(1);
}

// --- Bootstrap Moodle ---
require_once($moodle_path . '/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');

require_once(__DIR__ . '/lib.php');

// --- Lecture de l'état de déploiement ---
$ref_file = __DIR__ . '/../.deploy_state.json';
if (!file_exists($ref_file)) {
    parcours_log("Erreur : .deploy_state.json introuvable. Exécuter d'abord 00_setup_category.php.", 'error');
    exit(1);
}
$state = json_decode(file_get_contents($ref_file), true);
$category_id = $state['category_id'] ?? null;

if (!$category_id) {
    parcours_log("Erreur : category_id manquant dans .deploy_state.json.", 'error');
    exit(1);
}

// Vérifier que la catégorie existe en base
if (!$DB->record_exists('course_categories', ['id' => $category_id])) {
    parcours_log("Erreur : la catégorie id={$category_id} n'existe plus en base. Relancer 00_setup_category.php.", 'error');
    exit(1);
}

parcours_log("=== Création des cours — Parcours Gestion des Réseaux ===");
parcours_log("Catégorie cible : id={$category_id} ({$state['category_name']})");
$dry_run && parcours_log("MODE DRY-RUN — aucune modification en base de données", 'warning');

// --- Création des cours ---
$courses_def = parcours_get_courses_definition();
$created_courses = $state['courses'] ?? [];
$errors = 0;

foreach ($courses_def as $def) {
    if ($only_course !== null && $def['seq'] !== $only_course) {
        continue;
    }

    parcours_log("---");
    parcours_log("Cours {$def['seq']}/8 — {$def['fullname']}");

    // Vérifier si le cours existe déjà
    $existing = $DB->get_record('course', ['idnumber' => $def['idnumber']]);

    if ($existing) {
        if ($force) {
            parcours_log("  Cours existant (id={$existing->id}) — --force activé, suppression...", 'warning');
            if (!$dry_run) {
                delete_course($existing->id, false);
                parcours_log("  Cours supprimé.", 'warning');
            }
            $existing = null;
        } else {
            parcours_log("  Cours existe déjà (id={$existing->id}) — ignoré. Utilisez --force pour recréer.", 'warning');
            $created_courses[$def['idnumber']] = [
                'id'        => $existing->id,
                'shortname' => $existing->shortname,
                'idnumber'  => $existing->idnumber,
                'status'    => 'already_exists',
            ];
            continue;
        }
    }

    if (!$dry_run) {
        try {
            // Construire l'objet cours
            $course_data = (object) [
                'category'      => $category_id,
                'fullname'      => $def['fullname'],
                'shortname'     => $def['shortname'],
                'idnumber'      => $def['idnumber'],
                'summary'       => $def['summary'],
                'summaryformat' => FORMAT_HTML,
                'format'        => 'topics',
                'numsections'   => $def['numsections'],
                'visible'       => 1,
                'lang'          => 'fr',
                'showgrades'    => 1,
                'showreports'   => 0,
                'newsitems'     => 0,
                'enablecompletion' => 1,
                'completionnotify' => 0,
                'sortorder'     => $def['seq'] * 10,
            ];

            $course = create_course($course_data);
            parcours_log("  Cours créé — id={$course->id}, shortname={$course->shortname}", 'success');

            $created_courses[$def['idnumber']] = [
                'id'        => $course->id,
                'shortname' => $course->shortname,
                'idnumber'  => $course->idnumber,
                'fullname'  => $course->fullname,
                'seq'       => $def['seq'],
                'status'    => 'created',
            ];

        } catch (Exception $e) {
            parcours_log("  ERREUR : " . $e->getMessage(), 'error');
            $errors++;
        }
    } else {
        parcours_log("  [DRY-RUN] Aurait créé : {$def['fullname']} (idnumber={$def['idnumber']})", 'info');
    }
}

// Sauvegarder l'état mis à jour
if (!$dry_run) {
    $state['courses'] = $created_courses;
    $state['courses_deployed_at'] = date('c');
    file_put_contents($ref_file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    parcours_log("État mis à jour dans .deploy_state.json", 'info');
}

parcours_log("---");
if ($errors > 0) {
    parcours_log("=== Étape 01 terminée avec {$errors} erreur(s) ===", 'error');
    exit(1);
} else {
    parcours_log("=== Étape 01 terminée avec succès ===", 'success');
}
