#!/usr/bin/env bash
# =============================================================================
# deploy.sh — Déploiement du Parcours Gestion des Réseaux sur Moodle
# =============================================================================
# Usage :
#   ./deploy.sh --moodle=/var/www/moodle
#   ./deploy.sh --moodle=/var/www/moodle --step=00
#   ./deploy.sh --moodle=/var/www/moodle --force
#   ./deploy.sh --moodle=/var/www/moodle --dry-run
#
# Options :
#   --moodle    Chemin absolu vers la racine Moodle (obligatoire)
#   --step      Exécuter uniquement une étape : 00 | 01 (défaut : toutes)
#   --force     Recréer les éléments existants
#   --dry-run   Simuler sans modifier la base de données
#   --php       Chemin vers l'exécutable PHP (défaut : php)
# =============================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# --- Valeurs par défaut ---
MOODLE_PATH=""
STEP=""
FORCE=""
DRY_RUN=""
PHP_BIN="php"

# --- Couleurs ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

log_info()    { echo -e "${CYAN}[INFO]${NC} $*"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $*"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $*"; }
log_error()   { echo -e "${RED}[ERROR]${NC} $*"; }
log_header()  { echo -e "\n${BOLD}$*${NC}"; }

# --- Parsing des arguments ---
for arg in "$@"; do
    case "$arg" in
        --moodle=*)  MOODLE_PATH="${arg#*=}" ;;
        --step=*)    STEP="${arg#*=}" ;;
        --php=*)     PHP_BIN="${arg#*=}" ;;
        --force)     FORCE="--force" ;;
        --dry-run)   DRY_RUN="--dry-run" ;;
        --help|-h)
            sed -n '2,25p' "$0"
            exit 0
            ;;
        *)
            log_error "Argument inconnu : $arg"
            exit 1
            ;;
    esac
done

# --- Validation ---
if [[ -z "$MOODLE_PATH" ]]; then
    log_error "--moodle est obligatoire."
    echo "Usage : ./deploy.sh --moodle=/var/www/moodle"
    exit 1
fi

if [[ ! -f "${MOODLE_PATH}/config.php" ]]; then
    log_error "config.php introuvable dans ${MOODLE_PATH}"
    exit 1
fi

if ! command -v "$PHP_BIN" &>/dev/null; then
    log_error "PHP introuvable : $PHP_BIN"
    exit 1
fi

PHP_VERSION=$("$PHP_BIN" -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
log_info "PHP détecté : $PHP_VERSION"

# --- Bannière ---
log_header "============================================================"
log_header "  Déploiement — Parcours Gestion des Réseaux"
log_header "  Moodle : ${MOODLE_PATH}"
[[ -n "$DRY_RUN" ]] && log_warning "  MODE DRY-RUN activé"
[[ -n "$FORCE" ]]   && log_warning "  MODE FORCE activé"
log_header "============================================================"

# --- Fonction d'exécution d'une étape ---
run_step() {
    local step_num="$1"
    local step_script="${SCRIPT_DIR}/scripts/${step_num}_*.php"
    local script_file

    script_file=$(ls ${step_script} 2>/dev/null | head -1)
    if [[ -z "$script_file" ]]; then
        log_error "Script introuvable pour l'étape ${step_num}"
        exit 1
    fi

    log_header "--- Étape ${step_num} : $(basename "$script_file") ---"

    "$PHP_BIN" "$script_file" \
        --moodle="${MOODLE_PATH}" \
        ${FORCE} \
        ${DRY_RUN}

    log_success "Étape ${step_num} terminée."
}

# --- Exécution ---
if [[ -n "$STEP" ]]; then
    run_step "$STEP"
else
    run_step "00"
    run_step "01"
    run_step "02"
fi

log_header "============================================================"
log_success "Déploiement terminé."

# Afficher le résumé si .deploy_state.json existe
STATE_FILE="${SCRIPT_DIR}/.deploy_state.json"
if [[ -f "$STATE_FILE" ]]; then
    log_header "Résumé du déploiement :"
    if command -v python3 &>/dev/null; then
        python3 -c "
import json, sys
with open('${STATE_FILE}') as f:
    s = json.load(f)
print(f\"  Categorie : {s.get('category_name','')} (id={s.get('category_id','')})\")
courses = s.get('courses', {})
print(f\"  Cours deployes : {len(courses)}/8\")
for k,v in sorted(courses.items(), key=lambda x: x[1].get('seq',0)):
    print(f\"    [{v.get('seq','?')}] {v.get('fullname', k)} — id={v.get('id','?')} ({v.get('status','')})\")
"
    else
        cat "$STATE_FILE"
    fi
fi

log_header "============================================================"
