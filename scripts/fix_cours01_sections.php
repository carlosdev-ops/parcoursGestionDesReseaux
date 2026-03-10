<?php
/**
 * fix_cours01_sections.php — Design uniforme calqué sur Section 0 pour toutes les sections
 *
 * Même squelette partout :
 *   1. alert-primary   — bannière titre + sous-titre
 *   2. row g-3 mb-4    — col-md-7 bg-primary (objectifs)  +  col-md-5 bg-secondary (infos)
 *   3. row g-3 mb-4    — col-md-6 bg-info    (plan/outils) + col-md-6 bg-warning  (prérequis/labo)
 *   4. alert-info      — note de bas de section
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

parcours_log("=== Design uniforme calqué sur Section 0 — Cours 1 ===");
$dry_run && parcours_log("MODE DRY-RUN", 'warning');

// ============================================================
// Fonction : HTML d'une section module (1–6)
// Squelette identique à Section 0 "Bienvenue & Diagnostic"
// ============================================================
function module_html(array $d): string {
    $n            = $d['num'];
    $title        = $d['title'];
    $subtitle     = $d['subtitle'];
    $obj_items    = implode('', array_map(fn($o) => "<li class=\"mb-2\">&#x2705; {$o}</li>", $d['objectifs']));
    $duree        = $d['duree'];
    $niveau       = $d['niveau'];
    $outils_items = implode('', array_map(fn($o) => "<li class=\"list-group-item\">{$o}</li>", $d['outils']));
    $labo_titre   = $d['labo_titre'];
    $labo_corps   = $d['labo_corps'];
    $note_bas     = $d['note_bas'];

    return <<<HTML
<div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
  <div class="fs-2 me-2">&#x1F4D6;</div>
  <div>
    <h3 class="alert-heading mb-1">Module {$n} — {$title}</h3>
    <p class="mb-0">{$subtitle}</p>
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
          {$obj_items}
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
            <tr><td class="ps-3 text-muted" style="width:45%">Durée estimée</td><td><strong>{$duree}</strong></td></tr>
            <tr class="table-light"><td class="ps-3 text-muted">Niveau</td><td><span class="badge bg-success">{$niveau}</span></td></tr>
            <tr><td class="ps-3 text-muted">Quiz</td><td>15 questions · 2 tentatives</td></tr>
            <tr class="table-light"><td class="ps-3 text-muted">Langue</td><td>Français</td></tr>
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
        Outils utilisés dans ce module
      </div>
      <ul class="list-group list-group-flush">
        {$outils_items}
      </ul>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-dark fw-semibold">
        &#x1F9EA; {$labo_titre}
      </div>
      <div class="card-body">
        {$labo_corps}
      </div>
    </div>
  </div>

</div>

<div class="alert alert-info mb-0" role="alert">
  {$note_bas}
</div>
HTML;
}

// ============================================================
// Données par module (sections 1–6)
// ============================================================
$modules = [
    1 => [
        'num'      => 1,
        'title'    => 'Modèle OSI — Les 7 couches',
        'subtitle' => 'La colonne vertébrale conceptuelle de tous les réseaux. Comprendre OSI, c\'est comprendre pourquoi chaque protocole existe et à quel niveau il intervient.',
        'objectifs' => [
            'Nommer et ordonner les 7 couches OSI et décrire le rôle de chacune',
            'Identifier la PDU (unité de données) associée à chaque couche',
            'Associer les équipements réseau (hub, switch, routeur) à leur couche OSI',
            'Expliquer le processus d\'encapsulation lors de l\'émission d\'un message',
        ],
        'duree'   => '~7 h (1 semaine)',
        'niveau'  => 'Introductif',
        'outils'  => [
            '&#x1F50D; <strong>Wireshark</strong> — observer les couches traversées en temps réel',
            '&#x1F4D5; <strong>Livre de cours</strong> — 4 chapitres avec schémas interactifs',
            '&#x1F5A5; <strong>Terminal</strong> — commandes de capture basiques',
        ],
        'labo_titre' => 'Labo 1 — Analyse de paquets avec Wireshark',
        'labo_corps' => '<p class="text-muted mb-2">Capturer et analyser du trafic réseau réel pour observer l\'encapsulation OSI en pratique.</p>
<ul class="list-unstyled mb-0">
  <li class="mb-1">&#x1F4CC; Capturer un ping ICMP et identifier les couches 2, 3</li>
  <li class="mb-1">&#x1F4CC; Capturer une requête HTTP et identifier le 3-way handshake TCP</li>
  <li>&#x1F4CC; Soumettre un rapport PDF avec captures annotées</li>
</ul>',
        'note_bas' => '<strong>&#x1F4CB; Par où commencer ?</strong> — Ouvrez le livre de cours (Module 1), lisez les 4 chapitres, puis passez au quiz formatif avant d\'ouvrir Wireshark.',
    ],
    2 => [
        'num'      => 2,
        'title'    => 'Modèle TCP/IP et encapsulation',
        'subtitle' => 'Le modèle réellement implémenté sur Internet. TCP/IP est ce que vous voyez dans vos captures Wireshark tous les jours.',
        'objectifs' => [
            'Établir la correspondance entre les couches OSI et les couches TCP/IP',
            'Décrire les champs clés des en-têtes IPv4, TCP et UDP',
            'Expliquer le 3-way handshake TCP et la gestion des numéros de séquence',
            'Distinguer TCP (fiable) et UDP (rapide) et justifier le choix selon le cas d\'usage',
        ],
        'duree'   => '~7 h (1 semaine)',
        'niveau'  => 'Introductif',
        'outils'  => [
            '&#x1F50D; <strong>Wireshark</strong> — décoder les en-têtes IPv4, TCP, UDP en direct',
            '&#x1F4D5; <strong>Livre de cours</strong> — 4 chapitres sur TCP/IP et encapsulation',
            '&#x1F310; <strong>neverssl.com</strong> — site HTTP non chiffré pour les captures',
        ],
        'labo_titre' => 'Labo 2 — Capturer le 3-way handshake TCP',
        'labo_corps' => '<p class="text-muted mb-2">Observer et documenter l\'établissement d\'une connexion TCP et la comparer à UDP.</p>
<ul class="list-unstyled mb-0">
  <li class="mb-1">&#x1F4CC; Capturer SYN, SYN-ACK, ACK vers neverssl.com</li>
  <li class="mb-1">&#x1F4CC; Analyser les champs Sequence Number, Flags, Window Size</li>
  <li>&#x1F4CC; Tableau comparatif TCP vs UDP en livrable PDF</li>
</ul>',
        'note_bas' => '<strong>&#x1F4CB; Par où commencer ?</strong> — Ouvrez le livre de cours (Module 2), lisez les 4 chapitres sur TCP/IP, puis passez au quiz formatif avant d\'ouvrir Wireshark.',
    ],
    3 => [
        'num'      => 3,
        'title'    => 'Médias et équipements réseau',
        'subtitle' => 'Le câble, la fibre, le switch, le routeur — la réalité physique derrière chaque trame. Ce module vous prépare à comprendre pourquoi les données circulent comme elles circulent.',
        'objectifs' => [
            'Distinguer les catégories de câbles UTP (Cat5e, Cat6, Cat6A) et leurs limites',
            'Comparer fibre monomode (SMF) et multimode (MMF) selon les distances et usages',
            'Expliquer les différences opérationnelles entre hub, switch et routeur',
            'Construire une topologie multi-segments dans Cisco Packet Tracer',
        ],
        'duree'   => '~7 h (1 semaine)',
        'niveau'  => 'Introductif',
        'outils'  => [
            '&#x1F4E6; <strong>Cisco Packet Tracer</strong> — simuler des topologies multi-médias',
            '&#x1F4D5; <strong>Livre de cours</strong> — 3 chapitres sur câblage et équipements',
            '&#x1F5FA; <strong>draw.io</strong> — schématiser la topologie construite',
        ],
        'labo_titre' => 'Labo 3 — Topologie réseau dans Packet Tracer',
        'labo_corps' => '<p class="text-muted mb-2">Construire, câbler et tester une topologie de base avec 2 segments réseau distincts.</p>
<ul class="list-unstyled mb-0">
  <li class="mb-1">&#x1F4CC; 1 routeur · 2 switches · 4 PCs (2 par switch)</li>
  <li class="mb-1">&#x1F4CC; 2 sous-réseaux : 192.168.1.0/24 et 192.168.2.0/24</li>
  <li>&#x1F4CC; Livrables : fichier .pkt + schéma draw.io + captures ping</li>
</ul>',
        'note_bas' => '<strong>&#x1F4CB; Par où commencer ?</strong> — Ouvrez le livre de cours (Module 3), lisez les 3 chapitres, puis passez au quiz formatif avant d\'ouvrir Packet Tracer.',
    ],
    4 => [
        'num'      => 4,
        'title'    => 'Protocoles de couche application',
        'subtitle' => 'DNS, DHCP, HTTP, SSH, SMTP — les protocoles que vous verrez dans les logs et les captures Wireshark au quotidien dans un NOC.',
        'objectifs' => [
            'Citer le rôle et le port standard de DNS, DHCP, HTTP/S, FTP, SSH, SMTP, NTP, SNMP',
            'Identifier ces protocoles dans une capture Wireshark par leur comportement',
            'Lire une réponse DNS et interpréter ses champs (type A, MX, TTL)',
            'Expliquer le rôle de DHCP dans l\'attribution automatique d\'adresses IP',
        ],
        'duree'   => '~7 h (1 semaine)',
        'niveau'  => 'Intermédiaire',
        'outils'  => [
            '&#x1F50D; <strong>Wireshark</strong> — filtres par protocole (dns, http, dhcp)',
            '&#x1F4D5; <strong>Livre de cours</strong> — 3 chapitres sur les protocoles applicatifs',
            '&#x1F5A5; <strong>Terminal</strong> — commandes nslookup, dig, curl',
        ],
        'labo_titre' => 'Labo 4 — Analyse de protocoles dans Wireshark',
        'labo_corps' => '<p class="text-muted mb-2">Capturer et analyser DNS, DHCP, HTTP et ICMP dans des captures Wireshark réelles.</p>
<ul class="list-unstyled mb-0">
  <li class="mb-1">&#x1F4CC; Filtrer et analyser une requête DNS (question + réponse + TTL)</li>
  <li class="mb-1">&#x1F4CC; Identifier méthodes HTTP, codes de statut, en-têtes</li>
  <li>&#x1F4CC; Tableau livrable : Protocole | Port | Couche | Rôle | Observation</li>
</ul>',
        'note_bas' => '<strong>&#x1F4CB; Par où commencer ?</strong> — Ouvrez le livre de cours (Module 4), lisez les 3 chapitres sur les protocoles, puis passez au quiz formatif avant d\'ouvrir Wireshark.',
    ],
    5 => [
        'num'      => 5,
        'title'    => 'Outils de diagnostic fondamentaux',
        'subtitle' => 'Les commandes de diagnostic sont les premiers outils que vous utiliserez lors d\'un incident NOC. Ce module vous donne la syntaxe précise et la méthodologie.',
        'objectifs' => [
            'Utiliser ping, traceroute, nslookup et dig avec les bonnes options',
            'Interpréter la sortie de netstat/ss, arp et ip addr',
            'Appliquer la méthodologie bottom-up (couche 1 → couche 7) pour isoler un problème',
            'Documenter une séquence de diagnostic dans un format NOC standardisé',
        ],
        'duree'   => '~7 h (1 semaine)',
        'niveau'  => 'Intermédiaire',
        'outils'  => [
            '&#x1F5A5; <strong>Terminal Linux</strong> — natif (Mac/Linux) ou <a href="https://webminal.org" target="_blank">webminal.org</a>',
            '&#x1F4D5; <strong>Livre de cours</strong> — 3 chapitres avec syntaxe et exemples réels',
            '&#x1F50D; <strong>Wireshark</strong> — valider les résultats des commandes en capture',
        ],
        'labo_titre' => 'Labo 5 — Diagnostic réseau structuré',
        'labo_corps' => '<p class="text-muted mb-2">Scénario NOC simulé : diagnostiquer pourquoi un utilisateur ne peut pas accéder à moodle.monecole.ca.</p>
<ul class="list-unstyled mb-0">
  <li class="mb-1">&#x1F4CC; Appliquer la méthodologie bottom-up complète</li>
  <li class="mb-1">&#x1F4CC; Documenter chaque commande, résultat et interprétation</li>
  <li>&#x1F4CC; Livrable PDF : tableau Commande | Résultat | Couche OSI testée</li>
</ul>',
        'note_bas' => '<strong>&#x1F4CB; Par où commencer ?</strong> — Ouvrez le livre de cours (Module 5), lisez les 3 chapitres, puis passez au quiz formatif avant de lancer votre terminal.',
    ],
    6 => [
        'num'      => 6,
        'title'    => 'Documentation et nomenclature NOC',
        'subtitle' => 'Un bon technicien NOC laisse des traces claires. La documentation est aussi importante que la configuration — elle permet la continuité des opérations 24/7.',
        'objectifs' => [
            'Appliquer la convention de nommage NOC : [SITE]-[LOC]-[TYPE]-[ROLE]-[NUM]',
            'Créer un schéma de topologie physique et logique avec draw.io (symboles Cisco)',
            'Produire un inventaire réseau (IPAM) et un plan d\'adressage IP',
            'Rédiger un runbook de diagnostic d\'incident selon le format NOC standard',
        ],
        'duree'   => '~7 h (1 semaine)',
        'niveau'  => 'Intermédiaire',
        'outils'  => [
            '&#x1F5FA; <strong>draw.io</strong> — <a href="https://app.diagrams.net" target="_blank">app.diagrams.net</a> (bibliothèques Cisco intégrées)',
            '&#x1F4D5; <strong>Livre de cours</strong> — 3 chapitres sur les standards NOC',
            '&#x1F4CA; <strong>Tableur</strong> (Excel/Google Sheets) — inventaire réseau et IPAM',
        ],
        'labo_titre' => 'Labo 6 — Documenter une topologie avec draw.io',
        'labo_corps' => '<p class="text-muted mb-2">Créer la documentation complète d\'une topologie réseau selon les standards NOC.</p>
<ul class="list-unstyled mb-0">
  <li class="mb-1">&#x1F4CC; Schéma logique draw.io (symboles Cisco, noms NOC, IPs annotées)</li>
  <li class="mb-1">&#x1F4CC; Inventaire réseau CSV : Hostname | Type | IP | Interface | VLAN</li>
  <li>&#x1F4CC; Plan d\'adressage IP : Réseau | CIDR | Usage | Passerelle</li>
</ul>',
        'note_bas' => '<strong>&#x1F4CB; Par où commencer ?</strong> — Ouvrez le livre de cours (Module 6), lisez les 3 chapitres sur la documentation NOC, puis passez au quiz formatif avant d\'ouvrir draw.io.',
    ],
];

// ============================================================
// Section 0 — Bienvenue & Diagnostic (design de référence)
// ============================================================
$section0_html = <<<'HTML'
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
          <li class="mb-2">&#x1F50D; <strong>Wireshark</strong> — <a href="https://www.wireshark.org" target="_blank">wireshark.org</a>
            <small class="text-muted d-block ps-4">Sans droits admin : <a href="https://www.wireshark.org/download.html" target="_blank">Wireshark Portable</a> (clé USB)</small>
          </li>
          <li class="mb-2">&#x1F4E6; <strong>Cisco Packet Tracer</strong> — <a href="https://skillsforall.com" target="_blank">Cisco Skills for All</a> (navigateur, gratuit)
            <small class="text-muted d-block ps-4">Avec droits admin : appli bureau via <a href="https://www.netacad.com" target="_blank">netacad.com</a></small>
          </li>
          <li class="mb-2">&#x1F5A5; <strong>Terminal Linux</strong> — natif sur Mac/Linux
            <small class="text-muted d-block ps-4">Windows sans droits admin : <a href="https://webminal.org" target="_blank">webminal.org</a> ou Git Bash</small>
          </li>
          <li>&#x1F5FA; <strong>draw.io</strong> — <a href="https://app.diagrams.net" target="_blank">app.diagrams.net</a> (navigateur, aucune installation)</li>
        </ul>
      </div>
    </div>
  </div>

</div>

<div class="alert alert-info mb-0" role="alert">
  <strong>&#x1F4CB; Test diagnostique ci-dessous</strong> — Complétez-le avant de commencer le Module 1.
  Il évalue vos connaissances actuelles et n'affecte pas votre note finale. Les résultats sont disponibles immédiatement.
</div>
HTML;

// ============================================================
// Section 7 — Évaluation finale & Synthèse (même squelette)
// ============================================================
$section7_html = <<<'HTML'
<div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">
  <div class="fs-2 me-2">&#x1F3C6;</div>
  <div>
    <h3 class="alert-heading mb-1">Évaluation finale &amp; Synthèse</h3>
    <p class="mb-0">Cette section regroupe l'évaluation sommative du cours. Elle couvre l'ensemble des 6 modules. Complétez tous les modules et leurs travaux pratiques avant d'y accéder.</p>
  </div>
</div>

<div class="row g-3 mb-4">

  <div class="col-md-7">
    <div class="card h-100 border-0 shadow-sm">
      <div class="card-header bg-primary text-white fw-semibold">
        Ce que vous démontrerez
      </div>
      <div class="card-body">
        <ul class="list-unstyled mb-0">
          <li class="mb-2">&#x2705; Maîtrise des modèles OSI et TCP/IP et de leurs couches</li>
          <li class="mb-2">&#x2705; Identification des équipements, médias et protocoles réseau</li>
          <li class="mb-2">&#x2705; Utilisation des outils de diagnostic (Wireshark, ping, traceroute, dig)</li>
          <li class="mb-0">&#x2705; Production d'une documentation réseau complète selon les standards NOC</li>
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
            <tr><td class="ps-3 text-muted" style="width:45%">Quiz sommatif</td><td><strong>30 questions · 60 min</strong></td></tr>
            <tr class="table-light"><td class="ps-3 text-muted">Tentatives</td><td>1 seule tentative</td></tr>
            <tr><td class="ps-3 text-muted">Note de passage</td><td><span class="badge bg-success">70 %</span></td></tr>
            <tr class="table-light"><td class="ps-3 text-muted">Projet</td><td>30 % de la note finale</td></tr>
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
        &#x2753; Examen sommatif
      </div>
      <ul class="list-group list-group-flush">
        <li class="list-group-item">30 questions aléatoires issues des 6 banques de questions</li>
        <li class="list-group-item">60 minutes · 1 seule tentative</li>
        <li class="list-group-item">Résultats disponibles après la date de fin</li>
        <li class="list-group-item">Note de passage : 70 %</li>
      </ul>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-dark fw-semibold">
        &#x1F9EA; Projet intégrateur
      </div>
      <div class="card-body">
        <p class="text-muted mb-2">Documenter et analyser une infrastructure réseau complète selon les standards NOC.</p>
        <ul class="list-unstyled mb-0">
          <li class="mb-1">&#x1F4CC; Schéma de topologie logique (draw.io)</li>
          <li class="mb-1">&#x1F4CC; Plan d'adressage IP avec sous-réseaux justifiés</li>
          <li class="mb-1">&#x1F4CC; Inventaire réseau complet (convention NOC)</li>
          <li>&#x1F4CC; Runbook de diagnostic d'incident</li>
        </ul>
      </div>
    </div>
  </div>

</div>

<div class="alert alert-info mb-0" role="alert">
  <strong>&#x26A0; Condition d'accès</strong> — Assurez-vous d'avoir complété les quiz formatifs et les travaux pratiques des 6 modules avant de commencer cette évaluation.
</div>
HTML;

// ============================================================
// Appliquer les résumés à chaque section
// ============================================================

// Section 0
$sec = $DB->get_record('course_sections', ['course' => $courseid, 'section' => 0]);
parcours_log("Section 0 — Bienvenue & Diagnostic");
if (!$dry_run) {
    $sec->name = 'Bienvenue & Diagnostic';
    $sec->summary = $section0_html;
    $sec->summaryformat = FORMAT_HTML;
    $DB->update_record('course_sections', $sec);
    parcours_log("  OK", 'success');
}

// Sections 1–6
foreach ($modules as $snum => $data) {
    $html = module_html($data);
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

// Section 7
$sec7 = $DB->get_record('course_sections', ['course' => $courseid, 'section' => 7]);
parcours_log("Section 7 — Évaluation finale & Synthèse");
if ($sec7 && !$dry_run) {
    $sec7->summary       = $section7_html;
    $sec7->summaryformat = FORMAT_HTML;
    $DB->update_record('course_sections', $sec7);
    parcours_log("  OK", 'success');
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
