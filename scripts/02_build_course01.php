<?php
/**
 * 02_build_course01.php — Construction du contenu du Cours 1
 * Cours 1 — Fondements des réseaux & modèles OSI/TCP-IP
 *
 * Ce script crée :
 *   - Les 8 sections du cours (Section 0 à Section 7)
 *   - Les activités de chaque section (Livre, Quiz, Devoir)
 *   - Les banques de questions à partir des fichiers GIFT
 *   - Les liens questions → quiz
 *
 * Usage :
 *   sudo -u www-data php scripts/02_build_course01.php --moodle=/var/www/moodle
 *   sudo -u www-data php scripts/02_build_course01.php --moodle=/var/www/moodle --force
 *   sudo -u www-data php scripts/02_build_course01.php --moodle=/var/www/moodle --dry-run
 *
 * Prérequis : 00_setup_category.php et 01_create_courses.php doivent avoir été exécutés.
 */

define('CLI_SCRIPT', true);

$opts = getopt('', ['moodle:', 'force', 'dry-run']);

if (empty($opts['moodle'])) {
    echo "Erreur : --moodle est obligatoire.\n";
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
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/gift/format.php');

require_once(__DIR__ . '/lib.php');

// --- Récupération de l'état ---
$ref_file = __DIR__ . '/../.deploy_state.json';
if (!file_exists($ref_file)) {
    parcours_log("Erreur : .deploy_state.json introuvable. Exécuter d'abord les étapes 00 et 01.", 'error');
    exit(1);
}
$state = json_decode(file_get_contents($ref_file), true);

// Trouver le cours 1 dans l'état de déploiement
$course_idnumber = 'RESEAUX-01-OSI';
$course_state    = $state['courses'][$course_idnumber] ?? null;

if (!$course_state) {
    parcours_log("Erreur : Cours 1 (idnumber={$course_idnumber}) non trouvé dans .deploy_state.json.", 'error');
    parcours_log("Relancer : sudo -u www-data php scripts/01_create_courses.php --moodle={$moodle_path}", 'error');
    exit(1);
}

$courseid = (int) $course_state['id'];
$course   = get_course($courseid);

parcours_log("=== Construction du Cours 1 — Fondements des réseaux & modèles OSI/TCP-IP ===");
parcours_log("Course ID : {$courseid} — {$course->fullname}");
$dry_run && parcours_log("MODE DRY-RUN — aucune modification en base de données", 'warning');

// ============================================================
// 1. DÉFINITION DES SECTIONS
// ============================================================
$sections_def = [
    0 => [
        'name'    => 'Bienvenue & Diagnostic',
        'summary' => '<div class="section-intro"><h3>Bienvenue dans le Cours 1</h3>
<p>Ce cours est le <strong>point de départ</strong> du Parcours Gestion des Réseaux. Vous allez construire le cadre conceptuel qui donne du sens à tout ce que vous branchez au quotidien.</p>
<h4>Avant de commencer</h4>
<ul>
<li>Complétez le <strong>test diagnostique</strong> (20 questions, 30 minutes) pour évaluer vos connaissances actuelles.</li>
<li>Aucun résultat minimal n\'est requis — il sert uniquement à vous situer.</li>
<li>Consultez le <strong>plan du cours</strong> et les prérequis matériels ci-dessous.</li>
</ul>
<h4>Ce que vous aurez accompli à la fin de ce cours</h4>
<ul>
<li>Décrire et distinguer les 7 couches du modèle OSI et les 4 couches TCP/IP</li>
<li>Identifier le rôle des équipements réseau à chaque couche</li>
<li>Capturer et analyser du trafic réseau avec Wireshark</li>
<li>Documenter une topologie réseau de base selon les standards NOC</li>
</ul>
<h4>Durée estimée</h4>
<p>8 semaines · ~5 h/semaine · Niveau introductif</p></div>',
    ],
    1 => [
        'name'    => 'Module 1 — Modèle OSI : les 7 couches',
        'summary' => '<p><strong>Objectif :</strong> Décrire précisément le rôle de chacune des 7 couches OSI, identifier la PDU associée, et reconnaître les équipements qui opèrent à chaque couche.</p>
<p><strong>Outils :</strong> Schémas interactifs H5P, animation du trajet d\'un paquet, Wireshark pour observation en direct.</p>',
    ],
    2 => [
        'name'    => 'Module 2 — Modèle TCP/IP et encapsulation',
        'summary' => '<p><strong>Objectif :</strong> Comprendre la correspondance entre OSI et TCP/IP, maîtriser le processus d\'encapsulation/désencapsulation et analyser les en-têtes de protocoles dans Wireshark.</p>
<p><strong>Outils :</strong> Wireshark — capturer et décoder un paquet HTTP/DNS en temps réel.</p>',
    ],
    3 => [
        'name'    => 'Module 3 — Médias et équipements réseau',
        'summary' => '<p><strong>Objectif :</strong> Distinguer les types de supports de transmission (UTP, fibre, coaxial), identifier les équipements réseau (hub, switch, routeur) et leurs différences opérationnelles.</p>
<p><strong>Outils :</strong> Packet Tracer — construire et tester une topologie simple avec différents médias.</p>',
    ],
    4 => [
        'name'    => 'Module 4 — Protocoles de couche application',
        'summary' => '<p><strong>Objectif :</strong> Connaître le rôle et le port standard des protocoles DNS, DHCP, HTTP/S, FTP, SSH, SMTP, NTP, SNMP. Identifier ces protocoles par leur comportement dans Wireshark.</p>
<p><strong>Outils :</strong> Wireshark — filtres par protocole, analyse de requêtes DNS et HTTP.</p>',
    ],
    5 => [
        'name'    => 'Module 5 — Outils de diagnostic fondamentaux',
        'summary' => '<p><strong>Objectif :</strong> Maîtriser les commandes de diagnostic réseau essentielles — ping, traceroute, nslookup/dig, netstat/ss, arp, ip addr — et appliquer une méthodologie de troubleshooting structurée.</p>
<p><strong>Outils :</strong> Terminal Linux — sessions pratiques en ligne de commande.</p>',
    ],
    6 => [
        'name'    => 'Module 6 — Documentation et nomenclature NOC',
        'summary' => '<p><strong>Objectif :</strong> Appliquer les standards de documentation réseau NOC — nommage des équipements, schémas de topologie (draw.io), inventaire réseau, plan d\'adressage IP.</p>
<p><strong>Outils :</strong> draw.io — documenter une topologie réseau réelle.</p>',
    ],
    7 => [
        'name'    => 'Évaluation finale & Synthèse',
        'summary' => '<p>Cette section contient l\'<strong>évaluation sommative finale</strong> du cours. Elle couvre l\'ensemble des 6 modules.</p>
<p><strong>Quiz sommatif :</strong> 30 questions aléatoires · 60 minutes · 1 seule tentative · Note de passage : 70%</p>
<p><strong>Projet intégrateur :</strong> Documenter et analyser une topologie réseau réelle selon les standards NOC.</p>
<p><em>Complétez tous les modules et leurs labos avant d\'accéder à cette section.</em></p>',
    ],
];

// ============================================================
// 2. DÉFINITION DES ACTIVITÉS PAR SECTION
// ============================================================
$activities_def = [

    // --- SECTION 0 : Bienvenue & Diagnostic ---
    ['section' => 0, 'type' => 'label', 'name' => 'Plan et prérequis du cours',
     'intro' => '<div class="card mb-3"><div class="card-body">
<h4>Plan du cours — 6 modules + évaluation finale</h4>
<ol>
<li><strong>Modèle OSI</strong> — les 7 couches, PDU, équipements</li>
<li><strong>Modèle TCP/IP</strong> — encapsulation, en-têtes, Wireshark</li>
<li><strong>Médias et équipements</strong> — câblage, hub/switch/routeur</li>
<li><strong>Protocoles applicatifs</strong> — DNS, DHCP, HTTP, SSH, SMTP</li>
<li><strong>Outils de diagnostic</strong> — ping, traceroute, Wireshark</li>
<li><strong>Documentation NOC</strong> — nommage, draw.io, inventaire</li>
</ol>
<h4>Prérequis matériels</h4>
<ul>
<li>PC ou laptop (Windows/Linux/Mac) avec 8 Go RAM minimum</li>
<li>Wireshark installé (<a href="https://www.wireshark.org">wireshark.org</a>)</li>
<li>Cisco Packet Tracer (gratuit via Cisco NetAcad)</li>
<li>Accès à un terminal Linux (natif, WSL ou VM)</li>
</ul>
</div></div>'],

    ['section' => 0, 'type' => 'quiz', 'name' => 'Test diagnostique — Prérequis du cours',
     'intro' => '<p>Ce test évalue vos connaissances actuelles en réseaux. Il n\'est <strong>pas noté</strong> dans votre dossier — son but est de vous situer et d\'identifier les notions à consolider avant le cours.</p>
<p><strong>20 questions · 30 minutes · Résultats visibles immédiatement après soumission.</strong></p>',
     'attempts' => 2, 'timelimit' => 1800, 'gradepass' => 0,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => 'Cours 1 — Diagnostic',
     'idnumber' => 'C1-QUIZ-DIAG'],

    // --- SECTION 1 : Module 1 — OSI ---
    ['section' => 1, 'type' => 'book', 'name' => 'Théorie — Module 1 : Modèle OSI et les 7 couches',
     'intro' => '<p>Lisez attentivement chaque chapitre avant de passer au quiz. Le livre contient des schémas, des exemples concrets et des références aux protocoles réels utilisés en environnement opérateur.</p>',
     'chapters' => [
         ['title' => '1. Introduction au modèle OSI', 'subchapter' => 0, 'content' => '<h3>Pourquoi le modèle OSI ?</h3>
<p>Le modèle OSI (Open Systems Interconnection) a été développé par l\'ISO dans les années 1980 pour standardiser la communication entre systèmes informatiques hétérogènes. Avant OSI, chaque fabricant utilisait ses propres protocoles propriétaires.</p>
<p>Aujourd\'hui, OSI est un <strong>modèle de référence</strong> — non implémenté directement — mais indispensable pour :</p>
<ul>
<li>Comprendre comment les protocoles s\'organisent en couches</li>
<li>Diagnostiquer les pannes réseau en isolant la couche défaillante</li>
<li>Communiquer avec précision entre techniciens ("problème de couche 2 sur le trunk")</li>
</ul>
<h3>Structure en 7 couches</h3>
<p>OSI divise la communication réseau en 7 couches, chacune ayant un rôle précis et n\'interagissant qu\'avec les couches adjacentes (supérieure et inférieure).</p>
<p><strong>Moyen mnémotechnique (Anglais, haut→bas) :</strong> <em>All People Seem To Need Data Processing</em><br>
(Application, Presentation, Session, Transport, Network, Data Link, Physical)</p>'],
         ['title' => '2. Les couches 1 à 3 — Réseau', 'subchapter' => 0, 'content' => '<h3>Couche 1 — Physique (Physical)</h3>
<p><strong>Rôle :</strong> Transmission des bits bruts sur le support physique.<br>
<strong>PDU :</strong> Bit<br>
<strong>Équipements :</strong> Câbles (UTP, fibre, coaxial), hubs, répéteurs, transceiveurs<br>
<strong>Protocoles :</strong> Ethernet (signal électrique), 802.11 (Wi-Fi — signal radio), SONET/SDH</p>
<h3>Couche 2 — Liaison de données (Data Link)</h3>
<p><strong>Rôle :</strong> Livraison fiable des trames sur un lien physique local. Adressage physique (MAC). Détection d\'erreurs (CRC).<br>
<strong>PDU :</strong> Trame (Frame)<br>
<strong>Équipements :</strong> Switches, ponts (bridges), cartes réseau<br>
<strong>Protocoles :</strong> Ethernet 802.3, Wi-Fi 802.11, PPP, HDLC<br>
<strong>Sous-couches :</strong> LLC (Logical Link Control) et MAC (Media Access Control)</p>
<h3>Couche 3 — Réseau (Network)</h3>
<p><strong>Rôle :</strong> Adressage logique et routage des paquets entre réseaux différents.<br>
<strong>PDU :</strong> Paquet (Packet)<br>
<strong>Équipements :</strong> Routeurs, switches de couche 3<br>
<strong>Protocoles :</strong> IP (IPv4, IPv6), ICMP, ARP, OSPF, BGP</p>'],
         ['title' => '3. Les couches 4 à 7 — Transport & Application', 'subchapter' => 0, 'content' => '<h3>Couche 4 — Transport</h3>
<p><strong>Rôle :</strong> Segmentation des données, contrôle de flux, fiabilité de bout en bout.<br>
<strong>PDU :</strong> Segment (TCP) / Datagramme (UDP)<br>
<strong>Protocoles :</strong> TCP (fiable, orienté connexion), UDP (rapide, sans connexion)</p>
<h3>Couche 5 — Session</h3>
<p><strong>Rôle :</strong> Établissement, maintien et fermeture des sessions de communication.<br>
<strong>Protocoles :</strong> NetBIOS, RPC, PPTP<br>
<em>Note : Souvent intégrée à la couche Application dans TCP/IP.</em></p>
<h3>Couche 6 — Présentation</h3>
<p><strong>Rôle :</strong> Traduction du format des données (ASCII, UTF-8), compression, chiffrement.<br>
<strong>Protocoles :</strong> SSL/TLS (chiffrement), JPEG/PNG/MP4 (format médias)<br>
<em>Note : Souvent intégrée à la couche Application dans TCP/IP.</em></p>
<h3>Couche 7 — Application</h3>
<p><strong>Rôle :</strong> Interface entre l\'utilisateur et le réseau. Services réseau accessibles aux applications.<br>
<strong>PDU :</strong> Données (Data)<br>
<strong>Protocoles :</strong> HTTP/S, DNS, DHCP, SMTP, SSH, FTP, SNMP, NTP</p>'],
         ['title' => '4. Encapsulation et désencapsulation', 'subchapter' => 0, 'content' => '<h3>Le processus d\'encapsulation</h3>
<p>Lors de l\'envoi d\'un message, chaque couche OSI <strong>ajoute son propre en-tête</strong> aux données reçues de la couche supérieure. Ce processus s\'appelle l\'<strong>encapsulation</strong>.</p>
<p>Exemple — Envoi d\'une requête HTTP :</p>
<ol>
<li><strong>Application (7) :</strong> Génère la requête HTTP (GET /index.html)</li>
<li><strong>Transport (4) :</strong> Ajoute l\'en-tête TCP (ports source/dest, numéro de séquence) → Segment</li>
<li><strong>Réseau (3) :</strong> Ajoute l\'en-tête IP (adresses IP source/dest, TTL) → Paquet</li>
<li><strong>Liaison (2) :</strong> Ajoute l\'en-tête Ethernet (adresses MAC) + FCS → Trame</li>
<li><strong>Physique (1) :</strong> Convertit la trame en bits transmis sur le câble</li>
</ol>
<h3>La désencapsulation</h3>
<p>À la réception, le processus est <strong>inversé</strong> (couche 1 → 7) :</p>
<ol>
<li>La couche 1 reçoit les bits et les passe à la couche 2</li>
<li>La couche 2 valide le CRC, retire l\'en-tête Ethernet et passe le paquet à la couche 3</li>
<li>La couche 3 vérifie l\'adresse IP destination, retire l\'en-tête IP</li>
<li>La couche 4 réassemble les segments dans l\'ordre</li>
<li>La couche 7 présente les données à l\'application</li>
</ol>
<p><strong>En pratique avec Wireshark :</strong> Wireshark affiche exactement cette pile d\'en-têtes pour chaque paquet capturé — vous pouvez "ouvrir" chaque couche et voir ses champs.</p>'],
     ]],

    ['section' => 1, 'type' => 'quiz', 'name' => 'Quiz — Module 1 : Modèle OSI',
     'intro' => '<p>Quiz formatif couvrant les 7 couches OSI, les PDU et les équipements. <strong>2 tentatives permises · Résultats immédiats.</strong></p>',
     'attempts' => 2, 'timelimit' => 0, 'gradepass' => 70,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => 'Cours 1 — Module 1 — Modèle OSI',
     'idnumber' => 'C1-QUIZ-MOD01'],

    ['section' => 1, 'type' => 'assign', 'name' => 'Labo 1 — Analyse de paquets avec Wireshark',
     'intro' => '<h4>Objectif du laboratoire</h4>
<p>Capturer et analyser du trafic réseau réel avec Wireshark pour observer l\'encapsulation OSI en pratique.</p>
<h4>Tâches à réaliser</h4>
<ol>
<li>Lancer Wireshark sur votre interface réseau active. Démarrer une capture.</li>
<li>Ouvrir un terminal et exécuter : <code>ping -c 4 8.8.8.8</code><br>
   Arrêter la capture. Appliquer le filtre Wireshark : <code>icmp</code><br>
   <strong>Identifier :</strong> l\'en-tête Ethernet (couche 2), IP (couche 3) et ICMP (couche 3). Noter les adresses MAC et IP.</li>
<li>Démarrer une nouvelle capture. Naviguer vers <code>http://neverssl.com</code> dans votre navigateur.<br>
   Appliquer le filtre : <code>tcp.port == 80</code><br>
   <strong>Identifier :</strong> le 3-way handshake TCP (SYN, SYN-ACK, ACK) et la requête HTTP GET.</li>
<li>Effectuer une requête DNS : <code>dig google.com</code><br>
   Capturer et analyser avec le filtre <code>dns</code>.<br>
   <strong>Identifier :</strong> la question DNS et la réponse avec l\'adresse IP retournée.</li>
</ol>
<h4>Rapport à soumettre (PDF)</h4>
<ul>
<li>Captures d\'écran annotées pour chaque tâche (minimum 3 captures)</li>
<li>Pour chaque capture : identifier les couches OSI visibles et expliquer chaque en-tête</li>
<li>Répondre : Quelle est l\'adresse MAC de votre passerelle ? Comment l\'avez-vous trouvée ?</li>
</ul>
<p><strong>Format :</strong> PDF · <strong>Note :</strong> Sommatif 25% de la note finale</p>',
     'grade' => 25, 'idnumber' => 'C1-LAB-01'],

    // --- SECTION 2 : Module 2 — TCP/IP ---
    ['section' => 2, 'type' => 'book', 'name' => 'Théorie — Module 2 : Modèle TCP/IP et encapsulation',
     'intro' => '<p>Ce livre couvre le modèle TCP/IP, sa correspondance avec OSI, et les mécanismes d\'encapsulation. Focus sur les en-têtes TCP et IP que vous verrez dans Wireshark.</p>',
     'chapters' => [
         ['title' => '1. Le modèle TCP/IP — 4 couches', 'subchapter' => 0, 'content' => '<h3>TCP/IP vs OSI</h3>
<p>Contrairement à OSI (modèle théorique), TCP/IP est le modèle <strong>réellement implémenté</strong> sur Internet. Il comporte 4 couches :</p>
<table class="table table-bordered">
<thead><tr><th>Couche TCP/IP</th><th>Équivalent OSI</th><th>Protocoles typiques</th></tr></thead>
<tbody>
<tr><td><strong>Application</strong></td><td>Couches 5-6-7</td><td>HTTP, DNS, DHCP, SSH, SMTP, SNMP</td></tr>
<tr><td><strong>Transport</strong></td><td>Couche 4</td><td>TCP, UDP</td></tr>
<tr><td><strong>Internet</strong></td><td>Couche 3</td><td>IP, ICMP, ARP</td></tr>
<tr><td><strong>Accès réseau</strong></td><td>Couches 1-2</td><td>Ethernet, Wi-Fi, PPP</td></tr>
</tbody>
</table>'],
         ['title' => '2. L\'en-tête IPv4 décortiqué', 'subchapter' => 0, 'content' => '<h3>Structure de l\'en-tête IPv4 (20 octets minimum)</h3>
<p>Champs clés que vous verrez dans Wireshark :</p>
<ul>
<li><strong>Version (4 bits) :</strong> 4 pour IPv4, 6 pour IPv6</li>
<li><strong>TTL (8 bits) :</strong> Time To Live — decrementé de 1 à chaque routeur. Quand TTL=0, le paquet est détruit (ICMP Time Exceeded). La valeur initiale typique : 64 (Linux), 128 (Windows), 255 (Cisco IOS).</li>
<li><strong>Protocol (8 bits) :</strong> Identifie le protocole de couche 4 encapsulé : 6=TCP, 17=UDP, 1=ICMP</li>
<li><strong>Source IP et Destination IP (32 bits chacune) :</strong> Adresses IPv4 de l\'émetteur et du destinataire</li>
<li><strong>Checksum en-tête (16 bits) :</strong> Vérifie l\'intégrité de l\'en-tête IP (pas du payload)</li>
</ul>'],
         ['title' => '3. TCP — Fiabilité et contrôle de flux', 'subchapter' => 0, 'content' => '<h3>Le 3-way handshake TCP</h3>
<p>Avant tout échange de données, TCP établit une connexion en 3 étapes :</p>
<ol>
<li><strong>SYN :</strong> Client → Serveur — "Je veux me connecter, mon numéro de séquence est X"</li>
<li><strong>SYN-ACK :</strong> Serveur → Client — "OK, mon seq est Y, j\'accuse réception de X+1"</li>
<li><strong>ACK :</strong> Client → Serveur — "Compris, j\'accuse réception de Y+1"</li>
</ol>
<p>La connexion est établie. Les données peuvent maintenant transiter.</p>
<h3>Contrôle de flux et retransmission</h3>
<p>TCP numérote chaque octet envoyé (Sequence Number). Le destinataire accuse réception avec un ACK. Si aucun ACK n\'est reçu après un timeout, TCP retransmet automatiquement — c\'est ce qui garantit la fiabilité.</p>'],
         ['title' => '4. UDP — Légèreté et temps réel', 'subchapter' => 0, 'content' => '<h3>UDP — User Datagram Protocol</h3>
<p>UDP est un protocole de transport <strong>sans connexion</strong> et <strong>sans fiabilité garantie</strong>. Son en-tête ne fait que 8 octets (vs 20+ pour TCP).</p>
<p><strong>Champs de l\'en-tête UDP :</strong> Port source, Port destination, Longueur, Checksum.</p>
<h3>Quand utiliser UDP ?</h3>
<p>UDP est préféré quand la <strong>latence</strong> est plus critique que la fiabilité :</p>
<ul>
<li><strong>DNS :</strong> Requêtes rapides, une réponse suffit</li>
<li><strong>Streaming vidéo/audio :</strong> Un paquet perdu = un artefact acceptable</li>
<li><strong>Jeux en ligne :</strong> La position des joueurs doit arriver vite, pas parfaitement</li>
<li><strong>DHCP, TFTP, SNMP</strong></li>
</ul>'],
     ]],

    ['section' => 2, 'type' => 'quiz', 'name' => 'Quiz — Module 2 : TCP/IP et encapsulation',
     'intro' => '<p>Quiz formatif — modèle TCP/IP, en-têtes, TCP vs UDP. <strong>2 tentatives · Résultats immédiats.</strong></p>',
     'attempts' => 2, 'timelimit' => 0, 'gradepass' => 70,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => 'Cours 1 — Module 2 — TCP/IP et encapsulation',
     'idnumber' => 'C1-QUIZ-MOD02'],

    ['section' => 2, 'type' => 'assign', 'name' => 'Labo 2 — Capturer le 3-way handshake TCP avec Wireshark',
     'intro' => '<h4>Objectif</h4>
<p>Observer et documenter le 3-way handshake TCP et le processus de fermeture de connexion.</p>
<h4>Tâches</h4>
<ol>
<li>Lancer Wireshark. Naviguer vers <code>http://neverssl.com</code> (HTTP non chiffré).<br>
   Filtrer : <code>tcp and ip.addr == [IP du serveur]</code><br>
   Identifier et annoter les paquets SYN, SYN-ACK, ACK, FIN.</li>
<li>Comparer avec une connexion UDP : effectuer <code>dig google.com</code> et capturer avec filtre <code>udp.port == 53</code>.<br>
   Constater l\'absence de handshake.</li>
<li>Analyser les champs de l\'en-tête TCP : Sequence Number, Acknowledgment Number, Flags, Window Size.</li>
</ol>
<h4>Rapport (PDF) :</h4>
<ul>
<li>Captures annotées du handshake complet</li>
<li>Tableau comparatif TCP vs UDP (en-tête, fiabilité, cas d\'usage)</li>
<li>Explication du TTL observé dans l\'en-tête IP — quelle valeur initiale et pourquoi ?</li>
</ul>',
     'grade' => 25, 'idnumber' => 'C1-LAB-02'],

    // --- SECTION 3 : Module 3 — Médias ---
    ['section' => 3, 'type' => 'book', 'name' => 'Théorie — Module 3 : Médias et équipements réseau',
     'intro' => '<p>Câblage, équipements actifs et passifs — la base physique de tout réseau. Ce module vous prépare à comprendre pourquoi et comment les données circulent sur les supports physiques.</p>',
     'chapters' => [
         ['title' => '1. Câblage à paires torsadées (UTP/STP)', 'subchapter' => 0, 'content' => '<h3>Catégories de câbles UTP</h3>
<table class="table table-bordered table-sm">
<thead><tr><th>Catégorie</th><th>Vitesse max</th><th>Distance max</th><th>Usage typique</th></tr></thead>
<tbody>
<tr><td>Cat5e</td><td>1 Gbps</td><td>100 m</td><td>LAN bureau standard</td></tr>
<tr><td>Cat6</td><td>1 Gbps (10G sur 55m)</td><td>100 m</td><td>LAN moderne</td></tr>
<tr><td>Cat6A</td><td>10 Gbps</td><td>100 m</td><td>Salle serveurs, datacenter</td></tr>
<tr><td>Cat8</td><td>25/40 Gbps</td><td>30 m</td><td>Liaisons inter-switches datacenter</td></tr>
</tbody>
</table>
<p><strong>Standard de câblage :</strong> TIA/EIA-568B est le plus répandu en Amérique du Nord pour les prises RJ-45.</p>'],
         ['title' => '2. Fibre optique — SMF et MMF', 'subchapter' => 0, 'content' => '<h3>Fibre monomode (SMF — Single Mode Fiber)</h3>
<p><strong>Cœur :</strong> 9 µm · <strong>Distances :</strong> jusqu\'à 80 km (OS2) · <strong>Couleur gaine :</strong> jaune</p>
<p>Utilisée pour les liaisons longue distance : backbone opérateur, liens entre datacenters, liaisons intercités. La lumière se propage en un seul mode — moins de dispersion.</p>
<h3>Fibre multimode (MMF — Multi Mode Fiber)</h3>
<p><strong>Cœur :</strong> 50 ou 62,5 µm · <strong>Distances :</strong> 300-550 m (OM3/OM4) · <strong>Couleur gaine :</strong> orange (OM1/OM2) ou aigue-marine (OM3/OM4)</p>
<p>Utilisée dans les datacenters et campus. Plusieurs modes de propagation = plus de dispersion = distances limitées.</p>
<h3>Connecteurs courants</h3>
<ul>
<li><strong>SC/APC (vert) :</strong> Standard FTTH et HFC — finition angulée, meilleures pertes par réflexion</li>
<li><strong>LC/UPC (bleu) :</strong> Compact, dominant dans les datacenters</li>
<li><strong>ST :</strong> Ancienne génération, encore présent en campus</li>
</ul>'],
         ['title' => '3. Hub, Switch et Routeur — Différences clés', 'subchapter' => 0, 'content' => '<h3>Hub (concentrateur) — Couche 1</h3>
<p><strong>Fonctionnement :</strong> Répète le signal sur tous les ports simultanément. <strong>Un seul domaine de collision.</strong></p>
<p><strong>Problème :</strong> Si deux hôtes émettent en même temps → collision → retransmission → dégradation des performances. CSMA/CD gère les collisions mais ne les élimine pas.</p>
<p><strong>Statut :</strong> Technologie obsolète, remplacée par les switches.</p>
<h3>Switch (commutateur) — Couche 2</h3>
<p><strong>Fonctionnement :</strong> Apprend les adresses MAC et transmet les trames uniquement vers le port destination. <strong>Un domaine de collision par port</strong> en full-duplex = 0 collision.</p>
<p><strong>Table CAM :</strong> Association MAC ↔ Port physique, avec TTL. Si MAC inconnue → flooding sur tous les ports.</p>
<h3>Routeur — Couche 3</h3>
<p><strong>Fonctionnement :</strong> Route les paquets entre réseaux IP différents. Maintient une table de routage. <strong>Sépare les domaines de broadcast.</strong></p>
<p><strong>Chaque interface du routeur = un réseau IP différent = un domaine de broadcast différent.</strong></p>'],
     ]],

    ['section' => 3, 'type' => 'quiz', 'name' => 'Quiz — Module 3 : Médias et équipements',
     'intro' => '<p>Quiz formatif — câblage, fibre, équipements réseau. <strong>2 tentatives · Résultats immédiats.</strong></p>',
     'attempts' => 2, 'timelimit' => 0, 'gradepass' => 70,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => 'Cours 1 — Module 3 — Médias et équipements',
     'idnumber' => 'C1-QUIZ-MOD03'],

    ['section' => 3, 'type' => 'assign', 'name' => 'Labo 3 — Topologie réseau dans Packet Tracer',
     'intro' => '<h4>Objectif</h4>
<p>Construire, câbler et tester une topologie réseau de base dans Cisco Packet Tracer.</p>
<h4>Tâches</h4>
<ol>
<li>Créer une topologie avec : 1 routeur, 2 switches, 4 PCs (2 par switch).<br>
   Réseau A (Switch 1) : 192.168.1.0/24 · Réseau B (Switch 2) : 192.168.2.0/24</li>
<li>Configurer les adresses IP sur chaque PC et la passerelle par défaut.</li>
<li>Tester la connectivité : ping intra-réseau puis inter-réseau via le routeur.</li>
<li>Observer la table ARP sur un PC : comment l\'adresse MAC de la passerelle a-t-elle été apprise ?</li>
</ol>
<h4>Livrables</h4>
<ul>
<li>Fichier <code>.pkt</code> Packet Tracer complété</li>
<li>Captures d\'écran des pings réussis (intra et inter-réseau)</li>
<li>Schéma draw.io de la topologie avec adresses IP annotées</li>
</ul>',
     'grade' => 25, 'idnumber' => 'C1-LAB-03'],

    // --- SECTION 4 : Module 4 — Protocoles ---
    ['section' => 4, 'type' => 'book', 'name' => 'Théorie — Module 4 : Protocoles de couche application',
     'intro' => '<p>DNS, DHCP, HTTP, SSH, SMTP — les protocoles que vous verrez dans les logs et les captures Wireshark au quotidien. Ce module vous donne les bases pour les identifier et les dépanner.</p>',
     'chapters' => [
         ['title' => '1. Protocoles de résolution et configuration', 'subchapter' => 0, 'content' => '<h3>DNS — Domain Name System (port UDP/TCP 53)</h3>
<p>DNS traduit les noms de domaine en adresses IP. Hiérarchie : Résolveur local → Serveur récursif → Serveurs racine → TLD → Autoritative.</p>
<p><strong>Types d\'enregistrements clés :</strong></p>
<ul>
<li><strong>A :</strong> nom → IPv4 (ex. moodle.monecole.ca → 192.168.1.100)</li>
<li><strong>AAAA :</strong> nom → IPv6</li>
<li><strong>MX :</strong> domaine → serveur de messagerie</li>
<li><strong>CNAME :</strong> alias vers un autre nom (ex. www → moodle.monecole.ca)</li>
<li><strong>PTR :</strong> IPv4 → nom (résolution inverse)</li>
<li><strong>SOA, NS :</strong> définissent l\'autorité sur une zone DNS</li>
</ul>
<h3>DHCP — Dynamic Host Configuration Protocol (port UDP 67/68)</h3>
<p><strong>Processus DORA :</strong></p>
<ol>
<li><strong>Discover :</strong> Client broadcast (255.255.255.255) — "Y a-t-il un serveur DHCP ?"</li>
<li><strong>Offer :</strong> Serveur répond avec une offre d\'adresse IP</li>
<li><strong>Request :</strong> Client accepte l\'offre</li>
<li><strong>Acknowledge :</strong> Serveur confirme — bail accordé pour X heures/jours</li>
</ol>'],
         ['title' => '2. Protocoles web et transfert de fichiers', 'subchapter' => 0, 'content' => '<h3>HTTP — HyperText Transfer Protocol (port TCP 80)</h3>
<p>HTTP est un protocole <strong>stateless</strong> de type requête/réponse. Méthodes principales :</p>
<ul>
<li><strong>GET :</strong> Récupérer une ressource (page web, image)</li>
<li><strong>POST :</strong> Envoyer des données (formulaires, soumissions)</li>
<li><strong>PUT/DELETE :</strong> Modifier/supprimer une ressource (APIs REST)</li>
</ul>
<p><strong>Codes de statut HTTP :</strong> 200=OK, 301=Redirection permanente, 404=Non trouvé, 500=Erreur serveur.</p>
<h3>HTTPS (port TCP 443)</h3>
<p>HTTP sécurisé avec TLS. Le certificat SSL/TLS est négocié avant l\'échange HTTP. Les données sont chiffrées — Wireshark voit le trafic TLS mais pas le contenu HTTP.</p>
<h3>FTP (ports TCP 20/21) et SFTP/SCP (port TCP 22)</h3>
<p>FTP transfère des fichiers en clair (déprécié). SFTP et SCP utilisent le tunnel SSH pour le chiffrement.</p>'],
         ['title' => '3. Protocoles d\'administration et monitoring', 'subchapter' => 0, 'content' => '<h3>SSH — Secure Shell (port TCP 22)</h3>
<p>SSH est le standard pour l\'administration à distance sécurisée. Il remplace Telnet (port 23, non chiffré). SSH offre :</p>
<ul>
<li>Chiffrement de la session complète</li>
<li>Authentification par mot de passe ou clé publique/privée (plus sécurisé)</li>
<li>Transfert de fichiers (SFTP, SCP)</li>
<li>Tunneling de ports (port forwarding)</li>
</ul>
<h3>SNMP — Simple Network Management Protocol (port UDP 161/162)</h3>
<p>SNMP permet la surveillance et gestion des équipements réseau. Composants :</p>
<ul>
<li><strong>Agent SNMP :</strong> S\'exécute sur l\'équipement surveillé (switch, routeur)</li>
<li><strong>Gestionnaire SNMP :</strong> Collecte les données (Zabbix, Nagios, LibreNMS)</li>
<li><strong>MIB :</strong> Base d\'information de gestion — définit les OIDs disponibles</li>
<li><strong>Traps :</strong> Alertes proactives envoyées par l\'équipement (port 162)</li>
</ul>
<p><strong>Versions :</strong> SNMPv1/v2c (communauté en clair), SNMPv3 (authentification + chiffrement).</p>
<h3>NTP — Network Time Protocol (port UDP 123)</h3>
<p>Synchronise les horloges des systèmes. Critique pour la corrélation des logs, les certificats TLS et Kerberos.</p>'],
     ]],

    ['section' => 4, 'type' => 'quiz', 'name' => 'Quiz — Module 4 : Protocoles applicatifs',
     'intro' => '<p>Quiz formatif — DNS, DHCP, HTTP, SSH, SNMP et leurs ports. <strong>2 tentatives · Résultats immédiats.</strong></p>',
     'attempts' => 2, 'timelimit' => 0, 'gradepass' => 70,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => 'Cours 1 — Module 4 — Protocoles applicatifs',
     'idnumber' => 'C1-QUIZ-MOD04'],

    ['section' => 4, 'type' => 'assign', 'name' => 'Labo 4 — Analyse de protocoles applicatifs dans Wireshark',
     'intro' => '<h4>Objectif</h4>
<p>Identifier et analyser les protocoles DNS, DHCP, HTTP et ICMP dans des captures Wireshark.</p>
<h4>Tâches</h4>
<ol>
<li><strong>DNS :</strong> Capturer une requête DNS. Identifier la question, la réponse, le TTL et le serveur DNS utilisé (filtre: <code>dns</code>).</li>
<li><strong>HTTP :</strong> Capturer une requête GET HTTP vers neverssl.com. Identifier les méthodes, codes de statut et en-têtes (filtre: <code>http</code>).</li>
<li><strong>ICMP :</strong> Ping 8.8.8.8 et identifier le type ICMP (Echo Request / Echo Reply) et le TTL retourné.</li>
<li><strong>Défi :</strong> À partir d\'une capture Wireshark fournie par l\'instructeur, identifier les 5 protocoles présents et expliquer leur rôle.</li>
</ol>
<h4>Livrables</h4>
<ul>
<li>Captures Wireshark annotées (PNG) pour chaque protocole analysé</li>
<li>Tableau récapitulatif : Protocole | Port | Couche OSI | Rôle | Observation Wireshark</li>
</ul>',
     'grade' => 25, 'idnumber' => 'C1-LAB-04'],

    // --- SECTION 5 : Module 5 — Outils ---
    ['section' => 5, 'type' => 'book', 'name' => 'Théorie — Module 5 : Outils de diagnostic fondamentaux',
     'intro' => '<p>Les outils de diagnostic sont les premiers que vous utiliserez lors d\'un incident NOC. Ce module vous donne la syntaxe précise, l\'interprétation des résultats et la méthodologie de troubleshooting.</p>',
     'chapters' => [
         ['title' => '1. ping — Tester la connectivité', 'subchapter' => 0, 'content' => '<h3>La commande ping</h3>
<p>ping utilise le protocole ICMP (Echo Request / Echo Reply) pour tester si un hôte est joignable et mesurer le temps de réponse (RTT — Round Trip Time).</p>
<h4>Syntaxe Linux</h4>
<pre><code>ping -c 4 8.8.8.8          # 4 paquets vers Google DNS
ping -c 4 -s 1400 192.168.1.1  # Paquets de 1400 octets (test MTU)
ping -i 0.2 -c 100 10.0.0.1  # 100 paquets, intervalle 0.2s (test de charge)</code></pre>
<h4>Interprétation des résultats</h4>
<ul>
<li><strong>Reply from X : bytes=32 time=2ms TTL=64</strong> → Succès. TTL=64 suggère un Linux/réseau local.</li>
<li><strong>Request timeout</strong> → Paquet envoyé, pas de réponse. Pare-feu ? Hôte éteint ? Mauvais routage ?</li>
<li><strong>Destination Host Unreachable</strong> → Pas de route vers la destination (ICMP type 3 du routeur local)</li>
<li><strong>Network is unreachable</strong> → Pas d\'interface active ou pas de route par défaut</li>
</ul>
<h4>Méthodologie bottom-up avec ping</h4>
<ol>
<li>ping 127.0.0.1 → Valide la pile TCP/IP locale</li>
<li>ping [ma propre IP] → Valide la carte réseau</li>
<li>ping [passerelle] → Valide le LAN local</li>
<li>ping 8.8.8.8 → Valide la connectivité Internet (IP)</li>
<li>ping google.com → Valide la résolution DNS + Internet</li>
</ol>'],
         ['title' => '2. traceroute — Cartographier le chemin', 'subchapter' => 0, 'content' => '<h3>La commande traceroute / tracert</h3>
<p>traceroute révèle le chemin emprunté par les paquets en exploitant le mécanisme TTL d\'IP.</p>
<h4>Principe</h4>
<p>traceroute envoie des paquets avec TTL=1, 2, 3... Chaque routeur qui reçoit un paquet avec TTL=0 envoie un ICMP "Time Exceeded" en retour. traceroute collecte ces réponses pour cartographier le chemin.</p>
<pre><code>traceroute google.com        # Linux (UDP par défaut)
traceroute -I google.com    # Linux avec ICMP
tracert google.com          # Windows (ICMP)</code></pre>
<h4>Interprétation</h4>
<ul>
<li><strong>* * *</strong> → Routeur ne répond pas aux ICMP Time Exceeded (mais peut transmettre le trafic)</li>
<li><strong>!X ou !N</strong> → Destination inaccessible signalée par un routeur intermédiaire</li>
<li><strong>RTT élevé soudain</strong> → Lien lent ou congestionné entre deux sauts</li>
<li><strong>RTT qui augmente puis diminue</strong> → Normal — les réponses ICMP ont moins de priorité que le trafic réel</li>
</ul>'],
         ['title' => '3. nslookup, dig, netstat, arp', 'subchapter' => 0, 'content' => '<h3>nslookup et dig — Diagnostic DNS</h3>
<pre><code>nslookup moodle.monecole.ca      # Résolution simple
nslookup -type=MX monecole.ca    # Enregistrements mail
dig moodle.monecole.ca           # Réponse détaillée
dig MX monecole.ca               # Enregistrements MX
dig @8.8.8.8 moodle.monecole.ca  # Interroger un DNS spécifique
dig -x 192.168.1.100             # Résolution inverse</code></pre>
<h3>ss et netstat — Connexions actives et ports</h3>
<pre><code>ss -tuln                # Ports en écoute (TCP+UDP, numérique)
ss -tanp                # Connexions TCP actives avec PID
netstat -tuln           # Ancienne commande équivalente</code></pre>
<h3>arp — Table ARP locale</h3>
<pre><code>arp -a                  # Afficher la table ARP
arp -d 192.168.1.1      # Supprimer une entrée ARP</code></pre>
<h3>ip — Configuration réseau (Linux moderne)</h3>
<pre><code>ip addr show            # Afficher les adresses IP de toutes les interfaces
ip route show           # Afficher la table de routage
ip link show            # État des interfaces (up/down)</code></pre>'],
     ]],

    ['section' => 5, 'type' => 'quiz', 'name' => 'Quiz — Module 5 : Outils de diagnostic',
     'intro' => '<p>Quiz formatif — ping, traceroute, Wireshark, netstat, arp, dig. <strong>2 tentatives · Résultats immédiats.</strong></p>',
     'attempts' => 2, 'timelimit' => 0, 'gradepass' => 70,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => 'Cours 1 — Module 5 — Outils de diagnostic',
     'idnumber' => 'C1-QUIZ-MOD05'],

    ['section' => 5, 'type' => 'assign', 'name' => 'Labo 5 — Diagnostic réseau structuré',
     'intro' => '<h4>Scénario — Incident NOC simulé</h4>
<p>Vous êtes technicien NOC. Un utilisateur signale qu\'il ne peut pas accéder à <code>moodle.monecole.ca</code> depuis son poste. Votre mission : diagnostiquer le problème en utilisant la méthodologie bottom-up et les outils de diagnostic.</p>
<h4>Tâches</h4>
<ol>
<li>Appliquer la méthodologie bottom-up complète (ping 127.0.0.1 → passerelle → 8.8.8.8 → DNS).</li>
<li>Utiliser traceroute vers 8.8.8.8 et vers un domaine public. Identifier le nombre de sauts et les temps de réponse.</li>
<li>Vérifier la table ARP locale et la table de routage.</li>
<li>Utiliser dig pour vérifier la résolution DNS de moodle.monecole.ca.</li>
<li>Documenter chaque commande exécutée, son résultat et votre interprétation.</li>
</ol>
<h4>Rapport (PDF)</h4>
<ul>
<li>Tableau de diagnostic : Commande | Résultat | Interprétation | Couche OSI testée</li>
<li>Conclusion : À quelle couche OSI le problème se situe-t-il et pourquoi ?</li>
<li>Recommandations pour résoudre le problème identifié</li>
</ul>',
     'grade' => 25, 'idnumber' => 'C1-LAB-05'],

    // --- SECTION 6 : Module 6 — Documentation NOC ---
    ['section' => 6, 'type' => 'book', 'name' => 'Théorie — Module 6 : Documentation et nomenclature NOC',
     'intro' => '<p>La documentation est aussi importante que la configuration technique. Un bon technicien NOC laisse des traces claires pour ses collègues et pour les interventions futures.</p>',
     'chapters' => [
         ['title' => '1. Pourquoi documenter — La culture NOC', 'subchapter' => 0, 'content' => '<h3>La documentation comme outil opérationnel</h3>
<p>Dans un NOC, la documentation permet :</p>
<ul>
<li><strong>Résolution rapide des incidents :</strong> Le technicien du quart de nuit sait exactement où regarder</li>
<li><strong>Continuité des opérations :</strong> Passage de relève sans perte d\'information</li>
<li><strong>Gestion des changements :</strong> Traçabilité de qui a modifié quoi et quand</li>
<li><strong>Formation :</strong> Les nouveaux techniciens apprennent de la documentation existante</li>
<li><strong>Conformité et audits :</strong> Preuves d\'une gestion rigoureuse de l\'infrastructure</li>
</ul>'],
         ['title' => '2. Conventions de nommage NOC', 'subchapter' => 0, 'content' => '<h3>Structure d\'un nom d\'équipement NOC</h3>
<p>Format recommandé : <code>[SITE]-[LOC]-[TYPE]-[ROLE]-[NUM]</code></p>
<table class="table table-bordered table-sm">
<thead><tr><th>Composant</th><th>Exemples</th><th>Description</th></tr></thead>
<tbody>
<tr><td>SITE</td><td>MTL, QC, LAV, SHE</td><td>Ville/site géographique</td></tr>
<tr><td>LOC</td><td>POP1, DC2, BLDG-A</td><td>Point de présence / bâtiment</td></tr>
<tr><td>TYPE</td><td>RTR, SW, FW, SRV</td><td>Routeur, Switch, Firewall, Serveur</td></tr>
<tr><td>ROLE</td><td>CORE, EDGE, AGG, ACC</td><td>Rôle dans la topologie</td></tr>
<tr><td>NUM</td><td>01, 02</td><td>Numéro séquentiel</td></tr>
</tbody>
</table>
<p><strong>Exemples :</strong><br>
<code>MTL-POP1-RTR-EDGE-01</code> — Routeur de bordure #1 au POP1 de Montréal<br>
<code>QC-DC2-SW-CORE-02</code> — Switch core #2 au datacenter 2 de Québec</p>'],
         ['title' => '3. Schémas de topologie avec draw.io', 'subchapter' => 0, 'content' => '<h3>draw.io — Outil de diagramme gratuit</h3>
<p>draw.io (diagrams.net) est disponible en ligne ou hors ligne. Il inclut des bibliothèques de symboles réseau Cisco.</p>
<h4>Types de diagrammes réseau</h4>
<ul>
<li><strong>Topologie physique :</strong> Emplacement réel des équipements, tracé des câbles, panneaux de brassage</li>
<li><strong>Topologie logique :</strong> Adresses IP, VLANs, protocoles de routage — sans emplacement physique</li>
<li><strong>Topologie L2 :</strong> VLANs, trunks, STP root bridge</li>
<li><strong>Topologie L3 :</strong> Sous-réseaux, interfaces IP, routes statiques/dynamiques</li>
</ul>
<h4>Bonnes pratiques</h4>
<ul>
<li>Utiliser les symboles standardisés Cisco (disponibles dans draw.io)</li>
<li>Annoter chaque lien : type de câble, vitesse, VLAN ou adresses IP des interfaces</li>
<li>Exporter en PNG pour documentation et en SVG pour modification future</li>
<li>Versionner les diagrammes dans git (format XML de draw.io)</li>
</ul>'],
     ]],

    ['section' => 6, 'type' => 'quiz', 'name' => 'Quiz — Module 6 : Documentation NOC',
     'intro' => '<p>Quiz formatif — conventions NOC, draw.io, inventaire, ticketing. <strong>2 tentatives · Résultats immédiats.</strong></p>',
     'attempts' => 2, 'timelimit' => 0, 'gradepass' => 70,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => 'Cours 1 — Module 6 — Documentation NOC',
     'idnumber' => 'C1-QUIZ-MOD06'],

    ['section' => 6, 'type' => 'assign', 'name' => 'Labo 6 — Documenter une topologie réseau avec draw.io',
     'intro' => '<h4>Objectif</h4>
<p>Créer la documentation complète d\'une topologie réseau selon les standards NOC.</p>
<h4>Tâches</h4>
<ol>
<li>Utiliser la topologie créée dans le Labo 3 (Packet Tracer) comme base.</li>
<li>Créer un <strong>schéma de topologie logique</strong> dans draw.io :
<ul>
<li>Symboles Cisco pour chaque équipement</li>
<li>Noms d\'équipements selon la convention NOC (ex. MTL-POP1-RTR-01)</li>
<li>Adresses IP annotées sur chaque interface</li>
<li>Type de lien et vitesse sur chaque connexion</li>
</ul></li>
<li>Créer un <strong>tableau d\'inventaire réseau</strong> (CSV ou tableur) : Hostname | Type | IP gestion | Interface | VLAN | Localisation</li>
<li>Créer un <strong>plan d\'adressage IP</strong> : Réseau | Masque | Préfixe CIDR | Usage | VLAN | Passerelle</li>
</ol>
<h4>Livrables</h4>
<ul>
<li>Fichier draw.io (.xml) + Export PNG du schéma</li>
<li>Tableau d\'inventaire réseau (CSV ou XLSX)</li>
<li>Plan d\'adressage IP (CSV ou XLSX)</li>
</ul>',
     'grade' => 25, 'idnumber' => 'C1-LAB-06'],

    // --- SECTION 7 : Évaluation finale ---
    ['section' => 7, 'type' => 'quiz', 'name' => 'Examen sommatif final — Cours 1',
     'intro' => '<p><strong>Examen sommatif</strong> couvrant l\'ensemble des 6 modules du cours.</p>
<ul>
<li>30 questions aléatoires issues de la banque de questions de tous les modules</li>
<li>60 minutes · 1 seule tentative · Note de passage : 70%</li>
<li>Questions présentées une par une, dans un ordre aléatoire</li>
</ul>
<p><em>Assurez-vous d\'avoir complété tous les modules et laboratoires avant de commencer cet examen.</em></p>',
     'attempts' => 1, 'timelimit' => 3600, 'gradepass' => 70,
     'questionsperpage' => 1, 'shuffleanswers' => 1,
     'gift_category' => null, // Questions random de toutes les catégories du cours
     'idnumber' => 'C1-QUIZ-FINAL'],

    ['section' => 7, 'type' => 'assign', 'name' => 'Projet intégrateur — Documentation complète d\'un réseau d\'entreprise',
     'intro' => '<h4>Objectif du projet</h4>
<p>Intégrer les compétences des 6 modules pour documenter, analyser et présenter une infrastructure réseau complète, selon les standards NOC.</p>
<h4>Scénario</h4>
<p>Vous êtes technicien réseau pour une PME de 50 employés répartis sur 2 étages. L\'entreprise vient de vous demander de documenter leur réseau existant avant une migration.</p>
<h4>Livrables du projet</h4>
<ol>
<li><strong>Schéma de topologie logique complet</strong> (draw.io) :
   Minimum 1 routeur, 2 switches, segmentation en 3 VLANs (administration, employés, IoT)</li>
<li><strong>Plan d\'adressage IP</strong> avec sous-réseaux justifiés pour chaque VLAN</li>
<li><strong>Inventaire réseau</strong> de tous les équipements avec convention de nommage NOC</li>
<li><strong>Tableau des protocoles</strong> : pour chaque protocole utilisé (DNS, DHCP, HTTP, SSH...), documenter le port, la couche OSI et son rôle dans ce réseau spécifique</li>
<li><strong>Procédure de diagnostic</strong> (runbook simplifié, 1 page) : que faire si un utilisateur ne peut pas accéder à Internet ?</li>
</ol>
<h4>Critères d\'évaluation</h4>
<ul>
<li>Complétude et cohérence du plan d\'adressage (25%)</li>
<li>Qualité et clarté des schémas draw.io (25%)</li>
<li>Respect des conventions de nommage NOC (25%)</li>
<li>Qualité du runbook de diagnostic (25%)</li>
</ul>
<p><strong>Format de soumission :</strong> Archive ZIP contenant tous les fichiers · Note : 30% de la note finale du cours</p>',
     'grade' => 30, 'idnumber' => 'C1-PROJ-FINAL'],
];

// ============================================================
// 3. FONCTIONS UTILITAIRES
// ============================================================

function get_module_id(string $name): int {
    global $DB;
    $id = $DB->get_field('modules', 'id', ['name' => $name]);
    if (!$id) {
        throw new Exception("Module Moodle '{$name}' introuvable en base de données.");
    }
    return (int) $id;
}

function activity_exists(int $courseid, string $idnumber): bool {
    global $DB;
    return $DB->record_exists('course_modules', ['course' => $courseid, 'idnumber' => $idnumber]);
}

function update_section(int $courseid, int $sectionnum, string $name, string $summary, bool $dry_run): void {
    global $DB;
    $section = $DB->get_record('course_sections', ['course' => $courseid, 'section' => $sectionnum]);
    if (!$section) {
        // Créer la section si elle n'existe pas
        $section = new stdClass();
        $section->course = $courseid;
        $section->section = $sectionnum;
        $section->name = $name;
        $section->summary = $summary;
        $section->summaryformat = FORMAT_HTML;
        $section->visible = 1;
        $section->sequence = '';
        $section->availability = null;
        if (!$dry_run) {
            $DB->insert_record('course_sections', $section);
        }
    } else {
        $section->name = $name;
        $section->summary = $summary;
        $section->summaryformat = FORMAT_HTML;
        if (!$dry_run) {
            $DB->update_record('course_sections', $section);
        }
    }
    parcours_log("  Section {$sectionnum} mise à jour : {$name}", 'success');
}

function create_book(int $courseid, int $sectionnum, array $def, bool $dry_run): ?int {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/book/lib.php');

    $moduleinfo = new stdClass();
    $moduleinfo->modulename      = 'book';
    $moduleinfo->module          = get_module_id('book');
    $moduleinfo->course          = $courseid;
    $moduleinfo->section         = $sectionnum;
    $moduleinfo->visible         = 1;
    $moduleinfo->name            = $def['name'];
    $moduleinfo->intro           = $def['intro'] ?? '';
    $moduleinfo->introformat     = FORMAT_HTML;
    $moduleinfo->numbering       = 1; // numérotation séquentielle
    $moduleinfo->navstyle        = 1; // images
    $moduleinfo->customtitles    = 0;
    $moduleinfo->coursemodule    = 0;
    $moduleinfo->completion      = COMPLETION_TRACKING_MANUAL;
    $moduleinfo->completionview  = 1;
    $moduleinfo->idnumber        = $def['idnumber'] ?? '';

    if ($dry_run) {
        parcours_log("  [DRY-RUN] Créerait Livre : {$def['name']}", 'info');
        return null;
    }

    $result = add_moduleinfo($moduleinfo, get_course($courseid));
    $cmid   = $result->coursemodule;

    // Récupérer l'ID du livre en base
    $cm     = get_coursemodule_from_id('book', $cmid);
    $bookid = $cm->instance;

    // Ajouter les chapitres
    $pagenum = 1;
    foreach (($def['chapters'] ?? []) as $chap) {
        $chapter = new stdClass();
        $chapter->bookid        = $bookid;
        $chapter->pagenum       = $pagenum++;
        $chapter->subchapter    = $chap['subchapter'] ?? 0;
        $chapter->title         = $chap['title'];
        $chapter->content       = $chap['content'];
        $chapter->contentformat = FORMAT_HTML;
        $chapter->hidden        = 0;
        $chapter->timecreated   = time();
        $chapter->timemodified  = time();
        $chapter->importsrc     = '';
        $DB->insert_record('book_chapters', $chapter);
    }

    // Mettre à jour le compteur de chapitres
    $DB->set_field('book', 'revision', 1, ['id' => $bookid]);

    parcours_log("  Livre créé : {$def['name']} (cmid={$cmid}, chapters=" . count($def['chapters'] ?? []) . ")", 'success');
    return $cmid;
}

function create_quiz(int $courseid, int $sectionnum, array $def, bool $dry_run): ?int {
    global $DB, $CFG;

    $moduleinfo = new stdClass();
    $moduleinfo->modulename                = 'quiz';
    $moduleinfo->module                    = get_module_id('quiz');
    $moduleinfo->course                    = $courseid;
    $moduleinfo->section                   = $sectionnum;
    $moduleinfo->visible                   = 1;
    $moduleinfo->name                      = $def['name'];
    $moduleinfo->intro                     = $def['intro'] ?? '';
    $moduleinfo->introformat               = FORMAT_HTML;
    $moduleinfo->timeopen                  = 0;
    $moduleinfo->timeclose                 = 0;
    $moduleinfo->timelimit                 = $def['timelimit'] ?? 0;
    $moduleinfo->overduehandling           = 'autosubmit';
    $moduleinfo->graceperiod               = 0;
    $moduleinfo->preferredbehaviour        = 'deferredfeedback';
    $moduleinfo->canredoquestions          = 0;
    $moduleinfo->attempts                  = $def['attempts'] ?? 0;
    $moduleinfo->attemptonlast             = 0;
    $moduleinfo->grademethod               = 1; // Meilleure note
    $moduleinfo->decimalpoints             = 2;
    $moduleinfo->questiondecimalpoints     = -1;
    $moduleinfo->reviewattempt             = 69904;
    $moduleinfo->reviewcorrectness         = 69904;
    $moduleinfo->reviewmarks               = 69904;
    $moduleinfo->reviewspecificfeedback    = 69904;
    $moduleinfo->reviewgeneralfeedback     = 69904;
    $moduleinfo->reviewrightanswer         = 69904;
    $moduleinfo->reviewoverallfeedback     = 69904;
    $moduleinfo->questionsperpage          = $def['questionsperpage'] ?? 1;
    $moduleinfo->shuffleanswers            = $def['shuffleanswers'] ?? 1;
    $moduleinfo->grade                     = 100;
    $moduleinfo->sumgrades                 = 0;
    $moduleinfo->quizpassword              = ''; // converti en 'password' par quiz_add_instance
    $moduleinfo->subnet                    = '';
    $moduleinfo->browsersecurity           = '-';
    $moduleinfo->delay1                    = 0;
    $moduleinfo->delay2                    = 0;
    $moduleinfo->showuserpicture           = 0;
    $moduleinfo->showblocks                = 0;
    $moduleinfo->coursemodule              = 0;
    $moduleinfo->completion                = COMPLETION_TRACKING_AUTOMATIC;
    $moduleinfo->completionview            = 0;
    $moduleinfo->completionusegrade        = 1;
    $moduleinfo->completionpassgrade       = ($def['gradepass'] ?? 0) > 0 ? 1 : 0;
    $moduleinfo->completionexpected        = 0;
    $moduleinfo->idnumber                  = $def['idnumber'] ?? '';

    if ($dry_run) {
        parcours_log("  [DRY-RUN] Créerait Quiz : {$def['name']}", 'info');
        return null;
    }

    $result = add_moduleinfo($moduleinfo, get_course($courseid));
    $cmid   = $result->coursemodule;

    // Définir la note de passage si applicable
    if (($def['gradepass'] ?? 0) > 0) {
        $cm = get_coursemodule_from_id('quiz', $cmid);
        $gradeitem = $DB->get_record('grade_items', [
            'courseid'   => $courseid,
            'itemtype'   => 'mod',
            'itemmodule' => 'quiz',
            'iteminstance' => $cm->instance,
        ]);
        if ($gradeitem) {
            $gradeitem->gradepass = $def['gradepass'];
            $DB->update_record('grade_items', $gradeitem);
        }
    }

    parcours_log("  Quiz créé : {$def['name']} (cmid={$cmid})", 'success');
    return $cmid;
}

function create_assign(int $courseid, int $sectionnum, array $def, bool $dry_run): ?int {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/assign/lib.php');

    $moduleinfo = new stdClass();
    $moduleinfo->modulename                      = 'assign';
    $moduleinfo->module                          = get_module_id('assign');
    $moduleinfo->course                          = $courseid;
    $moduleinfo->section                         = $sectionnum;
    $moduleinfo->visible                         = 1;
    $moduleinfo->name                            = $def['name'];
    $moduleinfo->intro                           = $def['intro'] ?? '';
    $moduleinfo->introformat                     = FORMAT_HTML;
    $moduleinfo->alwaysshowdescription           = 1;
    $moduleinfo->submissiondrafts                = 0;
    $moduleinfo->sendnotifications               = 0;
    $moduleinfo->sendlatenotifications           = 0;
    $moduleinfo->duedate                         = 0;
    $moduleinfo->allowsubmissionsfromdate        = 0;
    $moduleinfo->grade                           = $def['grade'] ?? 100;
    $moduleinfo->cutoffdate                      = 0;
    $moduleinfo->gradingduedate                  = 0;
    $moduleinfo->maxattempts                     = -1;
    $moduleinfo->teamsubmission                  = 0;
    $moduleinfo->requireallteammemberssubmit     = 0;
    $moduleinfo->teamsubmissiongroupingid        = 0;
    $moduleinfo->blindmarking                    = 0;
    $moduleinfo->hidegrader                      = 0;
    $moduleinfo->requiresubmissionstatement      = 0;
    $moduleinfo->markingworkflow                 = 0;
    $moduleinfo->markingallocation               = 0;
    $moduleinfo->attemptreopenmethod             = 'none';
    $moduleinfo->assignsubmission_file_enabled   = 1;
    $moduleinfo->assignsubmission_file_maxfiles  = 5;
    $moduleinfo->assignsubmission_file_maxsizebytes = 20971520; // 20MB
    $moduleinfo->assignsubmission_onlinetext_enabled = 0;
    $moduleinfo->assignfeedback_comments_enabled = 1;
    $moduleinfo->coursemodule                    = 0;
    $moduleinfo->completion                      = COMPLETION_TRACKING_AUTOMATIC;
    $moduleinfo->completionview                  = 0;
    $moduleinfo->completionusegrade              = 1;
    $moduleinfo->completionsubmit                = 1;
    $moduleinfo->idnumber                        = $def['idnumber'] ?? '';

    if ($dry_run) {
        parcours_log("  [DRY-RUN] Créerait Devoir : {$def['name']}", 'info');
        return null;
    }

    $result = add_moduleinfo($moduleinfo, get_course($courseid));
    $cmid   = $result->coursemodule;

    parcours_log("  Devoir créé : {$def['name']} (cmid={$cmid}, note/{$def['grade']})", 'success');
    return $cmid;
}

function create_label(int $courseid, int $sectionnum, array $def, bool $dry_run): ?int {
    $moduleinfo = new stdClass();
    $moduleinfo->modulename   = 'label';
    $moduleinfo->module       = get_module_id('label');
    $moduleinfo->course       = $courseid;
    $moduleinfo->section      = $sectionnum;
    $moduleinfo->visible      = 1;
    $moduleinfo->name         = $def['name'];
    $moduleinfo->intro        = $def['intro'] ?? '';
    $moduleinfo->introformat  = FORMAT_HTML;
    $moduleinfo->coursemodule = 0;
    $moduleinfo->completion   = COMPLETION_TRACKING_NONE;

    if ($dry_run) {
        parcours_log("  [DRY-RUN] Créerait Étiquette : {$def['name']}", 'info');
        return null;
    }

    $result = add_moduleinfo($moduleinfo, get_course($courseid));
    $cmid   = $result->coursemodule;
    parcours_log("  Étiquette créée : {$def['name']} (cmid={$cmid})", 'success');
    return $cmid;
}

function import_gift_questions(int $courseid, string $gift_category, string $gift_file, bool $dry_run): ?int {
    global $DB;

    if (!file_exists($gift_file)) {
        parcours_log("  Fichier GIFT introuvable : {$gift_file}", 'error');
        return null;
    }

    $context = context_course::instance($courseid);

    // S'assurer que les catégories par défaut existent pour ce cours
    question_make_default_categories([$context]);

    // Créer ou récupérer la catégorie de questions
    $cat_name = $gift_category;
    $parent_cat = question_get_default_category($context->id);

    // Chercher si la catégorie existe déjà
    $existing_cat = $DB->get_record('question_categories', [
        'name'      => $cat_name,
        'contextid' => $context->id,
    ]);

    if ($existing_cat) {
        parcours_log("  Catégorie de questions existante : {$cat_name} (id={$existing_cat->id})", 'info');
        $category = $existing_cat;
    } else {
        if (!$dry_run) {
            $category = new stdClass();
            $category->name        = $cat_name;
            $category->contextid   = $context->id;
            $category->info        = '';
            $category->infoformat  = FORMAT_HTML;
            $category->parent      = $parent_cat->id;
            $category->sortorder   = 999;
            $category->stamp       = make_unique_id_code();
            $category->id          = $DB->insert_record('question_categories', $category);
            parcours_log("  Catégorie créée : {$cat_name} (id={$category->id})", 'success');
        } else {
            parcours_log("  [DRY-RUN] Créerait catégorie questions : {$cat_name}", 'info');
            return null;
        }
    }

    if ($dry_run) {
        parcours_log("  [DRY-RUN] Importerait questions depuis : " . basename($gift_file), 'info');
        return null;
    }

    // Importer les questions GIFT
    $qformat = new qformat_gift();
    $qformat->setCategory($category);
    $qformat->setContexts([$context]);
    $qformat->setCourse(get_course($courseid));
    $qformat->setFilename($gift_file);
    $qformat->setRealfilename(basename($gift_file));
    $qformat->setMatchgrades('error');
    $qformat->setCatfromfile(false);
    $qformat->setContextfromfile(false);
    $qformat->setStoponerror(false);

    ob_start();
    $ok = $qformat->importpreprocess();
    ob_end_clean();

    if (!$ok) {
        parcours_log("  Erreur lors du prétraitement GIFT : " . basename($gift_file), 'error');
        return null;
    }

    ob_start();
    $ok = $qformat->importprocess();
    $output = ob_get_clean();

    // Moodle 4.x : questions liées via question_bank_entries (plus de colonne 'category' dans question)
    $count = $DB->count_records('question_bank_entries', ['questioncategoryid' => $category->id]);
    parcours_log("  Questions importées : {$count} dans la catégorie '{$cat_name}'", 'success');

    return $category->id;
}

function add_questions_to_quiz(int $courseid, int $cmid, int $cat_id, int $count, bool $dry_run): void {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    require_once($CFG->dirroot . '/mod/quiz/lib.php');

    if ($dry_run) {
        parcours_log("  [DRY-RUN] Ajouterait {$count} questions au quiz cmid={$cmid}", 'info');
        return;
    }

    $cm   = get_coursemodule_from_id('quiz', $cmid);
    $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

    // Moodle 4.x : récupérer les question IDs via question_bank_entries + question_versions
    $sql = "SELECT q.id
              FROM {question} q
              JOIN {question_versions} qv ON qv.questionid = q.id
              JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
             WHERE qbe.questioncategoryid = :catid
               AND qv.status = 'ready'
          ORDER BY q.id ASC";
    $question_ids = $DB->get_fieldset_sql($sql, ['catid' => $cat_id]);
    $question_ids = array_slice($question_ids, 0, $count);

    if (empty($question_ids)) {
        parcours_log("  Aucune question trouvée dans la catégorie id={$cat_id}", 'warning');
        return;
    }

    $page = 1;
    $added = 0;
    foreach ($question_ids as $qid) {
        quiz_add_quiz_question($qid, $quiz, $page, 1);
        $page++;
        $added++;
    }

    // Recalculer les grades du quiz
    quiz_update_sumgrades($quiz);

    parcours_log("  {$added} questions ajoutées au quiz", 'success');
}

function add_random_questions_to_quiz(int $courseid, int $cmid, array $cat_ids, int $total, bool $dry_run): void {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
    require_once($CFG->dirroot . '/mod/quiz/lib.php');

    if ($dry_run || empty($cat_ids)) {
        parcours_log("  [DRY-RUN] Ajouterait {$total} questions aléatoires au quiz sommatif", 'info');
        return;
    }

    $cm   = get_coursemodule_from_id('quiz', $cmid);
    $quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

    // Moodle 4.x : collecter les IDs depuis question_bank_entries
    $all_questions = [];
    foreach ($cat_ids as $cat_id) {
        $sql = "SELECT q.id
                  FROM {question} q
                  JOIN {question_versions} qv ON qv.questionid = q.id
                  JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
                 WHERE qbe.questioncategoryid = :catid
                   AND qv.status = 'ready'";
        $ids = $DB->get_fieldset_sql($sql, ['catid' => $cat_id]);
        $all_questions = array_merge($all_questions, $ids);
    }

    // Mélanger et prendre $total questions
    shuffle($all_questions);
    $selected = array_slice($all_questions, 0, $total);

    $page  = 1;
    $added = 0;
    foreach ($selected as $qid) {
        quiz_add_quiz_question($qid, $quiz, $page, 1);
        $page++;
        $added++;
    }

    quiz_update_sumgrades($quiz);
    parcours_log("  {$added} questions aléatoires ajoutées au quiz sommatif", 'success');
}

// ============================================================
// 4. EXÉCUTION PRINCIPALE
// ============================================================
$errors      = 0;
$quiz_cmids  = []; // idnumber => cmid, pour lier les questions après
$cat_ids     = []; // gift_category => cat_id

// 4.1 — Mettre à jour les sections
parcours_log("\n--- Mise à jour des sections ---");
foreach ($sections_def as $num => $secdef) {
    if (!$dry_run) {
        update_section($courseid, $num, $secdef['name'], $secdef['summary'], $dry_run);
    } else {
        parcours_log("  [DRY-RUN] Section {$num} : {$secdef['name']}", 'info');
    }
}

// 4.2 — Créer les activités
parcours_log("\n--- Création des activités ---");

$gift_base = __DIR__ . '/../courses/cours-01-fondements/quizzes/';
$gift_files = [
    'Cours 1 — Diagnostic'                        => $gift_base . 'diagnostic.gift',
    'Cours 1 — Module 1 — Modèle OSI'             => $gift_base . 'module-01-osi.gift',
    'Cours 1 — Module 2 — TCP/IP et encapsulation'=> $gift_base . 'module-02-tcpip.gift',
    'Cours 1 — Module 3 — Médias et équipements'  => $gift_base . 'module-03-medias.gift',
    'Cours 1 — Module 4 — Protocoles applicatifs' => $gift_base . 'module-04-protocoles.gift',
    'Cours 1 — Module 5 — Outils de diagnostic'   => $gift_base . 'module-05-outils.gift',
    'Cours 1 — Module 6 — Documentation NOC'      => $gift_base . 'module-06-documentation.gift',
];

foreach ($activities_def as $actdef) {
    $section = $actdef['section'];
    $type    = $actdef['type'];
    $idnumber = $actdef['idnumber'] ?? '';

    parcours_log("\n  Section {$section} — [{$type}] {$actdef['name']}");

    // Vérifier si l'activité existe déjà (par idnumber)
    if ($idnumber && !$force && activity_exists($courseid, $idnumber)) {
        parcours_log("  Existe déjà (idnumber={$idnumber}) — ignoré. Utiliser --force pour recréer.", 'warning');
        // Récupérer le cmid existant pour lier les questions quiz
        if ($type === 'quiz') {
            $cm = $DB->get_record('course_modules', ['course' => $courseid, 'idnumber' => $idnumber]);
            if ($cm) $quiz_cmids[$idnumber] = $cm->id;
        }
        continue;
    }

    try {
        $cmid = null;
        switch ($type) {
            case 'label':
                $cmid = create_label($courseid, $section, $actdef, $dry_run);
                break;
            case 'book':
                $actdef['idnumber'] = 'C1-BOOK-MOD0' . $section;
                $cmid = create_book($courseid, $section, $actdef, $dry_run);
                break;
            case 'quiz':
                $cmid = create_quiz($courseid, $section, $actdef, $dry_run);
                if ($cmid && $idnumber) {
                    $quiz_cmids[$idnumber] = $cmid;
                }
                break;
            case 'assign':
                $cmid = create_assign($courseid, $section, $actdef, $dry_run);
                break;
        }
    } catch (Exception $e) {
        parcours_log("  ERREUR : " . $e->getMessage(), 'error');
        $errors++;
    }
}

// 4.3 — Importer les questions GIFT dans la banque de questions
parcours_log("\n--- Import des questions (banque de questions) ---");
foreach ($gift_files as $cat_name => $gift_file) {
    parcours_log("\n  Catégorie : {$cat_name}");
    $cat_id = import_gift_questions($courseid, $cat_name, $gift_file, $dry_run);
    if ($cat_id) {
        $cat_ids[$cat_name] = $cat_id;
    }
}

// 4.4 — Lier les questions aux quiz
parcours_log("\n--- Liaison questions → quiz ---");

$quiz_question_map = [
    'C1-QUIZ-DIAG'  => ['category' => 'Cours 1 — Diagnostic',                        'count' => 20],
    'C1-QUIZ-MOD01' => ['category' => 'Cours 1 — Module 1 — Modèle OSI',             'count' => 15],
    'C1-QUIZ-MOD02' => ['category' => 'Cours 1 — Module 2 — TCP/IP et encapsulation','count' => 15],
    'C1-QUIZ-MOD03' => ['category' => 'Cours 1 — Module 3 — Médias et équipements',  'count' => 15],
    'C1-QUIZ-MOD04' => ['category' => 'Cours 1 — Module 4 — Protocoles applicatifs', 'count' => 15],
    'C1-QUIZ-MOD05' => ['category' => 'Cours 1 — Module 5 — Outils de diagnostic',   'count' => 15],
    'C1-QUIZ-MOD06' => ['category' => 'Cours 1 — Module 6 — Documentation NOC',      'count' => 15],
];

foreach ($quiz_question_map as $quiz_idnumber => $qmap) {
    $cmid   = $quiz_cmids[$quiz_idnumber] ?? null;
    $cat_id = $cat_ids[$qmap['category']] ?? null;

    if (!$cmid) {
        parcours_log("  Quiz {$quiz_idnumber} — cmid introuvable, skip.", 'warning');
        continue;
    }
    if (!$cat_id) {
        parcours_log("  Quiz {$quiz_idnumber} — catégorie de questions non importée, skip.", 'warning');
        continue;
    }

    parcours_log("  Ajout questions → {$quiz_idnumber} (catégorie: {$qmap['category']}, max: {$qmap['count']})");
    add_questions_to_quiz($courseid, $cmid, $cat_id, $qmap['count'], $dry_run);
}

// Quiz sommatif — questions de toutes les catégories de modules (pas le diagnostic)
$final_cmid = $quiz_cmids['C1-QUIZ-FINAL'] ?? null;
if ($final_cmid) {
    $module_cat_ids = array_filter($cat_ids, function($key) {
        return str_contains($key, 'Module');
    }, ARRAY_FILTER_USE_KEY);

    if (!empty($module_cat_ids)) {
        parcours_log("  Ajout 30 questions aléatoires → Quiz sommatif final");
        add_random_questions_to_quiz($courseid, $final_cmid, array_values($module_cat_ids), 30, $dry_run);
    }
} else {
    parcours_log("  Quiz sommatif (C1-QUIZ-FINAL) — cmid introuvable, skip.", 'warning');
}

// ============================================================
// 5. RÉSUMÉ ET MISE À JOUR DE L'ÉTAT
// ============================================================
if (!$dry_run) {
    $state['courses'][$course_idnumber]['content_built'] = true;
    $state['courses'][$course_idnumber]['content_built_at'] = date('c');
    $state['courses'][$course_idnumber]['quiz_cmids'] = $quiz_cmids;
    file_put_contents($ref_file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    parcours_log("\nÉtat mis à jour dans .deploy_state.json", 'info');
    // Vider les caches Moodle
    purge_all_caches();
    parcours_log("Caches Moodle purgés.", 'info');
}

parcours_log("\n--- Résumé ---");
parcours_log("Cours 1 : " . count($activities_def) . " activités traitées");
parcours_log("Fichiers GIFT : " . count($gift_files) . " catégories");

if ($errors > 0) {
    parcours_log("=== Étape 02 terminée avec {$errors} erreur(s) ===", 'error');
    exit(1);
} else {
    parcours_log("=== Étape 02 terminée avec succès ===", 'success');
}
