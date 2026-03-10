<?php
/**
 * fix_cours01_books.php — Design professionnel uniforme pour tous les livres du Cours 1
 *
 * Transformations appliquées à chaque chapitre :
 *   - Bannière d'en-tête bg-primary avec le titre du chapitre
 *   - <h3> : séparateur visuel avec bordure primaire
 *   - <h4> : titre de sous-section text-primary
 *   - <pre><code> : fond sombre, police monospace lisible
 *   - <table> : table-striped + table-hover ajoutés, wrapper responsive
 *   - Premier <p> : classe "lead" pour l'accroche
 *
 * Usage :
 *   sudo -u www-data php scripts/fix_cours01_books.php --moodle=/var/www/moodle
 *   sudo -u www-data php scripts/fix_cours01_books.php --moodle=/var/www/moodle --dry-run
 */

define('CLI_SCRIPT', true);

$opts = getopt('', ['moodle:', 'dry-run']);
if (empty($opts['moodle'])) { echo "Usage: php fix_cours01_books.php --moodle=/var/www/moodle\n"; exit(1); }

$moodle_path = rtrim($opts['moodle'], '/');
$dry_run     = isset($opts['dry-run']);

if (!file_exists($moodle_path . '/config.php')) { echo "config.php introuvable.\n"; exit(1); }

require_once($moodle_path . '/config.php');
require_once($CFG->libdir  . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/lib.php');

$courseid = 2;

parcours_log("=== Design professionnel — Livres du Cours 1 ===");
$dry_run && parcours_log("MODE DRY-RUN", 'warning');

// ============================================================
// Fonction : styliser le contenu d'un chapitre
// ============================================================
function style_chapter(string $title, string $content): string {

    // 1. Bannière d'en-tête bg-primary avec le titre du chapitre
    $safe_title = htmlspecialchars($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $header = '<div class="card border-0 shadow-sm mb-4">'
            . '<div class="card-header bg-primary text-white fw-semibold py-2 px-3">'
            . $safe_title
            . '</div></div>' . "\n\n";

    // 2. <h3> → titre de section avec trait bleu en dessous
    $content = preg_replace(
        '/<h3>/',
        '<h3 class="fw-semibold border-bottom border-primary border-2 pb-2 mt-4 mb-3">',
        $content
    );

    // 3. <h4> → sous-titre accent primary
    $content = preg_replace(
        '/<h4>/',
        '<h4 class="fw-semibold text-primary mt-4 mb-2">',
        $content
    );

    // 4. <pre><code> → fond sombre, monospace lisible
    $content = preg_replace(
        '/<pre><code>/',
        '<pre class="bg-dark text-light rounded p-3 mb-3" style="font-size:.9rem;line-height:1.6"><code>',
        $content
    );

    // 5. Tables → striped + hover + responsive
    $content = preg_replace(
        '/<table class="table table-bordered">/',
        '<table class="table table-bordered table-striped table-hover table-sm">',
        $content
    );
    $content = preg_replace(
        '/<table class="table table-bordered table-sm">/',
        '<table class="table table-bordered table-striped table-hover table-sm">',
        $content
    );
    // Envelopper dans div.table-responsive
    $content = preg_replace(
        '/(<table class="table[^"]*">)/s',
        '<div class="table-responsive mb-3">$1',
        $content
    );
    $content = preg_replace(
        '/(<\/table>)/',
        '$1</div>',
        $content
    );

    // 6. Premier <p> en lead
    $content = preg_replace('/<p>/', '<p class="lead">', $content, 1);

    return $header . $content;
}

// ============================================================
// Fonction : styliser l'intro d'un livre
// ============================================================
function style_book_intro(string $intro): string {
    $text = trim(strip_tags($intro));
    return '<div class="alert alert-info d-flex gap-3 mb-0" role="alert">'
         . '<div class="fs-4 flex-shrink-0">&#x1F4D6;</div>'
         . '<p class="mb-0">' . htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>'
         . '</div>';
}

// ============================================================
// Traitement de tous les livres du cours
// ============================================================
$books = $DB->get_records_sql(
    'SELECT b.id, b.name, b.intro
       FROM {book} b
       JOIN {course_modules} cm ON cm.instance = b.id
       JOIN {modules} m ON m.id = cm.module
      WHERE cm.course = :course AND m.name = :mod
      ORDER BY b.id',
    ['course' => $courseid, 'mod' => 'book']
);

foreach ($books as $book) {
    parcours_log("\nLivre : {$book->name}");

    // Intro du livre
    $new_intro = style_book_intro($book->intro);
    if (!$dry_run) {
        $DB->set_field('book', 'intro',       $new_intro,  ['id' => $book->id]);
        $DB->set_field('book', 'introformat', FORMAT_HTML, ['id' => $book->id]);
    }
    parcours_log("  Intro stylisée", 'info');

    // Chapitres
    $chapters = $DB->get_records('book_chapters', ['bookid' => $book->id], 'pagenum ASC');
    foreach ($chapters as $ch) {
        parcours_log("  Ch{$ch->pagenum} — {$ch->title}");
        $new_content = style_chapter($ch->title, $ch->content);
        if (!$dry_run) {
            $DB->set_field('book_chapters', 'content',       $new_content, ['id' => $ch->id]);
            $DB->set_field('book_chapters', 'contentformat', FORMAT_HTML,  ['id' => $ch->id]);
        }
        parcours_log("    OK", 'success');
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

parcours_log("\n=== Livres mis à jour ===", 'success');
