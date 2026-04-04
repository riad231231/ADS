<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (is_array($this->getParam('skip_component_content')) && 
  in_array(JFactory::getApplication()->input->getInt('Itemid'), $this->getParam('skip_component_content'))) 
return;

/**
 * Mainbody 1 columns, content only
 */
?>

<div id="t3-mainbody" class="t3-mainbody">

		<!-- MAIN CONTENT -->
		<div id="t3-content" class="t3-content">
			<?php //if($this->hasMessage()) : ?>
			<jdoc:include type="message" />
			<?php //endif ?>
			<jdoc:include type="component" />
		</div>
		<!-- //MAIN CONTENT -->

</div> 