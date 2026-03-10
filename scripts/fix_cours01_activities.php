<?php
/**
 * fix_cours01_activities.php — Design uniforme pour les intros de quiz et devoirs
 *
 * Quiz formatifs  : alert-primary + badges (questions, tentatives, résultats)
 * Examen sommatif : alert-primary + carte modalités + condition d'accès
 * Devoirs (labos) : alert-primary bannière + cartes colorées par section
 *                   bg-primary = Objectif/Scénario
 *                   bg-warning = Tâches
 *                   bg-info    = Rapport/Livrables
 *                   bg-secondary = Critères
 *
 * Usage :
 *   sudo -u www-data php scripts/fix_cours01_activities.php --moodle=/var/www/moodle
 *   sudo -u www-data php scripts/fix_cours01_activities.php --moodle=/var/www/moodle --dry-run
 */

define('CLI_SCRIPT', true);

$opts = getopt('', ['moodle:', 'dry-run']);
if (empty($opts['moodle'])) { echo "Usage: php fix_cours01_activities.php --moodle=/var/www/moodle\n"; exit(1); }

$moodle_path = rtrim($opts['moodle'], '/');
$dry_run     = isset($opts['dry-run']);

if (!file_exists($moodle_path . '/config.php')) { echo "config.php introuvable.\n"; exit(1); }

