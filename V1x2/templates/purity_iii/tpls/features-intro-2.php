<?php
/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2017 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org 
 *------------------------------------------------------------------------------
 */

$responsive = $this->getParam('responsive', 1);
$resClass = "";
if ($responsive==0){
	$resClass = "no-responsive";
}

defined('_JEXEC') or die;
  $app = JFactory::getApplication();
  $menu = $app->getMenu();
  $lang = JFactory::getLanguage();
  $hide_mainbody = (!$this->params->get("ZEN_MAINBODY_DISABLED",true)==false && ($menu->getActive() == $menu->getDefault( $lang->getTag() )));

  $thisClass = get_class($this) ;
  if($thisClass == "T3TemplateLayout") {
    $hide_mainbody = 0;
  }

  // Detecting Active Variables
  $option   = $app->input->getCmd('option', '');
  $view     = $app->input->getCmd('view', '');
  $layout   = $app->input->getCmd('layout', '');
  $task     = $app->input->getCmd('task', '');
  $itemid   = $app->input->getCmd('Itemid', '');
  $sitename = $app->get('sitename');
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>"
	  class='<jdoc:include type="pageclass" /> <?php echo $resClass ?>'>

<head>
	<jdoc:include type="head" />
	<?php $this->loadBlock('head') ?>
</head>

<body>

<div class="t3-wrapper features-intro features-intro-2"> <!-- Need this wrapper for off-canvas menu. Remove if you don't use of-canvas -->

	<?php $this->loadBlock('header') ?>

  <?php if(!$hide_mainbody || $option == "com_config") {
    $this->loadBlock ('mainbody');
  } ?>

	<?php $this->loadBlock('features-intro-2') ?>

	<?php $this->loadBlock('footer') ?>

</div>


</body>
</html>