<?php
/** 
 * YouTech menu template file.
 * 
 * @author The YouTech JSC
 * @package menusys
 * @filesource default.php
 * @license Copyright (c) 2011 The YouTech JSC. All Rights Reserved.
 * @tutorial http://www.smartaddons.com
 */
global $yt;
$document = JFactory::getDocument();

if ($this->isRoot()){
	// import assets
	$fancyMenu				    = $yt->getParam('fancyMenu');
	$moofx				= $yt->getParam('moofx');
	$classfancyMenu = $fancyMenu ==1 ? 'fancyMenu': '';
	
	$menucssid = $this->getParams()->get('menustyle') . 'navigator' . $this->getParams()->get('cssidsuffix');
	$addCssRight = $document->direction =='rtl' ? "rtl" : "";
	echo '<div class="mainmenu-wrapper hidden-sm hidden-xs"><ul id="'.$menucssid.'" class="'.$classfancyMenu.' hidden-sm hidden-xs navi'.($addCssRight=='rtl' ? ' navirtl':'').'">';
	if($this->haveChild()){
		$idx = 0;
		foreach($this->getChild() as $child){
			$child->addClass('level'.$child->get('level',1));
			++$idx;
			if ($idx==1){
				$child->addClass('first');
			} else if ($idx==$this->countChild()){
				$child->addClass('last');
			}
			if ($child->haveChild()){
				$child->addClass('havechild');
			}
			$child->getContent();
		}
	}
	echo "</ul></div>";
	echo "<button class='btn-hamburger js-slideout-toggle hidden-lg hidden-md' type='button'>
				<i class='fa fa-bars'></i> Menu
		</button>";
		
	
	
	
	if($moofx !='fade'){
		$document->addScript($yt->templateurl().'menusys/class/moo/assets/jquery.moomenu.js');
		$document->addScript($yt->templateurl().'menusys/class/common/js/jquery.easing.1.3.js');
	}
	else $document->addScript($yt->templateurl().'menusys/class/moo/assets/jquery.moomenu.css.js');
	if($fancyMenu) $document->addScript($yt->templateurl().'menusys/class/common/js/gooeymenu.js');
	
	$duration   = $this->getParams()->get('moofxduration', '300');
	$transition = $this->getParams()->get('moofx', 'linear');
	$justify 	= 	$document->direction !='rtl' ? 'left' : 'right';
	
	?>
	
	<script type="text/javascript">
		jQuery(function($){
			<?php if($fancyMenu) {?>
				gooeymenu.setup({id:'<?php echo $menucssid?>', fx:'swing'})
			<?php };?>
			
            $('#moonavigator').moomenu({ 
            	'wrap':'#yt_menuwrap .container',
            	'easing': '<?php echo $transition ?>',
				'speed': '<?php echo $duration ?>',
				'justify':'<?php echo $justify ?>'
            });
	    });
	</script>
	<?php
} else if ( $this->canAccess() ){
	$haveChild = $this->haveChild();
	$liClass = $this->haveClass() ? "class=\"{$this->getClass()}\"" : "";
?>

<li <?php echo $liClass; ?>>
	<?php echo $this->getLink(); ?>
	<?php
		if($haveChild){
			$levelClassName = 'level'.($this->get('level',1)+1);
			$subStyleWidth = $this->getSubmenuWidth();
			
			echo "<ul class=\"{$levelClassName} subnavi\" $subStyleWidth>";
			$cidx = 0;
			foreach($this->getChild() as $child){
				$child->addClass($levelClassName);
				++$cidx;
				if ($cidx==1){
					$child->addClass('first');
				} else if ($cidx==$this->countChild()){
					$child->addClass('last');
				}
				if ($child->haveChild()){
					$child->addClass('havechild');
				}
				$child->getContent();
			}
			echo "</ul>";
		}
	?>
</li>

<?php 
}
?>