require_once($moodle_path . '/config.php');
require_once($CFG->libdir  . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lib.php');

$courseid = 2;

parcours_log("=== Design uniforme — Quiz et Devoirs du Cours 1 ===");
$dry_run && parcours_log("MODE DRY-RUN", 'warning');

// ============================================================
// Helpers
// ============================================================

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function contains(string $haystack, string $needle): bool {
    return strpos(strtolower($haystack), strtolower($needle)) !== false;
}

/** Couleur Bootstrap selon le titre de la section */
function card_color(string $title): string {
    if (contains($title, 'objectif') || contains($title, 'scénario') || contains($title, 'scenario')) {
        return 'bg-primary text-white';
    }
    if (contains($title, 'tâche') || contains($title, 'tache')) {
        return 'bg-warning text-dark';
    }
    if (contains($title, 'rapport') || contains($title, 'livrable')) {
        return 'bg-info text-white';
    }
    if (contains($title, 'critère') || contains($title, 'critere') || contains($title, 'évaluation')) {
        return 'bg-secondary text-white';
    }
    return 'bg-secondary text-white';
}

// ============================================================
// Intro formatée pour un quiz formatif
// ============================================================
function quiz_formatif_html(string $name, string $topic, int $questions = 15): string {
    return '<div class="alert alert-primary d-flex align-items-start gap-3" role="alert">'
         . '<div class="fs-3 flex-shrink-0">&#x2753;</div>'
         . '<div>'
         . '<h5 class="alert-heading mb-2">' . h($name) . '</h5>'
         . '<p class="mb-3">' . h($topic) . '</p>'
         . '<div class="d-flex gap-2 flex-wrap">'
         . '<span class="badge bg-secondary fs-6">' . $questions . ' questions</span>'
         . '<span class="badge bg-secondary fs-6">2 tentatives</span>'
         . '<span class="badge bg-success fs-6">R&eacute;sultats imm&eacute;diats</span>'
         . '</div>'
         . '</div></div>';
}

// ============================================================
// Intro formatée pour le quiz diagnostique
// ============================================================
function quiz_diagnostic_html(): string {
    return '<div class="alert alert-primary d-flex align-items-start gap-3" role="alert">'
         . '<div class="fs-3 flex-shrink-0">&#x1F4CA;</div>'
         . '<div>'
         . '<h5 class="alert-heading mb-2">Test diagnostique &mdash; Pr&eacute;requis du cours</h5>'
         . '<p class="mb-3">Ce test &eacute;value vos <strong>connaissances actuelles</strong> en r&eacute;seaux. '
         . 'Il n&rsquo;est <strong>pas not&eacute;</strong> dans votre dossier &mdash; son but est de vous situer '
         . 'et d&rsquo;identifier les notions &agrave; consolider avant de commencer.</p>'
         . '<div class="d-flex gap-2 flex-wrap">'
         . '<span class="badge bg-secondary fs-6">20 questions</span>'
         . '<span class="badge bg-secondary fs-6">30 minutes</span>'
         . '<span class="badge bg-success fs-6">R&eacute;sultats imm&eacute;diats</span>'
         . '</div>'
         . '</div></div>';
}

// ============================================================
// Intro formatée pour l'examen sommatif
// ============================================================
function quiz_sommatif_html(): string {
    return '<div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">'
         . '<div class="fs-2 flex-shrink-0">&#x1F3C6;</div>'
         . '<div>'
         . '<h4 class="alert-heading mb-1">Examen sommatif final &mdash; Cours 1</h4>'
         . '<p class="mb-0">Couvre l&rsquo;ensemble des 6 modules du cours. Compl&eacute;tez tous les modules avant d&rsquo;acc&eacute;der &agrave; cet examen.</p>'
         . '</div></div>'
         . '<div class="row g-3">'
         . '<div class="col-md-8">'
         . '<div class="card border-0 shadow-sm">'
         . '<div class="card-header bg-primary text-white fw-semibold py-2 px-3">Modalit&eacute;s</div>'
         . '<div class="card-body px-4 py-3">'
         . '<ul class="list-unstyled mb-0">'
         . '<li class="mb-2">&#x1F4CA; 30 questions al&eacute;atoires issues des banques des 6 modules</li>'
         . '<li class="mb-2">&#x23F1; 60 minutes &mdash; 1 seule tentative</li>'
         . '<li>&#x1F3AF; Note de passage : 70 %</li>'
         . '</ul>'
         . '</div></div></div>'
         . '<div class="col-md-4">'
         . '<div class="card border-0 shadow-sm h-100">'
         . '<div class="card-header bg-warning text-dark fw-semibold py-2 px-3">&#x26A0; Condition d&rsquo;acc&egrave;s</div>'
         . '<div class="card-body px-4 py-3">'
         . '<p class="mb-0">Avoir compl&eacute;t&eacute; les quiz formatifs et les travaux pratiques des 6 modules.</p>'
         . '</div></div></div>'
         . '</div>';
}

// ============================================================
// Intro formatée pour un devoir (labo / projet)
// Transforme le HTML existant (h4 + contenu) en cartes colorées
// ============================================================
function assign_html(string $name, string $existing_html): string {
    $is_projet = contains($name, 'projet');
    $emoji     = $is_projet ? '&#x1F3C6;' : '&#x1F9EA;';

    $banner = '<div class="alert alert-primary d-flex align-items-start gap-3 mb-4" role="alert">'
            . '<div class="fs-2 flex-shrink-0">' . $emoji . '</div>'
            . '<div><h4 class="alert-heading mb-0">' . h($name) . '</h4></div>'
            . '</div>';

    // Découper le HTML par blocs <h4>...</h4> + contenu suivant
    $parts = preg_split('/(<h4[^>]*>.*?<\/h4>)/s', $existing_html, -1,
                        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    $cards   = '';
    $h_title = null;
    $h_body  = '';

    // Pied de page (format + note) — on le détecte hors des h4
    $footer = '';

    foreach ($parts as $part) {
        if (preg_match('/<h4[^>]*>(.*?)<\/h4>/s', $part, $m)) {
            // Vider la section précédente
            if ($h_title !== null) {
                $cards .= make_card($h_title, $h_body);
                $h_body = '';
            }
            $h_title = strip_tags($m[1]);
        } else {
            // Détecter un éventuel pied de page (<p><strong>Format</strong>...)
            if ($h_title === null) {
                $footer .= $part;
            } else {
                $h_body .= $part;
            }
        }
    }
    // Vider la dernière section
    if ($h_title !== null) {
        $cards .= make_card($h_title, $h_body);
    }

    $footer_html = trim($footer) !== ''
        ? '<p class="text-muted mt-3 mb-0">' . trim(strip_tags($footer)) . '</p>'
        : '';

    return $banner . $cards . $footer_html;
}

function make_card(string $title, string $body): string {
    $color = card_color($title);
    return '<div class="card border-0 shadow-sm mb-3">'
         . '<div class="card-header ' . $color . ' fw-semibold py-2 px-3">' . h($title) . '</div>'
         . '<div class="card-body px-4 py-3">' . trim($body) . '</div>'
         . '</div>';
}

// ============================================================
// Données des quiz formatifs (topic + nb questions)
// ============================================================
$quiz_formatifs = [
    'Quiz — Module 1 : Modèle OSI'             => ['topic' => 'Les 7 couches OSI, les PDU associées et les équipements réseau par couche.',          'q' => 15],
    'Quiz — Module 2 : TCP/IP et encapsulation' => ['topic' => 'Modèle TCP/IP, en-têtes IPv4/TCP/UDP, encapsulation et désencapsulation.',            'q' => 15],
    'Quiz — Module 3 : Médias et équipements'   => ['topic' => 'Câblage UTP/fibre, équipements réseau (hub, switch, routeur) et leurs différences.', 'q' => 15],
    'Quiz — Module 4 : Protocoles applicatifs'  => ['topic' => 'DNS, DHCP, HTTP/S, FTP, SSH, SMTP, NTP, SNMP — rôles et ports standards.',           'q' => 15],
    'Quiz — Module 5 : Outils de diagnostic'    => ['topic' => 'ping, traceroute, Wireshark, netstat, ss, arp, dig — syntaxe et interprétation.',    'q' => 15],
    'Quiz — Module 6 : Documentation NOC'       => ['topic' => 'Conventions de nommage NOC, schémas draw.io, inventaire réseau et plan d\'adressage.','q' => 15],
];

// ============================================================
// Traitement des quiz
// ============================================================
parcours_log("\n--- Quiz ---");

$quizzes = $DB->get_records_sql(
    'SELECT q.id, q.name, q.intro
       FROM {quiz} q
       JOIN {course_modules} cm ON cm.instance = q.id
       JOIN {modules} m ON m.id = cm.module
      WHERE cm.course = :course AND m.name = :mod
      ORDER BY q.id',
    ['course' => $courseid, 'mod' => 'quiz']
);

foreach ($quizzes as $quiz) {
    parcours_log("  " . $quiz->name);

    if (contains($quiz->name, 'diagnostique')) {
        $new_intro = quiz_diagnostic_html();
    } elseif (contains($quiz->name, 'sommatif') || contains($quiz->name, 'examen')) {
        $new_intro = quiz_sommatif_html();
    } else {
        // Quiz formatif — chercher dans le tableau par correspondance de nom
        $data = null;
        foreach ($quiz_formatifs as $key => $val) {
            if (contains($quiz->name, explode(':', $key)[1] ?? $key)) {
                $data = $val;
                break;
            }
        }
        // Fallback : utiliser le texte existant comme topic
        $topic = $data ? $data['topic'] : trim(strip_tags($quiz->intro));
        $q     = $data ? $data['q'] : 15;
        $new_intro = quiz_formatif_html($quiz->name, $topic, $q);
    }

    if (!$dry_run) {
        $DB->set_field('quiz', 'intro',       $new_intro,  ['id' => $quiz->id]);
        $DB->set_field('quiz', 'introformat', FORMAT_HTML, ['id' => $quiz->id]);
    }
    parcours_log("    OK", 'success');
}

// ============================================================
// Traitement des devoirs
// ============================================================
parcours_log("\n--- Devoirs ---");

$assigns = $DB->get_records_sql(
    'SELECT a.id, a.name, a.intro
       FROM {assign} a
       JOIN {course_modules} cm ON cm.instance = a.id
       JOIN {modules} m ON m.id = cm.module
      WHERE cm.course = :course AND m.name = :mod
      ORDER BY a.id',
    ['course' => $courseid, 'mod' => 'assign']
);

foreach ($assigns as $assign) {
    parcours_log("  " . $assign->name);
    $new_intro = assign_html($assign->name, $assign->intro);

    if (!$dry_run) {
        $DB->set_field('assign', 'intro',       $new_intro,  ['id' => $assign->id]);
        $DB->set_field('assign', 'introformat', FORMAT_HTML, ['id' => $assign->id]);
    }
    parcours_log("    OK", 'success');
}

// ============================================================
// Purge des caches
// ============================================================
if (!$dry_run) {
    rebuild_course_cache($courseid, true);
    purge_all_caches();
    parcours_log("\nCaches purgés.", 'info');
}

parcours_log("\n=== Activités mises à jour ===", 'success');
