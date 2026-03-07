<?php
/**
 * fix_cours01_cleanup.php — Nettoyage des doublons + refonte esthétique Section 0
 * Cours 1 — Fondements des réseaux & modèles OSI/TCP-IP
 *
 * Usage :
 *   sudo -u www-data php scripts/fix_cours01_cleanup.php --moodle=/var/www/moodle
 *   sudo -u www-data php scripts/fix_cours01_cleanup.php --moodle=/var/www/moodle --dry-run
 */

define('CLI_SCRIPT', true);

$opts = getopt('', ['moodle:', 'dry-run']);
if (empty($opts['moodle'])) { echo "Usage: php fix_cours01_cleanup.php --moodle=/var/www/moodle\n"; exit(1); }

$moodle_path = rtrim($opts['moodle'], '/');
$dry_run     = isset($opts['dry-run']);

if (!file_exists($moodle_path . '/config.php')) { echo "config.php introuvable.\n"; exit(1); }

require_once($moodle_path . '/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');

require_once(__DIR__ . '/lib.php');

$courseid = 2; // Cours 1 — idnumber RESEAUX-01-OSI
$course   = get_course($courseid);

parcours_log("=== Nettoyage doublons + refonte Section 0 — Cours 1 ===");
$dry_run && parcours_log("MODE DRY-RUN", 'warning');

// ============================================================
// ÉTAPE 1 : SUPPRIMER LES DOUBLONS DANS TOUTES LES SECTIONS
// Règle : pour chaque section, garder le cmid le plus élevé par type d'activité
// ============================================================
parcours_log("\n--- Étape 1 : Suppression des doublons ---");

$deleted_total = 0;

for ($s = 0; $s <= 7; $s++) {

    // Récupérer la section DB
    $section_rec = $DB->get_record('course_sections', ['course' => $courseid, 'section' => $s]);
    if (!$section_rec) continue;

    // Récupérer tous les modules de cette section, groupés par nom de module
    $cms = $DB->get_records_sql(
        "SELECT cm.id as cmid, m.name as modname, cm.instance, cm.idnumber
           FROM {course_modules} cm
           JOIN {modules} m ON m.id = cm.module
          WHERE cm.course = :course AND cm.section = :section
          ORDER BY cm.id ASC",
        ['course' => $courseid, 'section' => $section_rec->id]
    );

    // Grouper par (modname) et pour les labels aussi par position approximative
    // On garde le cmid le plus ÉLEVÉ (le plus récent) de chaque type+nom
    $by_name = [];
    foreach ($cms as $cm) {
        // Récupérer le nom de l'activité pour grouper intelligemment
        $name_rec = $DB->get_record($cm->modname, ['id' => $cm->instance], 'id, name');
        $key      = $cm->modname . '::' . ($name_rec->name ?? $cm->instance);
        if (!isset($by_name[$key])) {
            $by_name[$key] = [];
        }
        $by_name[$key][] = (int)$cm->cmid;
    }

    foreach ($by_name as $key => $cmids) {
        if (count($cmids) <= 1) continue;

        // Garder le plus récent (max cmid)
        $keep    = max($cmids);
        $to_del  = array_filter($cmids, fn($id) => $id !== $keep);

        parcours_log("  Section {$s} [{$key}] : garder cmid={$keep}, supprimer " . implode(',', $to_del), 'warning');

        if (!$dry_run) {
            foreach ($to_del as $cmid) {
                course_delete_module($cmid, false); // false = suppression synchrone immédiate
                $deleted_total++;
            }
        } else {
            $deleted_total += count($to_del);
        }
    }
}

parcours_log("  Total supprimé : {$deleted_total} modules", $deleted_total > 0 ? 'success' : 'info');

// ============================================================
// ÉTAPE 2 : REFONTE ESTHÉTIQUE DE LA SECTION 0
// Nouvelle approche : tout dans le résumé de section + label supprimé
// ============================================================
parcours_log("\n--- Étape 2 : Refonte esthétique Section 0 ---");

