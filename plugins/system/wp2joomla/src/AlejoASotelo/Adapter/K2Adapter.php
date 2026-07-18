<?php

namespace AlejoASotelo\Adapter;

use Joomla\Registry\Registry;
use AlejoASotelo\Adapter\Wp2JoomlaAdapterInterface;
use AlejoASotelo\Table\ArticleFinalTable;
use AlejoASotelo\Table\MigratorCategoryTable;
use AlejoASotelo\Table\MigratorTagTable;
use AlejoASotelo\Table\CategoryFinalTable;
use AlejoASotelo\Table\TagFinalTable;
use Joomla\String\StringHelper;
use Joomla\CMS\Filter\OutputFilter;

class K2Adapter implements Wp2JoomlaAdapterInterface
{

    const CATEGORY_UNCATEGORIZED = 2;

    protected $cache = [];

    protected $db;

    protected $user;

    protected $wpContentPath;

    public function __construct($db, $user, $wpContentPath)
    {
        $this->db = $db;
        $this->user = $user;
        $this->wpContentPath = rtrim($wpContentPath, '/');
    }

    public function getName() {
        return 'k2';
    }

    /**
     * Undocumented function
     *
     * @return array<ArticleFinalTable>
     */
    public function listArticles()
    {
        $k2Articles = $this->getK2Articles();

        $category = new MigratorCategoryTable($this->db);
        $tag = new MigratorTagTable($this->db);

        $articles = [];
        foreach ($k2Articles as $k2Article) {

            if ($k2Article->catid > 0) {
                $category->load(['id_adapter' => $k2Article->catid, 'adapter' => $this->getName()]);
                $categoryId = !$category->id ? self::CATEGORY_UNCATEGORIZED : $category->id_joomla;
                
                $tag->load(['id_adapter' => $k2Article->catid, 'adapter' => $this->getName()]);
                $tags = $tag->id ? [$tag->id_joomla] : [];
            } else {
                $categoryId = self::CATEGORY_UNCATEGORIZED;
                $tags = [];
            }

            $article = new ArticleFinalTable($this->db);
            $article->id_adapter = $k2Article->id;
            $article->catid = $categoryId;
            $article->title = $k2Article->title;
            $baseAlias = !empty($k2Article->alias) ? $k2Article->alias : OutputFilter::stringURLSafe($k2Article->title);
            if ($baseAlias === '') { $baseAlias = 'item-' . $k2Article->id; }
            $article->alias = $this->generateNewAlias($articles, $baseAlias, $article->id_adapter);
            $article->published = $k2Article->published;
            $article->state = $k2Article->published;
            $article->language = '*';
            $article->introtext = $k2Article->introtext;
            $article->fulltext = $k2Article->fulltext;
            $article->created = $this->normalizeDate($k2Article->created);
            $article->created_by = $this->user->id;
            $article->created_by_alias = $k2Article->created_by_alias ?: $this->user->name;
            $article->modified = $this->normalizeDate($k2Article->modified) ?: $article->created;
            $article->modified_by = $this->user->id;
            $article->publish_up = $this->normalizeDate($k2Article->publish_up) ?: $article->created;
            $article->publish_down = $this->normalizeDate($k2Article->publish_down);
            $article->access = $k2Article->access;
            $article->featured = $k2Article->featured;
            $article->hits = 0;
            $article->metakey = $k2Article->metakey ?? '';
            $article->metadesc = $k2Article->metadesc ?? '';
            $article->hits = $k2Article->hits;
			$article->images = '{}';
			$article->urls = '{}';
			$article->attribs = '{}';
            $article->tags = json_encode($tags);
            $article->newTags = json_encode($tags);
			$article->metadata = '{}';

            $image = $this->getK2ImagePath($k2Article->id);

            if (!empty($image)) {
                $registry = new Registry;
                $registry->set('image_intro', $image);
                $registry->set('float_into', '');
                $registry->set('image_intro_alt', '');
                $registry->set('image_intro_caption', '');
                $registry->set('image_fulltext', '');
                $registry->set('float_fulltext', '');
                $registry->set('image_fulltext_alt', '');
                $registry->set('image_fulltext_caption', '');

                $article->images = (string)$registry;
            }

            $articles[] = $article;
        }

        return $articles;
    }

    public function getK2Articles()
    {
        $query = $this->db->getQuery(true);

        $query
            ->select('`id`, `title`, `alias`, `introtext`, `fulltext`, `created`, `catid`')
            ->select('`published`, `trash`')
            ->select('`created_by_alias`, `checked_out`, `checked_out_time`, `modified`, `publish_up`, `publish_down`, `access`, `featured`, `hits`, `language`, `metakey`, `metadesc`')
            ->where('trash = 0')
            ->from('#__k2_items')
            // ->setLimit(10)
            ->order('id ASC');

        return $this->db->setQuery($query)->loadObjectList();
    }

