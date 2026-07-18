<?php

/*
 * ------------------------------------------------------------------------
 * Copyright (C) 2009 - 2015 The YouTech JSC. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: The YouTech JSC
 * Websites: http://www.smartaddons.com - http://www.cmsportal.net
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die();

if (defined('JPATH_PLUGINS') && is_file(JPATH_PLUGINS.'/system/j3legacy/j3legacy.php')) {
	require_once JPATH_PLUGINS.'/system/j3legacy/j3legacy.php';
	\PlgSystemJ3legacyBootstrap::register();
}



class PlgSystemYtInstallerScript
{
    /**
     * Called after any type of action
     */
    public function postFlight($route, $adapter)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->update('#__extensions')
            ->set("`enabled`='1'")
            ->where("`type`='plugin'")
            ->where("`folder`='system'")
            ->where("`element`='yt'");
        $db->setQuery($query);
        $db->execute();
        
        return true;
    }
}
