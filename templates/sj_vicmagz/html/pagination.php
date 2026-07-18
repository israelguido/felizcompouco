<?php

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

function pagination_list_render($list) {
	// Initialize variables
	$html = '<ul class="pagination">';
	
	//if ($list['start']['active']==1)   $html .= $list['start']['data'];
	if ($list['previous']['active']==1) $html .= $list['previous']['data'];

	foreach ($list['pages'] as $page) {
		$html .= $page['data'];
	}

	if ($list['next']['active']==1) $html .= $list['next']['data'];
	//if ($list['end']['active']==1)  $html .= $list['end']['data'];

	$html .= "</ul>";
	
	return $html;
}

function pagination_item_active(&$item) {
	
	$cls = '';
	
    if ($item->text == JText::_('Next')) { $item->text = 'Next'; $cls = "next";}
    if ($item->text == JText::_('Prev')) { $item->text = 'Prev'; $cls = "previous";}
    
	if ($item->text == JText::_('First')) { $cls = "first";}
    if ($item->text == JText::_('Last'))   { $cls = "last";}
	
    return "<li><a class='" . $cls . "' href='" . $item->link . "' title='" . $item->text . "'>" . $item->text . "</a></li>";
}

function pagination_item_inactive( &$item ) {
	$cls = (int)$item->text > 0 ? 'active': 'disabled';
	return "<li class='" . $cls . "'><span >" . $item->text . "</a></li>";
}