$section0_summary = <<<'HTML'
<div class="course-welcome-section">

  <!-- Bandeau de bienvenue -->
  <div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
    <div class="fs-2 me-2">📡</div>
    <div>
      <h3 class="alert-heading mb-1">Bienvenue dans le Cours 1 — Fondements des réseaux</h3>
      <p class="mb-0">Point de départ du Parcours Gestion des Réseaux. Vous allez construire le cadre conceptuel qui donne du sens à tout ce que vous branchez au quotidien.</p>
    </div>
  </div>

  <div class="row g-3 mb-4">

    <!-- Objectifs du cours -->
    <div class="col-md-7">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold">
          Objectifs d'apprentissage
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li class="mb-2">&#x2705; Décrire et distinguer les 7 couches OSI et les 4 couches TCP/IP</li>
            <li class="mb-2">&#x2705; Identifier le rôle des équipements réseau à chaque couche</li>
            <li class="mb-2">&#x2705; Capturer et analyser du trafic réseau avec <strong>Wireshark</strong></li>
            <li class="mb-0">&#x2705; Documenter une topologie réseau selon les standards NOC</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Informations du cours -->
    <div class="col-md-5">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header bg-secondary text-white fw-semibold">
          Informations
        </div>
        <div class="card-body p-0">
          <table class="table table-sm table-borderless mb-0">
            <tbody>
              <tr><td class="ps-3 text-muted" style="width:40%">Durée</td><td><strong>8 semaines · ~40 h</strong></td></tr>
              <tr class="table-light"><td class="ps-3 text-muted">Niveau</td><td><span class="badge bg-success">Introductif</span></td></tr>
              <tr><td class="ps-3 text-muted">Équivalent DEC</td><td><strong>420-1A3</strong></td></tr>
              <tr class="table-light"><td class="ps-3 text-muted">Cadence</td><td>~5 h/semaine</td></tr>
              <tr><td class="ps-3 text-muted">Langue</td><td>Français</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">

    <!-- Plan des modules -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-info text-white fw-semibold">
          Plan du cours — 6 modules
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>1.</strong> Modèle OSI — les 7 couches</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>2.</strong> Modèle TCP/IP &amp; encapsulation</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>3.</strong> Médias et équipements réseau</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>4.</strong> Protocoles de couche application</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>5.</strong> Outils de diagnostic fondamentaux</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>6.</strong> Documentation et nomenclature NOC</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Prérequis matériels -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-warning text-dark fw-semibold">
          &#x26A0; Prérequis avant de commencer
        </div>
        <div class="card-body">
          <p class="text-muted small mb-2">Assurez-vous d'avoir installé les outils suivants :</p>
          <ul class="list-unstyled mb-0">
            <li class="mb-2">&#x1F4BB; PC/laptop avec <strong>8 Go RAM minimum</strong> (Windows, Linux ou Mac)</li>
            <li class="mb-2">&#x1F50D; <a href="https://www.wireshark.org" target="_blank">Wireshark</a> — analyseur de paquets</li>
            <li class="mb-2">&#x1F4E6; <a href="https://www.netacad.com" target="_blank">Cisco Packet Tracer</a> — gratuit via NetAcad</li>
            <li class="mb-0">&#x1F5A5; Accès à un <strong>terminal Linux</strong> (natif, WSL2 ou VM)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Note sur le test diagnostique -->
  <div class="alert alert-info mb-0" role="alert">
    <strong>&#x1F4CB; Test diagnostique ci-dessous</strong> — Complétez-le avant de commencer le Module 1.
    Il évalue vos connaissances actuelles et n'affecte pas votre note finale. Les résultats sont disponibles immédiatement.
  </div>

</div>
HTML;

// Mettre à jour le résumé de la section 0
$section0 = $DB->get_record('course_sections', ['course' => $courseid, 'section' => 0]);
$section0->name          = 'Bienvenue & Diagnostic';
$section0->summary       = $section0_summary;
$section0->summaryformat = FORMAT_HTML;

