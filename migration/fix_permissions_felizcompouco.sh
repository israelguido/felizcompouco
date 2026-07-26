#!/usr/bin/env bash
#
# Ajusta dono e permissões do Joomla para o usuário felizcompouco.
#
# Uso (no servidor, na raiz do site ou passando o caminho):
#   bash migration/fix_permissions_felizcompouco.sh
#   bash migration/fix_permissions_felizcompouco.sh /home/felizcompouco/htdocs/www.felizcompouco.com.br
#
set -euo pipefail

SITE_USER="${SITE_USER:-felizcompouco}"
SITE_GROUP="${SITE_GROUP:-felizcompouco}"

# Se o PHP-FPM rodar como outro usuário (ex.: www-data), defina:
#   export WEB_GROUP=www-data
WEB_GROUP="${WEB_GROUP:-$SITE_GROUP}"

ROOT="${1:-}"
if [[ -z "$ROOT" ]]; then
	# Detecta a raiz do site a partir do script
	SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
	ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
fi

ROOT="$(cd "$ROOT" && pwd)"

if [[ ! -f "$ROOT/configuration.php" && ! -f "$ROOT/index.php" ]]; then
	echo "ERRO: não parece ser a raiz do Joomla: $ROOT" >&2
	exit 1
fi

if [[ "$(id -u)" -ne 0 && "$(id -un)" != "$SITE_USER" ]]; then
	echo "Aviso: rode como root ou como $SITE_USER para aplicar chown/chmod." >&2
fi

echo "== Fix permissions =="
echo "Root:  $ROOT"
echo "Owner: $SITE_USER:$SITE_GROUP"
echo "Group (web): $WEB_GROUP"
echo

# Dono geral
if command -v sudo >/dev/null 2>&1 && [[ "$(id -u)" -ne 0 ]]; then
	SUDO=(sudo)
else
	SUDO=()
fi

"${SUDO[@]}" chown -R "$SITE_USER:$SITE_GROUP" "$ROOT"

# Pastas graváveis pelo PHP (cache, tmp, logs, uploads)
WRITABLE_DIRS=(
	cache
	tmp
	administrator/cache
	administrator/logs
	images
	media
	files
)

for d in "${WRITABLE_DIRS[@]}"; do
	if [[ -d "$ROOT/$d" ]]; then
		echo "writable: $d"
		"${SUDO[@]}" chown -R "$SITE_USER:$WEB_GROUP" "$ROOT/$d"
		"${SUDO[@]}" find "$ROOT/$d" -type d -exec chmod 775 {} \;
		"${SUDO[@]}" find "$ROOT/$d" -type f -exec chmod 664 {} \;
		# setgid: arquivos novos herdam o grupo
		"${SUDO[@]}" find "$ROOT/$d" -type d -exec chmod g+s {} \;
	fi
done

# Código: pastas 755, arquivos 644 (exceto graváveis acima)
echo "locking code dirs..."
"${SUDO[@]}" find "$ROOT" \
	\( -path "$ROOT/cache" -o -path "$ROOT/tmp" -o -path "$ROOT/administrator/cache" \
	   -o -path "$ROOT/administrator/logs" -o -path "$ROOT/images" -o -path "$ROOT/media" \
	   -o -path "$ROOT/files" \) -prune -o \
	-type d -exec chmod 755 {} \;

"${SUDO[@]}" find "$ROOT" \
	\( -path "$ROOT/cache" -o -path "$ROOT/tmp" -o -path "$ROOT/administrator/cache" \
	   -o -path "$ROOT/administrator/logs" -o -path "$ROOT/images" -o -path "$ROOT/media" \
	   -o -path "$ROOT/files" \) -prune -o \
	-type f -exec chmod 644 {} \;

# configuration.php mais restrito
if [[ -f "$ROOT/configuration.php" ]]; then
	"${SUDO[@]}" chown "$SITE_USER:$SITE_GROUP" "$ROOT/configuration.php"
	"${SUDO[@]}" chmod 640 "$ROOT/configuration.php"
	echo "configuration.php -> 640"
fi

# CLI / migration executáveis
for f in "$ROOT/cli/joomla.php" \
	"$ROOT/migration/fix_permissions_felizcompouco.sh" \
	"$ROOT/migration/setup_newsletter_admin.php" \
	"$ROOT/migration/setup_newsletter.php" \
	"$ROOT/migration/remove_instagram_home.php"
do
	if [[ -f "$f" ]]; then
		"${SUDO[@]}" chmod 755 "$f" 2>/dev/null || true
	fi
done

# Garante index.html nas pastas de cache (Joomla usa; não precisa ser deletável com erro)
for d in cache administrator/cache tmp; do
	if [[ -d "$ROOT/$d" && ! -f "$ROOT/$d/index.html" ]]; then
		echo "" | "${SUDO[@]}" tee "$ROOT/$d/index.html" >/dev/null
		"${SUDO[@]}" chown "$SITE_USER:$WEB_GROUP" "$ROOT/$d/index.html"
		"${SUDO[@]}" chmod 664 "$ROOT/$d/index.html"
	fi
done

echo
echo "Done."
echo
echo "Se o limpar-cache do admin ainda falhar, o PHP-FPM provavelmente"
echo "roda como outro usuário. Descubra com:"
echo "  ps aux | grep php-fpm | head"
echo "e rode de novo com:"
echo "  WEB_GROUP=www-data bash migration/fix_permissions_felizcompouco.sh"
echo
