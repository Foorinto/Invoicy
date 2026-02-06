#!/bin/bash

#############################################
# Script de déploiement faktur.lu
# Usage: ./deploy.sh [quick]
#   - Sans argument : déploiement complet
#   - quick : git pull + cache clear seulement
#############################################

set -e  # Arrêter en cas d'erreur

# Configuration
SSH_USER="sc1beal9117"
SSH_HOST="saut.o2switch.net"
SSH_PORT="22"
REMOTE_PATH="/home2/sc1beal9117/faktur.lu"
BRANCH="main"
SITE_URL="https://faktur.lu"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Déploiement faktur.lu${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Vérifier qu'on est sur la bonne branche localement
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
    echo -e "${YELLOW}Attention: Vous êtes sur la branche '$CURRENT_BRANCH', pas '$BRANCH'${NC}"
    read -p "Continuer quand même? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Vérifier les modifications non commitées
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}Erreur: Vous avez des modifications non commitées${NC}"
    git status --short
    exit 1
fi

# Pousser les modifications locales
echo -e "${YELLOW}[1/6] Push des modifications vers GitHub...${NC}"
git push origin $BRANCH

# Connexion SSH et déploiement
echo -e "${YELLOW}[2/6] Connexion au serveur...${NC}"

if [ "$1" == "quick" ]; then
    # Déploiement rapide (git pull + cache)
    ssh -p $SSH_PORT $SSH_USER@$SSH_HOST << 'ENDSSH'
        cd /home2/sc1beal9117/faktur.lu

        echo "=== ÉTAPE 1: Activation du mode maintenance ==="
        php artisan down --retry=30 2>/dev/null || true

        echo "=== ÉTAPE 2: Nettoyage COMPLET du cache ==="
        # Supprimer tous les fichiers cache compilés
        rm -f bootstrap/cache/*.php 2>/dev/null || true
        rm -f storage/framework/cache/data/* 2>/dev/null || true
        rm -f storage/framework/views/*.php 2>/dev/null || true

        # Clear via artisan (ignore errors si app est cassée)
        php artisan cache:clear 2>/dev/null || true
        php artisan config:clear 2>/dev/null || true
        php artisan route:clear 2>/dev/null || true
        php artisan view:clear 2>/dev/null || true
        php artisan event:clear 2>/dev/null || true

        echo "=== ÉTAPE 3: Pull des modifications ==="
        git pull origin main

        echo "=== ÉTAPE 4: Clear OPcache ==="
        # Créer un script temporaire pour clear OPcache via HTTP
        cat > public/opcache-clear.php << 'OPCACHE'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache cleared';
} else {
    echo 'OPcache not available';
}
unlink(__FILE__);
OPCACHE
        # Appeler le script pour clear OPcache
        curl -s "https://faktur.lu/opcache-clear.php" || true

        echo "=== ÉTAPE 5: Désactivation du mode maintenance ==="
        php artisan up

        echo "=== Déploiement rapide terminé! ==="
ENDSSH
else
    # Déploiement complet
    ssh -p $SSH_PORT $SSH_USER@$SSH_HOST << 'ENDSSH'
        cd /home2/sc1beal9117/faktur.lu

        echo "=== ÉTAPE 1: Activation du mode maintenance ==="
        php artisan down --retry=30 2>/dev/null || true

        echo "=== ÉTAPE 2: Nettoyage COMPLET du cache avant déploiement ==="
        # Supprimer tous les fichiers cache compilés PHYSIQUEMENT
        rm -f bootstrap/cache/*.php 2>/dev/null || true
        rm -f bootstrap/cache/packages.php 2>/dev/null || true
        rm -f bootstrap/cache/services.php 2>/dev/null || true
        rm -f bootstrap/cache/config.php 2>/dev/null || true
        rm -f bootstrap/cache/routes-v7.php 2>/dev/null || true
        rm -rf storage/framework/cache/data/* 2>/dev/null || true
        rm -f storage/framework/views/*.php 2>/dev/null || true

        # Clear via artisan (ignore errors si app est cassée)
        php artisan cache:clear 2>/dev/null || true
        php artisan config:clear 2>/dev/null || true
        php artisan route:clear 2>/dev/null || true
        php artisan view:clear 2>/dev/null || true
        php artisan event:clear 2>/dev/null || true

        echo "=== ÉTAPE 3: Pull des modifications ==="
        git pull origin main

        echo "=== ÉTAPE 4: Installation des dépendances ==="
        composer install --no-dev --optimize-autoloader --no-interaction

        echo "=== ÉTAPE 5: Migrations de base de données ==="
        php artisan migrate --force

        echo "=== ÉTAPE 6: Reconstruction du cache ==="
        # IMPORTANT: Ne pas utiliser config:cache sur hébergement mutualisé
        # car cela peut causer des problèmes avec les chemins absolus
        php artisan route:cache
        php artisan view:cache
        # On évite config:cache - utiliser config:clear à la place
        php artisan config:clear

        echo "=== ÉTAPE 7: Clear OPcache PHP ==="
        # Créer un script temporaire pour clear OPcache via HTTP
        cat > public/opcache-clear.php << 'OPCACHE'
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OPcache cleared';
} else {
    echo 'OPcache not available';
}
unlink(__FILE__);
OPCACHE
        # Appeler le script pour clear OPcache (s'auto-supprime après)
        curl -s "https://faktur.lu/opcache-clear.php" || true

        echo "=== ÉTAPE 8: Désactivation du mode maintenance ==="
        php artisan up

        echo "=== ÉTAPE 9: Vérification du site ==="
        HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://faktur.lu" || echo "000")
        if [ "$HTTP_STATUS" == "200" ] || [ "$HTTP_STATUS" == "302" ]; then
            echo "✓ Site accessible (HTTP $HTTP_STATUS)"
        else
            echo "⚠ Attention: Site retourne HTTP $HTTP_STATUS"
        fi

        echo ""
        echo "=== Déploiement complet terminé! ==="
ENDSSH
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Déploiement terminé!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Vérification locale du site
echo -e "${YELLOW}Vérification du site...${NC}"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL" 2>/dev/null || echo "000")
if [ "$HTTP_STATUS" == "200" ] || [ "$HTTP_STATUS" == "302" ]; then
    echo -e "${GREEN}✓ Site accessible (HTTP $HTTP_STATUS)${NC}"
else
    echo -e "${RED}⚠ Attention: Le site retourne HTTP $HTTP_STATUS${NC}"
    echo -e "${YELLOW}Tentative de fix automatique...${NC}"
    ssh -p $SSH_PORT $SSH_USER@$SSH_HOST << 'ENDSSH'
        cd /home2/sc1beal9117/faktur.lu
        rm -f bootstrap/cache/*.php 2>/dev/null || true
        php artisan config:clear 2>/dev/null || true
        php artisan cache:clear 2>/dev/null || true
        php artisan up 2>/dev/null || true
ENDSSH
    echo -e "${YELLOW}Fix appliqué, vérifiez le site manuellement${NC}"
fi

echo ""
echo "Site: $SITE_URL"
