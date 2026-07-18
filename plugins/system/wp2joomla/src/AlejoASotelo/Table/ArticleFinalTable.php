<?php

namespace AlejoASotelo\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Content;
use Joomla\CMS\Workflow\Workflow;
use AlejoASotelo\Table\MigratorArticleTable;

/**
 * Article table
 *
 * @since  1.5
 */
class ArticleFinalTable extends Content
{
    public $isNew = true;

    public $id_adapter;

    public function store($updateNulls = false)
    {
        $articleMigration = new MigratorArticleTable($this->_db);
        $articleMigration->load(['id_adapter' => $this->id_adapter, 'adapter' => $this->adapter]);

        $this->id = $articleMigration->id_joomla;
        $this->isNew = !$articleMigration->id_joomla;

        try {

            if (!parent::store($updateNulls)) {
                return false;
            }

            $this->saveWorkflow($this->id);

            $articleMigration->title = $this->title;
            $articleMigration->id_joomla = $this->id;
            $articleMigration->id_adapter = $this->id_adapter;
            $articleMigration->adapter = $this->adapter;

            $date = Factory::getDate()->toSql();
            if ($articleMigration->id) {
                $articleMigration->modified = $date;
            } else {
                $articleMigration->created = $date;
            }

            $result = $articleMigration->store();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            $result = false;
        }

        return $result;
    }

    public function saveWorkflow($idArticle)
    {
        $workflow = new Workflow('com_content.article');
        // Use default published stage of the default workflow
        $stageId = 1;
        try {
            $db = $this->_db;
            $query = $db->getQuery(true)
                ->select($db->quoteName('s.id'))
                ->from($db->quoteName('#__workflow_stages', 's'))
                ->join('INNER', $db->quoteName('#__workflows', 'w') . ' ON w.id = s.workflow_id')
                ->where('w.extension = ' . $db->quote('com_content.article'))
                ->where('w.default = 1')
                ->where('s.default = 1');
            $stageId = (int) $db->setQuery($query)->loadResult() ?: 1;
        } catch (\Exception $e) {
            $stageId = 1;
        }

        if ($workflow->getAssociation($idArticle)) {
            return $workflow->updateAssociations([$idArticle], $stageId);
        }

        return $workflow->createAssociation($idArticle, $stageId);
    }
}
