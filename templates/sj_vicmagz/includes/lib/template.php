<?php
/*
 * ------------------------------------------------------------------------
 * Copyright (C) 2009 - 2012 The YouTech JSC. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: The YouTech JSC
 * Websites: http://www.smartaddons.com - http://www.cmsportal.net
 * ------------------------------------------------------------------------
*/

/**** For each template, if there are special cases you can override the function here ***/

#[\AllowDynamicProperties]
class YtTemplate extends YtFrameworkTemplate {

	// Get Copyright Template
	/* Render as mod_footer */
	function getCopyright(){
		
		$app		= JFactory::getApplication();
		$date		= JFactory::getDate();
		$cur_year	= $date->format('Y');
		$csite_name	= $app->get('sitename');
		
		if (JString::strpos(JText :: _('MOD_FOOTER_LINE1'), '%date%')) {
			$line1 = str_replace('%date%', $cur_year, JText :: _('MOD_FOOTER_LINE1'));
		}
		else {
			$line1 = JText :: _('MOD_FOOTER_LINE1');
		}
		
		if (JString::strpos($line1, '%sitename%')) {
			$lineone = str_replace('%sitename%', $csite_name, $line1);
		}
		else {
			$lineone = $line1;
		}
		
		$ytTemplate = JFactory::getApplication()->getTemplate();
		
		$footer_logo   = $this->getParam('footer_logo');
		$joomla_create = $this->getParam('joomla_create');
		
		$copyright_center= (!$footer_logo && !$joomla_create ) ?  'copyright_middle' : '';
		?>
		
       
		
	
		<?php if($this->getParam('copyright')) : ?>
			<div class="copyright">
				<?php if($this->getParam('ytcopyright') !='') {
						$ytcopyright =  $this->getParam('ytcopyright');
						$ytcopyright = str_replace('{year}', $cur_year, $ytcopyright);
						echo $ytcopyright;
					}else{
						echo $lineone;
					}
				?>
			</div>
		<?php endif; ?>
			
        <?php if($this->getParam('joomla_create')) : ?>
			<div class="powered-by"><?php echo JText::_('MOD_FOOTER_LINE2'); ?></div>
		<?php endif; ?>
		
        <?php
	}
	
}



?>