    public function getK2ImagePath($id) {
        $filename = md5("Image" . $id);
        return 'images/k2/' . $filename . '.jpg';
    }

    /**
     * Undocumented function
     *
     * @return array<CategoryFinalTable>
     */
    public function listCategories()
    {

        $k2Categories = $this->getCategories();


        $categories = [];

        foreach ($k2Categories as $k2Category) {
            try {

            $category = new CategoryFinalTable($this->db);

            } catch (\Throwable $e) {

              throw $e;
            }
            $category->id_adapter = $k2Category->id;
            $category->parent_id = 1;
            $category->parent_id_adapter = $k2Category->parent_id ?: 1;
            $category->title = $k2Category->name;
            $baseAlias = !empty($k2Category->alias) ? $k2Category->alias : OutputFilter::stringURLSafe($k2Category->name);
            if ($baseAlias === '') {
                $baseAlias = 'category-' . $k2Category->id;
            }
            $category->alias = $this->generateNewAlias($categories, $baseAlias, $category->id_adapter);
            $category->extension = 'com_content';
            $category->published = ((int) $k2Category->published === 1) ? 1 : 0;
            $category->language = '*';
            $category->params = ['category_layout' => '', 'image' => ''];
            $category->metadata = ['author' => '', 'robots' => ''];
            $category->rules = [
                'core.edit.state' => [],
                'core.edit.delete' => [],
                'core.edit.edit' => [],
                'core.edit.state' => [],
                'core.edit.own' => [1 => true]
            ];

            $categories[] = $category;
        }


        return $categories;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function listTags()
    {
        $k2Categories = $this->getCategories();

        $tags = [];

        foreach ($k2Categories as $k2Category) {
            $tag = new TagFinalTable($this->db);
            $tag->id_adapter = $k2Category->id;
            $tag->parent_id = 1;
            $tag->parent_id_adapter = $k2Category->parent_id ?: 1;
            $tag->title = $k2Category->name;
            $tag->alias = $this->generateNewAlias($tags, OutputFilter::stringURLSafe($k2Category->name), $tag->id_adapter);
            $tag->description = '';
            $tag->published = 1;
            $tag->level = $k2Category->level;
            $tag->language = '*';
            $tag->params = json_encode(['tag_layout' => '', 'tag_link_class' => '']);
            $tag->metadata = json_encode(['author' => '', 'robots' => '']);
            $tag->metadesc = '';
            $tag->metakey = '';
            $tag->images = '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}';
            $tag->urls = '{}';

            $tags[] = $tag;
        }

        return $tags;
    }

    protected function generateNewAlias($items, $alias, $idAdapter)
    {
        $i = 0;
        $len = count($items);
        while ($i < $len) {
            if ($items[$i]->alias == $alias && $items[$i]->id_adapter != $idAdapter) {
                $alias = StringHelper::increment($alias, 'dash');
                $i = -1;
            }
            
            $i++;
        }
        
        return $alias;
    }

    protected function getCategories()
    {
        $cacheId = 'getCategories';

        if (!isset($this->cache[$cacheId])) {
            $this->cache[$cacheId] = $this->getK2CategoriesTree();
        }

        return $this->cache[$cacheId];
    }

    /**
     * Devuelve el arbol de categorias K2 con los campos listos para categorías Joomla
     *
     * @param integer $offsetId Este valor se suma a los IDs de las categorías
     * @return Array<object> Categorías K2
     */
    public function getK2CategoriesTree($parentId = 0, $level = 1)
    {
        // Flat load + BFS to avoid recursion bugs and fragile SQL quoting
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'name', 'alias', 'published', 'access', 'parent', 'language']))
            ->from($this->db->quoteName('#__k2_categories'))
            ->order($this->db->quoteName('parent') . ' ASC, ' . $this->db->quoteName('ordering') . ' ASC, ' . $this->db->quoteName('id') . ' ASC');

        $all = $this->db->setQuery($query)->loadObjectList('id') ?: [];


        $childrenMap = [];
        foreach ($all as $cat) {
            $pid = (int) $cat->parent;
            $childrenMap[$pid][] = $cat;
        }

        $result = [];
        $walk = function ($pid, $lvl) use (&$walk, &$result, &$childrenMap) {
            if (empty($childrenMap[$pid])) {
                return;
            }
            foreach ($childrenMap[$pid] as $cat) {
                $cat->parent_id = (int) $cat->parent;
                $cat->level = $lvl;
                $cat->extension = 'com_content';
                $cat->path = '';
                $cat->asset_id = 0;
                $result[] = $cat;
                $walk((int) $cat->id, $lvl + 1);
            }
        };
        $walk(0, 1);


        return $result;
    }


    protected function normalizeDate($value)
    {
        if (empty($value) || $value === '0000-00-00 00:00:00' || $value === '0000-00-00') {
            return null;
        }
        return $value;
    }

    public function setDatabase($db)
    {
        $this->db = $db;
    }

    public function getDatabase()
    {
        return $this->db;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

}
