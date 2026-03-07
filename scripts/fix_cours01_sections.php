<?php
/**
 * fix_cours01_sections.php — Design original restauré + uniformisé sur toutes les sections
 *
 * Usage :
 *   sudo -u www-data php scripts/fix_cours01_sections.php --moodle=/var/www/moodle
 *   sudo -u www-data php scripts/fix_cours01_sections.php --moodle=/var/www/moodle --dry-run
 */

define('CLI_SCRIPT', true);

$opts = getopt('', ['moodle:', 'dry-run']);
if (empty($opts['moodle'])) { echo "Usage: php fix_cours01_sections.php --moodle=/var/www/moodle\n"; exit(1); }

$moodle_path = rtrim($opts['moodle'], '/');
$dry_run     = isset($opts['dry-run']);

if (!file_exists($moodle_path . '/config.php')) { echo "config.php introuvable.\n"; exit(1); }

require_once($moodle_path . '/config.php');
require_once($CFG->libdir  . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lib.php');

$courseid = 2;
$course   = get_course($courseid);

parcours_log("=== Restauration design original — Cours 1 ===");
$dry_run && parcours_log("MODE DRY-RUN", 'warning');

// ============================================================
// Fonction : HTML d'une section module (1–6)
// Palette identique à la Section 0 originale :
//   bg-primary   = objectif (apprentissage)
//   bg-info      = outils (ressources)
//   bg-warning   = activités (ce que l'étudiant fait)
// ============================================================
function module_html(int $num, string $title, string $objectif, string $outils, string $labo): string {
    $n = $num;
    return <<<HTML
<div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
  <div class="fs-2 me-1">&#x1F4D6;</div>
  <div>
    <h4 class="alert-heading mb-1">Module {$n} — {$title}</h4>
    <p class="mb-0">Suivez la lecture guidée, complétez le quiz formatif, puis réalisez le travail pratique avant de passer au module suivant.</p>
  </div>
</div>

<div class="row g-3 mb-3">

  <div class="col-md-8">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header bg-primary text-white fw-semibold">
        &#x1F3AF; Objectif d'apprentissage
      </div>
      <div class="card-body">
        <p class="mb-0">{$objectif}</p>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="d-flex flex-column gap-3 h-100">

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-info text-white fw-semibold">
          &#x1F6E0; Outils utilisés
        </div>
        <div class="card-body">
          <p class="mb-0">{$outils}</p>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-warning text-dark fw-semibold">
          &#x1F4CB; Activités
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li class="mb-2">&#x1F4D5; Livre — Lecture guidée</li>
            <li class="mb-2">&#x2753; Quiz formatif — 15 questions</li>
            <li>&#x1F9EA; Travail pratique — {$labo}</li>
          </ul>
        </div>
      </div>

    </div>
  </div>

</div>
HTML;
}

// ============================================================
// Données par module (sections 1–6)
// ============================================================
$modules = [
    1 => [
        'title'    => 'Modèle OSI — Les 7 couches',
        'objectif' => 'Décrire précisément le rôle de chacune des 7 couches OSI, nommer la PDU associée à chaque couche, et identifier les équipements réseau (hub, switch, routeur) selon la couche à laquelle ils opèrent.',
        'outils'   => 'Wireshark — observation du trafic en direct pour valider les couches traversées.',
        'labo'     => 'Capturer du trafic et identifier les couches OSI dans Wireshark',
    ],
    2 => [
        'title'    => 'Modèle TCP/IP et encapsulation',
        'objectif' => 'Comprendre la correspondance entre le modèle OSI et le modèle TCP/IP, maîtriser le mécanisme d\'encapsulation et de désencapsulation des données, et décoder les en-têtes de protocoles dans une capture réseau.',
        'outils'   => 'Wireshark — décoder un paquet HTTP et une requête DNS en temps réel.',
        'labo'     => 'Analyser l\'encapsulation d\'un paquet HTTP/DNS avec Wireshark',
    ],
    3 => [
        'title'    => 'Médias et équipements réseau',
        'objectif' => 'Distinguer les types de supports de transmission (UTP, fibre optique, coaxial, Wi-Fi), identifier les équipements réseau (hub, switch, routeur, point d\'accès) et comprendre leurs différences opérationnelles.',
        'outils'   => 'Cisco Packet Tracer — construire et tester une topologie avec différents types de médias.',
        'labo'     => 'Construire une topologie multi-médias dans Cisco Packet Tracer',
    ],
    4 => [
        'title'    => 'Protocoles de couche application',
        'objectif' => 'Connaître le rôle et le port standard des protocoles DNS, DHCP, HTTP/HTTPS, FTP, SSH, SMTP, NTP et SNMP. Identifier ces protocoles par leur comportement dans une capture réseau.',
        'outils'   => 'Wireshark — filtres par protocole, analyse de requêtes DNS et HTTP.',
        'labo'     => 'Analyser des échanges DNS, DHCP et HTTP par filtres Wireshark',
    ],
    5 => [
        'title'    => 'Outils de diagnostic fondamentaux',
        'objectif' => 'Maîtriser les commandes de diagnostic réseau — ping, traceroute, nslookup, dig, netstat, ss, arp, ip addr — et appliquer une méthodologie de dépannage structurée (approche bottom-up).',
        'outils'   => 'Terminal Linux — sessions de diagnostic en ligne de commande (local ou webminal.org).',
        'labo'     => 'Exécuter une séquence complète de diagnostic réseau en ligne de commande',
    ],
    6 => [
        'title'    => 'Documentation et nomenclature NOC',
        'objectif' => 'Appliquer les standards de documentation réseau NOC : convention de nommage des équipements, schémas de topologie physique et logique, inventaire réseau (IPAM), plan d\'adressage IP et runbooks.',
        'outils'   => 'draw.io (app.diagrams.net) — créer des diagrammes de topologie réseau selon les standards.',
        'labo'     => 'Documenter une topologie réseau complète selon les standards NOC avec draw.io',
    ],
];

// ============================================================
// Section 0 — design original restauré + prérequis réalistes
// ============================================================
$section0_html = <<<'HTML'
<div class="course-welcome-section">

  <div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
    <div class="fs-2 me-2">📡</div>
    <div>
      <h3 class="alert-heading mb-1">Bienvenue dans le Cours 1 — Fondements des réseaux</h3>
      <p class="mb-0">Point de départ du Parcours Gestion des Réseaux. Vous allez construire le cadre conceptuel qui donne du sens à tout ce que vous branchez et configurez au quotidien.</p>
    </div>
  </div>

  <div class="row g-3 mb-4">

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
              <tr class="table-light"><td class="ps-3 text-muted">Cadence</td><td>~5 h / semaine</td></tr>
              <tr><td class="ps-3 text-muted">Langue</td><td>Français</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

  <div class="row g-3 mb-4">

    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-info text-white fw-semibold">
          Plan du cours — 6 modules
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item"><strong>1.</strong> Modèle OSI — les 7 couches</li>
          <li class="list-group-item"><strong>2.</strong> Modèle TCP/IP &amp; encapsulation</li>
          <li class="list-group-item"><strong>3.</strong> Médias et équipements réseau</li>
          <li class="list-group-item"><strong>4.</strong> Protocoles de couche application</li>
          <li class="list-group-item"><strong>5.</strong> Outils de diagnostic fondamentaux</li>
          <li class="list-group-item"><strong>6.</strong> Documentation et nomenclature NOC</li>
        </ul>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-warning text-dark fw-semibold">
          &#x26A0; Prérequis — Préparer votre environnement
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">Ce cours utilise 4 outils. Une alternative sans droits admin est indiquée pour chaque étape.</p>
          <ul class="list-unstyled mb-0">
            <li class="mb-2">
              &#x1F50D; <strong>Wireshark</strong> —
              <a href="https://www.wireshark.org" target="_blank">wireshark.org</a>
              <small class="text-muted d-block ps-4">Sans droits admin : <a href="https://www.wireshark.org/download.html" target="_blank">Wireshark Portable</a> (clé USB)</small>
            </li>
            <li class="mb-2">
              &#x1F4E6; <strong>Cisco Packet Tracer</strong> —
              <a href="https://skillsforall.com" target="_blank">Cisco Skills for All</a> (navigateur, gratuit)
              <small class="text-muted d-block ps-4">Avec droits admin : appli bureau via <a href="https://www.netacad.com" target="_blank">netacad.com</a></small>
            </li>
            <li class="mb-2">
              &#x1F5A5; <strong>Terminal Linux</strong> — natif sur Mac/Linux
              <small class="text-muted d-block ps-4">Windows sans droits admin : <a href="https://webminal.org" target="_blank">webminal.org</a> ou Git Bash</small>
            </li>
            <li>
              &#x1F5FA; <strong>draw.io</strong> —
              <a href="https://app.diagrams.net" target="_blank">app.diagrams.net</a> (navigateur, aucune installation)
            </li>
          </ul>
        </div>
      </div>
    </div>

  </div>

  <div class="alert alert-info mb-0" role="alert">
    <strong>&#x1F4CB; Test diagnostique ci-dessous</strong> — Complétez-le avant de commencer le Module 1.
    Il évalue vos connaissances actuelles et n'affecte pas votre note finale. Les résultats sont disponibles immédiatement.
  </div>

</div>
HTML;

// ============================================================
// Appliquer les résumés à chaque section
// ============================================================

// Section 0
$sec = $DB->get_record('course_sections', ['course' => $courseid, 'section' => 0]);
parcours_log("Section 0 — Bienvenue & Diagnostic");
if (!$dry_run) {
    $sec->name          = 'Bienvenue & Diagnostic';
    $sec->summary       = $section0_html;
    $sec->summaryformat = FORMAT_HTML;
    $DB->update_record('course_sections', $sec);
    parcours_log("  OK", 'success');
}

// Sections 1–6
foreach ($modules as $snum => $data) {
    $html = module_html($snum, $data['title'], $data['objectif'], $data['outils'], $data['labo']);
    $sec  = $DB->get_record('course_sections', ['course' => $courseid, 'section' => $snum]);
    if (!$sec) { parcours_log("  Section {$snum} introuvable", 'warning'); continue; }
    parcours_log("Section {$snum} — {$data['title']}");
    if (!$dry_run) {
        $sec->summary       = $html;
        $sec->summaryformat = FORMAT_HTML;
        $DB->update_record('course_sections', $sec);
        parcours_log("  OK", 'success');
    }
}

// ============================================================
// Purge des caches
// ============================================================
if (!$dry_run) {
    rebuild_course_cache($courseid, true);
    purge_all_caches();
    parcours_log("\nCaches purgés.", 'info');
}

parcours_log("\n=== Terminé ===", 'success');
