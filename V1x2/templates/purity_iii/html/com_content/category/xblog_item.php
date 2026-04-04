<?php
 /**
 *------------------------------------------------------------------------------
 * @package Purity III Template - JoomlArt
 * @version 1.0 Feb 1, 2014
 * @author JoomlArt http://www.joomlart.com
 * @copyright Copyright (c) 2004 - 2014 JoomlArt.com
 * @license GNU General Public License version 2 or later;
 *------------------------------------------------------------------------------
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
if(version_compare(JVERSION, '3.0', 'lt')){
	JHtml::_('behavior.tooltip');
}
JHtml::_('behavior.framework');

// Create a shortcut for params.
$params  = & $this->item->params;
$images  = json_decode($this->item->images);
$canEdit = $this->item->params->get('access-edit');
$info    = $params->get('info_block_position', 2);
$aInfo1 = ($params->get('show_publish_date') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author'));
$aInfo2 = ($params->get('show_create_date') || $params->get('show_modify_date') || $params->get('show_hits'));
$topInfo = ($aInfo1 && $info != 1) || ($aInfo2 && $info == 0);
$botInfo = ($aInfo1 && $info == 1) || ($aInfo2 && $info != 0);
$icons = $params->get('access-edit') || $params->get('show_print_icon') || $params->get('show_email_icon');

// update catslug if not exists - compatible with 2.5
if (empty ($this->item->catslug)) {
	$this->item->catslug = $this->item->category_alias ? ($this->item->catid.':'.$this->item->category_alias) : $this->item->catid;
}

$currentDate   = Factory::getDate()->format('Y-m-d H:i:s');
$isUnpublished = ($this->item->state == ContentComponent::CONDITION_UNPUBLISHED || $this->item->publish_up > $currentDate)
    || ($this->item->publish_down < $currentDate && $this->item->publish_down !== null);
?>
	<?php if ($isUnpublished) : ?>
		<div class="system-unpublished">
	<?php endif; ?>

	<!-- Article -->
	<article>

		<!-- Intro image -->
		<div class="col-md-4">
			<?php echo JLayoutHelper::render('joomla.content.intro_image', $this->item); ?>
		</div>

		<div class="col-md-8">
			<?php if ($params->get('show_title')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.item_title', array('item' => $this->item, 'params' => $params, 'title-tag'=>'h2')); ?>
			<?php endif; ?>

			<!-- Aside -->
			<?php if ($topInfo || $icons) : ?>
			<aside class="article-aside clearfix">
				<?php if ($topInfo): ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
				<?php endif; ?>
				
				<?php if ($icons): ?>
				<?php echo JLayoutHelper::render('joomla.content.icons', array('item' => $this->item, 'params' => $params)); ?>
				<?php endif; ?>
			</aside>  
			<?php endif; ?>
			<!-- //Aside -->

			<section class="article-intro clearfix">
				<?php if (!$params->get('show_intro')) : ?>
					<?php echo $this->item->event->afterDisplayTitle; ?>
				<?php endif; ?>

				<?php echo $this->item->event->beforeDisplayContent; ?>

				<?php echo $this->item->introtext; ?>
			</section>
			
			<!-- footer -->
			<?php if ($botInfo) : ?>
			<footer class="article-footer clearfix">
				<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
			</footer>
			<?php endif; ?>
			<!-- //footer -->
			<?php if ($params->get('show_readmore')) :
				if ($params->get('access-view')) :
					$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
				else :
					$menu = JFactory::getApplication()->getMenu();
					$active = $menu->getActive();
					$itemId = $active->id;
					$link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
					$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)));
				endif; ?>

				<?php echo JLayoutHelper::render('joomla.content.readmore', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>

			<?php endif; ?>
		</div>
	</article>
	<!-- //Article -->

	<?php if ($isUnpublished) : ?>
		</div>
	<?php endif; ?>

<?php echo $this->item->event->afterDisplayContent; ?> 
