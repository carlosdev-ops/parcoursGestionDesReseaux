<?php
/**
 * lib.php — Utilitaires partagés pour le déploiement du parcours Moodle
 * Parcours Gestion des Réseaux — Carlos Costa
 */

defined('CLI_SCRIPT') || die('CLI only');

/**
 * Affiche un message coloré dans le terminal.
 */
function parcours_log(string $message, string $level = 'info'): void {
    $colors = [
        'info'    => "\033[0;36m",  // Cyan
        'success' => "\033[0;32m",  // Vert
        'warning' => "\033[0;33m",  // Jaune
        'error'   => "\033[0;31m",  // Rouge
    ];
    $reset = "\033[0m";
    $color = $colors[$level] ?? $colors['info'];
    $prefix = strtoupper($level);
    echo "{$color}[{$prefix}]{$reset} {$message}\n";
}

/**
 * Retourne la définition des 8 cours du parcours.
 */
function parcours_get_courses_definition(): array {
    return [
        [
            'idnumber'    => 'RESEAUX-01-OSI',
            'shortname'   => 'Réseaux-01 — OSI/TCP-IP',
            'fullname'    => 'Cours 1 — Fondements des réseaux & modèles OSI/TCP-IP',
            'summary'     => '<p>Point de départ du parcours. Le technicien terrain connaît le câblage physique — ce cours construit le cadre conceptuel qui donne du sens à ce qu\'il branche tous les jours.</p><p><strong>Équivalent DEC :</strong> 420-1A3 — Semestre 1<br><strong>Durée :</strong> 8 semaines (~40 h)<br><strong>Niveau :</strong> Introductif<br><strong>Outils clés :</strong> Wireshark, ping, traceroute</p>',
            'numsections' => 8,
            'seq'         => 1,
            'weeks'       => 8,
        ],
        [
            'idnumber'    => 'RESEAUX-02-IP',
            'shortname'   => 'Réseaux-02 — Adressage IP',
            'fullname'    => 'Cours 2 — Adressage IP & routage statique',
            'summary'     => '<p>Le cœur du métier réseau. Maîtriser l\'adressage IPv4/IPv6 et le routage statique est indispensable avant d\'aborder les protocoles dynamiques.</p><p><strong>Équivalent DEC :</strong> 420-1B3 — Semestre 1<br><strong>Durée :</strong> 10 semaines (~50 h)<br><strong>Niveau :</strong> Base<br><strong>Outils clés :</strong> GNS3, ipcalc, Cisco IOS</p>',
            'numsections' => 8,
            'seq'         => 2,
            'weeks'       => 10,
        ],
        [
            'idnumber'    => 'RESEAUX-03-VLAN',
            'shortname'   => 'Réseaux-03 — Commutation & VLANs',
            'fullname'    => 'Cours 3 — Commutation & VLANs',
            'summary'     => '<p>La couche 2 est le terrain quotidien des techniciens NOC et installation. Ce cours couvre la commutation Ethernet en profondeur, avec une attention particulière aux VLANs et au STP.</p><p><strong>Équivalent DEC :</strong> 420-2A3 — Semestre 2<br><strong>Durée :</strong> 10 semaines (~50 h)<br><strong>Niveau :</strong> Base<br><strong>Outils clés :</strong> GNS3, Packet Tracer, SNMP</p>',
            'numsections' => 8,
            'seq'         => 3,
            'weeks'       => 10,
        ],
        [
            'idnumber'    => 'RESEAUX-04-OSPF-BGP',
            'shortname'   => 'Réseaux-04 — Routage dynamique',
            'fullname'    => 'Cours 4 — Routage dynamique (OSPF, BGP & MPLS)',
            'summary'     => '<p>Le cœur du routage en environnement opérateur. Ce cours couvre les protocoles que les techniciens NOC voient quotidiennement — OSPF pour le backbone, BGP pour les peerings, MPLS pour les VPNs clients.</p><p><strong>Équivalent DEC :</strong> 420-2B3 — Semestres 2-3<br><strong>Durée :</strong> 12 semaines (~60 h)<br><strong>Niveau :</strong> Intermédiaire<br><strong>Outils clés :</strong> GNS3, Quagga/FRR, tcpdump</p>',
            'numsections' => 8,
            'seq'         => 4,
            'weeks'       => 12,
        ],
        [
            'idnumber'    => 'RESEAUX-05-HFC',
            'shortname'   => 'Réseaux-05 — Fibre & HFC/DOCSIS',
            'fullname'    => 'Cours 5 — Infrastructure télécom — Fibre optique & HFC/DOCSIS',
            'summary'     => '<p>Cours unique à ce parcours — directement ancré dans la réalité des réseaux câblés HFC. Couvre l\'infrastructure physique et protocolaire, la fibre optique et la transition vers le tout-optique (Node+0, Remote PHY).</p><p><strong>Équivalent DEC :</strong> 420-3A3 — Semestre 3<br><strong>Durée :</strong> 10 semaines (~50 h)<br><strong>Niveau :</strong> Intermédiaire<br><strong>Outils clés :</strong> Spectrum analyzers, CableLabs docs</p>',
            'numsections' => 8,
            'seq'         => 5,
            'weeks'       => 10,
        ],
        [
            'idnumber'    => 'RESEAUX-06-SEC',
            'shortname'   => 'Réseaux-06 — Sécurité réseau',
            'fullname'    => 'Cours 6 — Sécurité réseau & pare-feu',
            'summary'     => '<p>La sécurité n\'est plus optionnelle en environnement opérateur. Ce cours couvre les fondements de la sécurité réseau, la configuration de pare-feux, la détection d\'intrusion et les bonnes pratiques de hardening.</p><p><strong>Équivalent DEC :</strong> 420-3B3 — Semestres 3-4<br><strong>Durée :</strong> 12 semaines (~60 h)<br><strong>Niveau :</strong> Avancé<br><strong>Outils clés :</strong> pfSense, Nmap, Wireshark, Snort</p>',
            'numsections' => 8,
            'seq'         => 6,
            'weeks'       => 12,
        ],
        [
            'idnumber'    => 'RESEAUX-07-LINUX',
            'shortname'   => 'Réseaux-07 — Admin Linux',
            'fullname'    => 'Cours 7 — Administration Linux & services réseau',
            'summary'     => '<p>Linux est l\'OS sous-jacent de la quasi-totalité des équipements réseau et outils NOC. Ce cours est calibré sur RHEL 8 et couvre l\'administration des services réseau critiques.</p><p><strong>Équivalent DEC :</strong> 420-4A3 — Semestre 4<br><strong>Durée :</strong> 10 semaines (~50 h)<br><strong>Niveau :</strong> Avancé<br><strong>Outils clés :</strong> RHEL 8, Bash, systemd, Ansible</p>',
            'numsections' => 8,
            'seq'         => 7,
            'weeks'       => 10,
        ],
        [
            'idnumber'    => 'RESEAUX-08-NOC',
            'shortname'   => 'Réseaux-08 — Monitoring & NOC',
            'fullname'    => 'Cours 8 — Monitoring, automatisation & opérations NOC',
            'summary'     => '<p>Cours de synthèse et d\'intégration. Il rassemble les compétences des 7 cours précédents dans un contexte d\'opérations NOC réalistes — surveillance, automatisation, réponse aux incidents et documentation.</p><p><strong>Équivalent DEC :</strong> 420-4B3 — Semestre 4 (intégration)<br><strong>Durée :</strong> 10 semaines (~50 h)<br><strong>Niveau :</strong> Avancé<br><strong>Outils clés :</strong> Zabbix, Python, Ansible, Grafana</p>',
            'numsections' => 8,
            'seq'         => 8,
            'weeks'       => 10,
        ],
    ];
}

/**
 * Retourne la définition de la catégorie principale du parcours.
 */
function parcours_get_category_definition(): array {
    return [
        'name'        => 'Parcours Gestion des Réseaux',
        'idnumber'    => 'PARCOURS-RESEAUX',
        'description' => '<p>Parcours de formation complet en gestion des réseaux, équivalent DEC en Techniques de l\'informatique, orientation Gestion des réseaux.</p><p>82 semaines (~20 mois à temps partiel, ~5 h/semaine) — 8 cours progressifs du niveau introductif au niveau avancé.</p><p><strong>Public cible :</strong> Techniciens terrain avec base câblage/IP souhaitant progresser vers le niveau DEC Gestion des réseaux.</p><p><strong>Plateforme :</strong> Moodle 4.1 LTS / 4.5 LTS<br><strong>Simulateurs :</strong> GNS3, Packet Tracer, pfSense, RHEL 8 VM, Zabbix</p>',
        'parent'      => 0,
        'visible'     => 1,
    ];
}
