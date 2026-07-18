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
$typelayout = $yt->getParam('layouttype');

?>

<?php
if ($this->isRoot()){
	if(($typelayout=='res') || ($typelayout=='float')){ ?>
		<div id="menu" >
				<ul class="nav resmenu">
				<?php
				if($this->haveChild()){
					$idx = 0;
					foreach($this->getChild() as $child){
						++$idx;
						$child->getContent('collapse');
					}
				}
				?>
				</ul>
			
			
		</div>
	<?php
	}
}
?>
