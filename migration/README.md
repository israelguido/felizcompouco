# Migração K2 (Joomla 3.9) → Artigos (Joomla 6)

## Ferramenta
[plg_system_wp2joomla](https://github.com/alejoasotelo/plg_system_wp2joomla) (adapter `k2`), com ajustes para Joomla 6:
- `JPATH_PLATFORM` → `_JEXEC`
- `JText` → `Text`
- `JFilterOutput` → `OutputFilter`
- árvore de categorias sem bug de `foreach` por referência
- imagens em `images/k2/{md5}.jpg`

## Fonte
- SQL: `felizcompouco/backup-hacking/joomla-sem-redirects.sql`
- Imagens: `felizcompouco/media/k2/items/src`

## Resultado
- Categorias K2 → `com_content` categories
- Itens K2 (não lixeira) → artigos
- Tabelas K2 mantidas no DB com prefixo `a6u17_` (podem ser removidas depois)
- Mapas em `#__wp2joomla_categories` / `#__wp2joomla_articles`

## Comandos (reexecução)
```bash
warden env exec php-fpm php cli/joomla.php migrate:categories --adapter=k2 --userId=450
warden env exec php-fpm php cli/joomla.php migrate:articles --adapter=k2 --userId=450
# rollback artigos migrados:
warden env exec php-fpm php cli/joomla.php delete:migrate:articles --adapter=k2 --userId=450
```

## Home VicMagz (J6)
```bash
warden env exec php-fpm php /var/www/html/migration/setup_home.php
```
Cria `mod_fcp_articles` (layouts slideshow/popular/latest/mostviewed/trending/recent), módulos nas posições do VicMagz, tema **serenity**, logo e `hideComponentHomePage`.

## Páginas estáticas (Sobre, Contato, Anuncie, Mídia Kit)
O conteúdo já veio na migração K2 (artigos 36–39). O que faltava era o menu SEF:
```bash
warden env exec php-fpm php /var/www/html/migration/setup_pages.php
```
Cria `menu-hidden` com `/sobre`, `/contato`, `/anuncie`, `/midia-kit` e o módulo footer **Páginas**.

## Instagram (grade de fotos)
```bash
warden env exec php-fpm php /var/www/html/migration/setup_instagram.php
```
Substitui o card-link da sidebar por `mod_fcp_instagram` (grade de fotos). Posts mais recentes via busca automática.

## Newsletter (home)
```bash
warden env exec php-fpm php /var/www/html/migration/setup_newsletter.php
warden env exec php-fpm php /var/www/html/migration/setup_newsletter_admin.php
```
- Front: `mod_fcp_newsletter` (posição `position2`)
- Admin: **Componentes → Newsletter** (`com_fcp_newsletter`) — lista, busca, excluir e exportar CSV
- Tabela: `#__fcp_newsletter`

## Não migrado automaticamente
- Tags reais do K2 (`#__k2_tags`) — o plugin trata categorias como tags
- Extra fields K2 → custom fields
- Comentários K2
- Redirects SEF do K2
- Itens na lixeira (`trash=1`)
- AcyMailing completo (campanhas) — inscrição funciona com `mod_fcp_newsletter`
- Módulos SJ K2 originais (`mod_sj_k2_*`) — substituídos por `mod_fcp_articles`
- Stories (`com_stories`) — extensão antiga não portada
