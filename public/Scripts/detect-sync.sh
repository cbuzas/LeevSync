#!/bin/bash

# Fonction de détection avancée
get_sync_info() {
    local tool_info=""

    echo "🔍 Détection des outils de synchronisation..."
    echo "============================================="

    # Vérifier OpenRsync
    if command -v openrsync &> /dev/null; then
        echo "✅ OpenRsync trouvé"
        local openrsync_path=$(which openrsync)
        local openrsync_version=""

        # Essayer différentes méthodes pour la version
        if brew list --versions openrsync &>/dev/null; then
            openrsync_version=$(brew list --versions openrsync | cut -d' ' -f2)
            echo "   📦 Version (Homebrew): $openrsync_version"
        else
            # Méthode alternative
            openrsync_version=$(openrsync --help 2>&1 | grep -i "version\|openrsync" | head -1 | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+" || echo "inconnue")
            echo "   📦 Version: $openrsync_version"
        fi

        echo "   📍 Chemin: $openrsync_path"

        # Vérifier les capacités
        echo "   🔧 Capacités:"
        if openrsync --help 2>&1 | grep -q "\-\-exclude"; then
            echo "      ✅ Exclusions supportées"
        fi
        if openrsync --help 2>&1 | grep -q "\-\-progress"; then
            echo "      ✅ Progress supporté"
        fi

        tool_info="openrsync|$openrsync_version|$openrsync_path"
    fi

    # Vérifier Rsync classique
    if command -v rsync &> /dev/null; then
        echo "✅ Rsync classique trouvé"
        local rsync_path=$(which rsync)
        local rsync_version=$(rsync --version | head -1 | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")
        local rsync_full=$(rsync --version | head -1)

        echo "   📦 Version: $rsync_version"
        echo "   📍 Chemin: $rsync_path"
        echo "   ℹ️  Info complète: $rsync_full"

        # Vérifier si c'est la version système ou Homebrew
        if [[ "$rsync_path" == "/usr/bin/rsync" ]]; then
            echo "   ⚠️  Version système (ancienne)"
            echo "   💡 Recommandation: brew install rsync"
        elif [[ "$rsync_path" == *"homebrew"* ]] || [[ "$rsync_path" == "/opt/homebrew"* ]]; then
            echo "   ✅ Version Homebrew (recommandée)"
        fi

        if [ -z "$tool_info" ]; then
            tool_info="rsync|$rsync_version|$rsync_path"
        fi
    fi

    # Recommandation finale
    echo ""
    if command -v openrsync &> /dev/null; then
        echo "🎯 Recommandation: Utiliser OpenRsync (optimisé pour macOS)"
        export RECOMMENDED_SYNC="openrsync"
    elif command -v rsync &> /dev/null; then
        echo "🎯 Recommandation: Rsync disponible"
        if [[ $(which rsync) == "/usr/bin/rsync" ]]; then
            echo "   💡 Conseil: brew install rsync pour une version plus récente"
        fi
        export RECOMMENDED_SYNC="rsync"
    else
        echo "❌ Aucun outil trouvé - Installation requise"
        echo "   brew install openrsync    # Recommandé"
        echo "   brew install rsync        # Alternative"
        return 1
    fi

    echo "============================================="
    return 0
}

# Fonction pour NativePHP (retourne JSON)
get_sync_info_json() {
    local json_output="{"

    # OpenRsync
    if command -v openrsync &> /dev/null; then
        local version=$(brew list --versions openrsync 2>/dev/null | cut -d' ' -f2 || echo "unknown")
        local path=$(which openrsync)
        json_output+="\"openrsync\":{\"available\":true,\"version\":\"$version\",\"path\":\"$path\"},"
    else
        json_output+="\"openrsync\":{\"available\":false},"
    fi

    # Rsync
    if command -v rsync &> /dev/null; then
        local version=$(rsync --version | head -1 | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")
        local path=$(which rsync)
        local is_system=$([[ "$path" == "/usr/bin/rsync" ]] && echo "true" || echo "false")
        json_output+="\"rsync\":{\"available\":true,\"version\":\"$version\",\"path\":\"$path\",\"is_system\":$is_system}"
    else
        json_output+="\"rsync\":{\"available\":false}"
    fi

    json_output+="}"
    echo "$json_output"
}