if (!$dry_run) {
    $DB->update_record('course_sections', $section0);
    parcours_log("  Résumé Section 0 mis à jour.", 'success');
} else {
    parcours_log("  [DRY-RUN] Mettrait à jour le résumé de la Section 0.", 'info');
}

// ============================================================
// ÉTAPE 3 : SUPPRIMER LE LABEL (contenu maintenant dans le résumé)
// + Améliorer l'intro du quiz diagnostique
// ============================================================
parcours_log("\n--- Étape 3 : Suppression du label + amélioration quiz ---");

// Trouver le label restant dans la section 0 après nettoyage
$section0_rec = $DB->get_record('course_sections', ['course' => $courseid, 'section' => 0]);
$remaining_cms = $DB->get_records_sql(
    "SELECT cm.id as cmid, m.name as modname, cm.instance
       FROM {course_modules} cm
       JOIN {modules} m ON m.id = cm.module
      WHERE cm.course = :course AND cm.section = :section
      ORDER BY cm.id",
    ['course' => $courseid, 'section' => $section0_rec->id]
);

foreach ($remaining_cms as $cm) {
    if ($cm->modname === 'label') {
        parcours_log("  Suppression du label cmid={$cm->cmid} (contenu intégré dans le résumé de section).", 'warning');
        if (!$dry_run) {
            course_delete_module((int)$cm->cmid, false);
        }
    }

    if ($cm->modname === 'quiz') {
        // Améliorer l'intro du quiz diagnostique
        $new_intro = <<<'HTML'
<div class="card border-0 shadow-sm mb-2">
  <div class="card-body py-3">
    <div class="d-flex align-items-center gap-3">
      <div class="fs-3">&#x1F4CA;</div>
      <div>
        <p class="mb-1">Ce test évalue vos <strong>connaissances actuelles</strong> en réseaux.
        Il n'est <strong class="text-success">pas noté</strong> dans votre dossier — son but est
        de vous situer et d'identifier les notions à consolider avant de commencer.</p>
        <p class="mb-0 text-muted">
          <span class="badge bg-secondary me-1">20 questions</span>
          <span class="badge bg-secondary me-1">30 minutes</span>
          <span class="badge bg-success">Résultats immédiats</span>
        </p>
      </div>
    </div>
  </div>
</div>
HTML;
        parcours_log("  Mise à jour de l'intro du quiz diagnostique cmid={$cm->cmid}.", 'info');
        if (!$dry_run) {
            $DB->set_field('quiz', 'intro', $new_intro, ['id' => $cm->instance]);
            $DB->set_field('quiz', 'introformat', FORMAT_HTML, ['id' => $cm->instance]);
        }
    }
}

// ============================================================
// ÉTAPE 4 : PURGE DES CACHES
// ============================================================
if (!$dry_run) {
    rebuild_course_cache($courseid, true);
    purge_all_caches();
    parcours_log("\nCaches Moodle purgés.", 'info');
}

// ============================================================
// VÉRIFICATION FINALE
// ============================================================
parcours_log("\n--- Vérification finale : modules par section ---");
for ($s = 0; $s <= 7; $s++) {
    $sec_rec = $DB->get_record('course_sections', ['course' => $courseid, 'section' => $s]);
    if (!$sec_rec) continue;
    $cms = $DB->get_records_sql(
        "SELECT cm.id, m.name as modname FROM {course_modules} cm
           JOIN {modules} m ON m.id = cm.module
          WHERE cm.course = :course AND cm.section = :section ORDER BY cm.id",
        ['course' => $courseid, 'section' => $sec_rec->id]
    );
    $types = [];
    foreach ($cms as $c) { $types[] = $c->modname; }
    parcours_log(sprintf("  Section %-2d [%-28s] : %d modules — %s",
        $s, $sec_rec->name, count($cms), implode(', ', $types)));
}

parcours_log("\n=== Correction terminée ===", 'success');
