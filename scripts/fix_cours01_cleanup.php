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
<div class="border-start border-primary border-4 ps-3 mb-4">
  <h4 class="fw-semibold mb-1">Cours 1 — Fondements des réseaux et modèles OSI/TCP-IP</h4>
  <p class="text-muted mb-0">
    Point de départ du Parcours Gestion des Réseaux. Vous allez construire le cadre conceptuel
    qui donne du sens à ce que vous branchez et configurez au quotidien.
  </p>
</div>

<div class="row g-3 mb-4">

  <div class="col-lg-7">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header bg-primary text-white fw-semibold py-2">
        Objectifs du cours
      </div>
      <div class="card-body">
        <ul class="list-unstyled mb-0">
          <li class="d-flex gap-2 mb-2"><span class="text-primary fw-bold">&#8250;</span> Décrire les 7 couches OSI et les 4 couches TCP/IP</li>
          <li class="d-flex gap-2 mb-2"><span class="text-primary fw-bold">&#8250;</span> Identifier le rôle de chaque équipement réseau selon sa couche</li>
          <li class="d-flex gap-2 mb-2"><span class="text-primary fw-bold">&#8250;</span> Capturer et analyser du trafic réseau avec Wireshark</li>
          <li class="d-flex gap-2"><span class="text-primary fw-bold">&#8250;</span> Documenter une topologie réseau selon les standards NOC</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="row g-3">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-primary text-white fw-semibold py-2">
            Informations
          </div>
          <div class="card-body py-2">
            <dl class="row mb-0" style="font-size:.9em">
              <dt class="col-5 fw-normal text-muted">Durée</dt>       <dd class="col-7">8 semaines</dd>
              <dt class="col-5 fw-normal text-muted">Charge</dt>      <dd class="col-7">~5 h/semaine</dd>
              <dt class="col-5 fw-normal text-muted">Niveau</dt>      <dd class="col-7">Introductif</dd>
              <dt class="col-5 fw-normal text-muted mb-0">Équivalent</dt><dd class="col-7 mb-0">DEC 420-1A3</dd>
            </dl>
          </div>
        </div>
      </div>
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-primary text-white fw-semibold py-2">
            Plan — 6 modules
          </div>
          <div class="card-body py-2">
            <ol class="mb-0 ps-3" style="font-size:.9em;column-count:2">
              <li class="mb-1">Modèle OSI</li>
              <li class="mb-1">Modèle TCP/IP</li>
              <li class="mb-1">Médias et équipements</li>
              <li class="mb-1">Protocoles applicatifs</li>
              <li class="mb-1">Outils de diagnostic</li>
              <li>Documentation NOC</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-primary text-white fw-semibold py-2">
    Prérequis — Préparer votre environnement de travail
  </div>
  <div class="card-body">
    <p class="text-muted mb-3" style="font-size:.9em">
      Ce cours utilise quatre outils logiciels. Suivez les étapes ci-dessous dans l'ordre.
      <strong>Si vous n'avez pas les droits administrateur</strong> (ordinateur de travail, poste de l'entreprise
      ou de l'école), une alternative sans installation est indiquée pour chaque outil.
    </p>
    <div class="row g-3">

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 1 &mdash; Wireshark <span class="text-muted fw-normal">(analyseur de paquets)</span></p>
        <p class="text-muted mb-1" style="font-size:.875em">Utilisé pour capturer et observer le trafic réseau en temps réel.</p>
        <ul style="font-size:.875em" class="mb-0">
          <li><strong>Avec droits admin :</strong> Installer depuis
            <a href="https://www.wireshark.org" target="_blank">wireshark.org</a> (gratuit, Windows/Mac/Linux).</li>
          <li><strong>Sans droits admin :</strong> Télécharger
            <a href="https://www.wireshark.org/download.html" target="_blank">Wireshark Portable</a>
            — aucune installation, exécutable depuis une clé USB ou un dossier local.</li>
          <li><strong>Sur poste de laboratoire :</strong> Généralement déjà installé — vérifiez avec votre responsable.</li>
        </ul>
      </div>

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 2 &mdash; Cisco Packet Tracer <span class="text-muted fw-normal">(simulateur réseau)</span></p>
        <p class="text-muted mb-1" style="font-size:.875em">Pour construire et tester des topologies réseau sans équipement physique.</p>
        <ul style="font-size:.875em" class="mb-0">
          <li><strong>Option recommandée (aucune installation) :</strong> Version navigateur via
            <a href="https://skillsforall.com" target="_blank">Cisco Skills for All</a>
            — compte gratuit, aucun logiciel à installer.</li>
          <li><strong>Avec droits admin :</strong> Application de bureau depuis
            <a href="https://www.netacad.com" target="_blank">netacad.com</a> après création d'un compte.</li>
        </ul>
      </div>

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 3 &mdash; Terminal Linux <span class="text-muted fw-normal">(commandes de diagnostic)</span></p>
        <p class="text-muted mb-1" style="font-size:.875em">Pour les commandes ping, traceroute, dig, ss, etc.</p>
        <ul style="font-size:.875em" class="mb-0">
          <li><strong>Mac ou Linux :</strong> Terminal déjà disponible, aucune installation requise.</li>
          <li><strong>Windows avec droits admin :</strong> Activer WSL2, puis Ubuntu depuis le Microsoft Store.</li>
          <li><strong>Windows sans droits admin :</strong>
            <a href="https://webminal.org" target="_blank">webminal.org</a> (terminal Linux en ligne)
            ou Git Bash si déjà installé sur votre poste.</li>
        </ul>
      </div>

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 4 &mdash; draw.io <span class="text-muted fw-normal">(schémas de topologie)</span></p>
        <p class="text-muted mb-1" style="font-size:.875em">Pour créer des schémas réseau selon les standards NOC.</p>
        <ul style="font-size:.875em" class="mb-0">
          <li><strong>Aucune installation requise :</strong>
            <a href="https://app.diagrams.net" target="_blank">app.diagrams.net</a>
            dans votre navigateur — fichiers sauvegardés localement ou sur Google Drive.</li>
        </ul>
        <p class="fw-semibold mt-3 mb-1">Un doute sur votre configuration ?</p>
        <p style="font-size:.875em" class="mb-0">Contactez votre responsable de formation. La plupart des exercices
          peuvent aussi être réalisés sur les postes du laboratoire informatique.</p>
      </div>

    </div>
  </div>
</div>

<div class="alert alert-primary border-0 mb-0" role="alert">
  <strong>Avant de commencer le Module 1</strong> — complétez le test diagnostique ci-dessous.
  Il évalue vos connaissances actuelles, n'affecte pas votre note et donne des résultats immédiats.
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
