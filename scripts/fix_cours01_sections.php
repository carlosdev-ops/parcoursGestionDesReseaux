<?php
/**
 * fix_cours01_sections.php — Design professionnel uniforme pour toutes les sections du Cours 1
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

parcours_log("=== Uniformisation design professionnel — Cours 1 ===");
$dry_run && parcours_log("MODE DRY-RUN", 'warning');

// ============================================================
// Fonction utilitaire : génère le HTML d'une section module (1–6)
// ============================================================
function module_html(int $num, string $title, string $objectif, string $outils, string $labo): string {
    $n = $num;
    return <<<HTML
<div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-2">
  <span class="badge bg-primary px-3 py-2" style="font-size:1rem;letter-spacing:.03em;white-space:nowrap">Module {$n}</span>
  <h4 class="fw-semibold mb-0">{$title}</h4>
</div>

<div class="row g-3">

  <div class="col-lg-8">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header bg-primary text-white fw-semibold py-2 px-3">
        Objectif d'apprentissage
      </div>
      <div class="card-body px-4 py-3">
        <p class="mb-0">{$objectif}</p>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="d-flex flex-column gap-3 h-100">

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold py-2 px-3">
          Outils utilisés
        </div>
        <div class="card-body px-4 py-3">
          <p class="mb-0">{$outils}</p>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold py-2 px-3">
          Activités
        </div>
        <div class="card-body px-4 py-3">
          <ul class="mb-0 ps-3">
            <li class="mb-2">Livre — Lecture guidée</li>
            <li class="mb-2">Quiz formatif — 15 questions</li>
            <li>Travail pratique — {$labo}</li>
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
// Section 0 — Bienvenue & Diagnostic (design unifié)
// ============================================================
$section0_html = <<<'HTML'
<div class="border-start border-primary border-4 ps-4 mb-4 pb-2">
  <h4 class="fw-semibold mb-2">Cours 1 — Fondements des réseaux et modèles OSI/TCP-IP</h4>
  <p class="text-secondary mb-0">
    Point de départ du Parcours Gestion des Réseaux. Vous allez construire le cadre conceptuel
    qui donne du sens à chaque équipement que vous branchez et chaque commande que vous exécutez.
  </p>
</div>

<div class="row g-3 mb-4">

  <div class="col-lg-7">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header bg-primary text-white fw-semibold py-2 px-3">
        Objectifs du cours
      </div>
      <div class="card-body px-4 py-3">
        <ul class="list-unstyled mb-0">
          <li class="d-flex gap-2 mb-3"><span class="text-primary fw-bold mt-1">&#8250;</span><span>Décrire les 7 couches OSI et les 4 couches TCP/IP et expliquer le rôle de chacune</span></li>
          <li class="d-flex gap-2 mb-3"><span class="text-primary fw-bold mt-1">&#8250;</span><span>Identifier les équipements réseau selon leur couche d'opération</span></li>
          <li class="d-flex gap-2 mb-3"><span class="text-primary fw-bold mt-1">&#8250;</span><span>Capturer et analyser du trafic réseau en temps réel avec Wireshark</span></li>
          <li class="d-flex gap-2"><span class="text-primary fw-bold mt-1">&#8250;</span><span>Documenter une topologie réseau selon les standards NOC</span></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="d-flex flex-column gap-3 h-100">

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold py-2 px-3">
          Informations
        </div>
        <div class="card-body px-4 py-3">
          <dl class="row mb-0">
            <dt class="col-5 fw-normal text-secondary">Durée</dt>        <dd class="col-7">8 semaines</dd>
            <dt class="col-5 fw-normal text-secondary">Charge</dt>       <dd class="col-7">~5 h / semaine</dd>
            <dt class="col-5 fw-normal text-secondary">Niveau</dt>       <dd class="col-7">Introductif</dd>
            <dt class="col-5 fw-normal text-secondary mb-0">Équivalent</dt><dd class="col-7 mb-0">DEC 420-1A3</dd>
          </dl>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white fw-semibold py-2 px-3">
          Plan — 6 modules
        </div>
        <div class="card-body px-4 py-3">
          <ol class="mb-0 ps-3">
            <li class="mb-2">Modèle OSI — Les 7 couches</li>
            <li class="mb-2">Modèle TCP/IP et encapsulation</li>
            <li class="mb-2">Médias et équipements réseau</li>
            <li class="mb-2">Protocoles de couche application</li>
            <li class="mb-2">Outils de diagnostic fondamentaux</li>
            <li>Documentation et nomenclature NOC</li>
          </ol>
        </div>
      </div>

    </div>
  </div>

</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-primary text-white fw-semibold py-2 px-3">
    Prérequis — Préparer votre environnement de travail
  </div>
  <div class="card-body px-4 py-3">
    <p class="mb-4">
      Ce cours utilise quatre outils logiciels. Suivez les étapes ci-dessous dans l'ordre.
      <strong>Si vous n'avez pas les droits administrateur</strong> (ordinateur de travail ou de l'école),
      une alternative sans installation est indiquée pour chaque outil.
    </p>

    <div class="row g-4">

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 1 &mdash; Wireshark <span class="text-secondary fw-normal">(analyseur de paquets)</span></p>
        <p class="text-secondary mb-2">Utilisé pour capturer et observer le trafic réseau en temps réel.</p>
        <ul class="mb-0">
          <li class="mb-1"><strong>Avec droits admin :</strong> Installer depuis <a href="https://www.wireshark.org" target="_blank">wireshark.org</a> — gratuit, Windows / Mac / Linux.</li>
          <li class="mb-1"><strong>Sans droits admin :</strong> Télécharger <a href="https://www.wireshark.org/download.html" target="_blank">Wireshark Portable</a> — aucune installation, fonctionne depuis une clé USB.</li>
          <li><strong>Sur poste de laboratoire :</strong> Généralement préinstallé — vérifiez avec votre responsable.</li>
        </ul>
      </div>

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 2 &mdash; Cisco Packet Tracer <span class="text-secondary fw-normal">(simulateur réseau)</span></p>
        <p class="text-secondary mb-2">Pour construire et tester des topologies réseau sans équipement physique.</p>
        <ul class="mb-0">
          <li class="mb-1"><strong>Recommandé — aucune installation :</strong> Version navigateur via <a href="https://skillsforall.com" target="_blank">Cisco Skills for All</a> — compte gratuit.</li>
          <li><strong>Avec droits admin :</strong> Application de bureau depuis <a href="https://www.netacad.com" target="_blank">netacad.com</a> après création d'un compte.</li>
        </ul>
      </div>

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 3 &mdash; Terminal Linux <span class="text-secondary fw-normal">(commandes de diagnostic)</span></p>
        <p class="text-secondary mb-2">Pour les commandes ping, traceroute, dig, ss, ip addr, etc.</p>
        <ul class="mb-0">
          <li class="mb-1"><strong>Mac ou Linux :</strong> Terminal intégré, aucune installation requise.</li>
          <li class="mb-1"><strong>Windows avec droits admin :</strong> Activer WSL2, puis installer Ubuntu depuis le Microsoft Store.</li>
          <li><strong>Windows sans droits admin :</strong> <a href="https://webminal.org" target="_blank">webminal.org</a> (terminal Linux en ligne) ou Git Bash si déjà installé.</li>
        </ul>
      </div>

      <div class="col-md-6">
        <p class="fw-semibold mb-1">Étape 4 &mdash; draw.io <span class="text-secondary fw-normal">(schémas de topologie)</span></p>
        <p class="text-secondary mb-2">Pour créer des schémas de topologie réseau selon les standards NOC.</p>
        <ul class="mb-0">
          <li class="mb-1"><strong>Aucune installation requise :</strong> <a href="https://app.diagrams.net" target="_blank">app.diagrams.net</a> dans votre navigateur — fichiers sauvegardés localement ou sur Google Drive.</li>
        </ul>
        <p class="fw-semibold mt-3 mb-1">Un doute sur votre configuration ?</p>
        <p class="mb-0">Contactez votre responsable de formation. La plupart des exercices peuvent aussi être réalisés sur les postes du laboratoire.</p>
      </div>

    </div>
  </div>
</div>

<div class="alert alert-primary border-0 mb-0" role="alert">
  <strong>Avant de commencer le Module 1</strong> — complétez le test diagnostique ci-dessous.
  Il évalue vos connaissances actuelles, n'affecte pas votre note et donne des résultats immédiats.
</div>
HTML;

// ============================================================
// Appliquer les résumés à chaque section
// ============================================================

// Section 0
$section0 = $DB->get_record('course_sections', ['course' => $courseid, 'section' => 0]);
parcours_log("Mise à jour Section 0 — Bienvenue & Diagnostic");
if (!$dry_run) {
    $section0->name          = 'Bienvenue & Diagnostic';
    $section0->summary       = $section0_html;
    $section0->summaryformat = FORMAT_HTML;
    $DB->update_record('course_sections', $section0);
    parcours_log("  OK", 'success');
}

// Sections 1–6
foreach ($modules as $snum => $data) {
    $html = module_html($snum, $data['title'], $data['objectif'], $data['outils'], $data['labo']);
    $section = $DB->get_record('course_sections', ['course' => $courseid, 'section' => $snum]);
    if (!$section) {
        parcours_log("  Section {$snum} introuvable — ignorée", 'warning');
        continue;
    }
    parcours_log("Mise à jour Section {$snum} — {$data['title']}");
    if (!$dry_run) {
        $section->summary       = $html;
        $section->summaryformat = FORMAT_HTML;
        $DB->update_record('course_sections', $section);
        parcours_log("  OK", 'success');
    }
}

// ============================================================
// Purge des caches
// ============================================================
if (!$dry_run) {
    rebuild_course_cache($courseid, true);
    purge_all_caches();
    parcours_log("\nCaches Moodle purgés.", 'info');
}

parcours_log("\n=== Uniformisation terminée ===", 'success');
