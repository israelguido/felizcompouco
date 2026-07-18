<?php
	 /**
    * @package YT Framework
    * @author Smartaddons http://www.Smartaddons.com
    * @copyright Copyright (c) 2009 - 2014 Smartaddons
    * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
    */
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldYtColor extends JFormField
{
	public $type = 'YtColor';

	protected function getInput() {
		$className = $this->element['class']; 
		$html = '';
		$html .= '
		<div class="ytcolor-wrap" id="'.$this->id.'_wrap">
			<input type="text" value="'.$this->value.'" name="'.$this->name.'" class="'.$className.' color-picker " id="'.$this->id.'" autocomplete="off" size="8" maxlength="8">
		</div>
		<script language="javascript" type="text/javascript">
		jQuery(document).ready(function($){
			$("#'.$this->id.'_wrap input.color-picker").miniColors({
				change: function(hex, rgb) {
					
				}
			});
		});
		</script>';
		
		
		return $html;
	}
}
