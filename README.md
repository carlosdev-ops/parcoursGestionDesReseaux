# Parcours Gestion des Réseaux — Moodle

Formation complète en gestion des réseaux, équivalent DEC 420.xx.
**82 semaines** · 8 cours · Moodle 4.1 LTS / 4.5 LTS

---

## Structure du dépôt

```
parcoursGestionDesReseaux/
├── deploy.sh                          # Orchestrateur de déploiement
├── scripts/
│   ├── lib.php                        # Définitions des cours et utilitaires
│   ├── 00_setup_category.php          # Étape 00 : création de la catégorie Moodle
│   └── 01_create_courses.php          # Étape 01 : création des 8 cours (shells)
├── courses/
│   ├── cours-01-fondements/           # Contenu XML + ressources — Cours 1
│   ├── cours-02-adressage/
│   ├── cours-03-commutation-vlans/
│   ├── cours-04-routage-dynamique/
│   ├── cours-05-infrastructure-telecom/
│   ├── cours-06-securite-reseau/
│   ├── cours-07-administration-linux/
│   └── cours-08-monitoring-noc/
└── Blueprint_Parcours_Gestion_Reseaux_Moodle.docx
```

---

## Prérequis

- PHP 7.4+ accessible en CLI (`php --version`)
- Moodle 4.1 LTS ou 4.5 LTS installé et fonctionnel
- Accès au système de fichiers du serveur Moodle
- Permissions de lecture/écriture sur la base de données Moodle

---

## Déploiement

### 1. Cloner le dépôt sur le serveur Moodle

```bash
git clone git@github.com:carlosdev-ops/parcoursGestionDesReseaux.git
cd parcoursGestionDesReseaux
chmod +x deploy.sh
```

### 2. Déployer le parcours complet

```bash
./deploy.sh --moodle=/var/www/moodle
```

### 3. Options disponibles

```bash
# Simuler sans modifier la base de données
./deploy.sh --moodle=/var/www/moodle --dry-run

# Recréer les éléments existants (catégorie + cours)
./deploy.sh --moodle=/var/www/moodle --force

# Exécuter une seule étape
./deploy.sh --moodle=/var/www/moodle --step=00   # catégorie seulement
./deploy.sh --moodle=/var/www/moodle --step=01   # cours seulement

# Spécifier un binaire PHP différent
./deploy.sh --moodle=/var/www/moodle --php=/usr/bin/php8.1
```

---

## Étapes du déploiement

| Étape | Script | Action |
|-------|--------|--------|
| `00` | `00_setup_category.php` | Crée la catégorie **Parcours Gestion des Réseaux** |
| `01` | `01_create_courses.php` | Crée les 8 cours avec métadonnées et structure |

L'état du déploiement est sauvegardé dans `.deploy_state.json` (ignoré par git).

---

## Les 8 cours

| # | Cours | DEC | Durée | Niveau |
|---|-------|-----|-------|--------|
| 1 | Fondements des réseaux & modèles OSI/TCP-IP | 420-1A3 | 8 sem. | Intro |
| 2 | Adressage IP & routage statique | 420-1B3 | 10 sem. | Base |
| 3 | Commutation & VLANs | 420-2A3 | 10 sem. | Base |
| 4 | Routage dynamique (OSPF, BGP & MPLS) | 420-2B3 | 12 sem. | Inter. |
| 5 | Infrastructure télécom — Fibre & HFC/DOCSIS | 420-3A3 | 10 sem. | Inter. |
| 6 | Sécurité réseau & pare-feu | 420-3B3 | 12 sem. | Avancé |
| 7 | Administration Linux & services réseau | 420-4A3 | 10 sem. | Avancé |
| 8 | Monitoring, automatisation & NOC | 420-4B3 | 10 sem. | Avancé |

---

## Ajout de contenu (prochaines étapes)

Le dossier `courses/cours-XX-nom/` de chaque cours contiendra :
- `moodle_backup.mbz` — Backup Moodle importable (sections, activités, ressources)
- `content/` — Sources Markdown/HTML des leçons
- `quizzes/` — Banques de questions XML (format Moodle Gift/XML)
- `labs/` — Topologies GNS3 et guides de laboratoire PDF

---

## Auteur

Carlos Costa — Gestionnaire principal, centre de formation
Version 1.0 — Mars 2026
