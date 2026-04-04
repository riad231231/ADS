<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

// Create a shortcut for params.
$params  = & $this->item->params;
$images  = json_decode($this->item->images);
$canEdit = $this->item->params->get('access-edit');
$info    = $params->get('info_block_position', 2);
$icons = ($params->get('show_print_icon') ||
	$params->get('show_email_icon') ||
	$canEdit);
$aInfo1 = ($params->get('show_publish_date') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author'));
$aInfo2 = ($params->get('show_create_date') || $params->get('show_modify_date') || $params->get('show_hits'));
$topInfo = ($aInfo1 && $info != 1) || ($aInfo2 && $info == 0);
$botInfo = ($aInfo1 && $info == 1) || ($aInfo2 && $info != 0);
$icons = $params->get('access-edit') || $params->get('show_print_icon') || $params->get('show_email_icon');

$currentDate   = Factory::getDate()->format('Y-m-d H:i:s');
$isUnpublished = ($this->item->state == ContentComponent::CONDITION_UNPUBLISHED || $this->item->publish_up > $currentDate)
    || ($this->item->publish_down < $currentDate && $this->item->publish_down !== null);
?>

	<?php if ($isUnpublished) : ?>
		<div class="system-unpublished">
	<?php endif; ?>

	<!-- Article -->
	<article>

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

				<?php echo JLayoutHelper::render('joomla.content.intro_image', $this->item); ?>

				<?php if (!$params->get('show_intro')) : ?>
					<?php echo $this->item->event->afterDisplayTitle; ?>
				<?php endif; ?>

				<?php echo $this->item->event->beforeDisplayContent; ?>

				<section class="article-intro clearfix">
					<?php echo $this->item->introtext; ?>
				</section>
    
		    <!-- footer -->
		    <?php if ($botInfo) : ?>
		    <footer class="article-footer clearfix">
		      <?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
		    </footer>
		    <?php endif; ?>
		    <!-- //footer -->

			    <?php if ($params->get('show_tags', 1) && !empty($this->item->tags)) : ?>
			      <?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
			    <?php endif; ?>

				<?php if ($params->get('show_readmore') && $this->item->readmore) :
					if ($params->get('access-view')) :
						$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid));
					else :
						$menu      = JFactory::getApplication()->getMenu();
						$active    = $menu->getActive();
						$itemId    = $active->id;
						$link1     = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
						$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid));
						$link      = new JURI($link1);
						$link->setVar('return', base64_encode($returnURL));
					endif;
					?>
					<section class="readmore">
						<a class="btn btn-default" href="<?php echo $link; ?>">
							<span>
							<?php if (!$params->get('access-view')) :
								echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
							elseif ($readmore = $this->item->alternative_readmore) :
								echo $readmore;
								if ($params->get('show_readmore_title', 0) != 0) :
									echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
								endif;
							elseif ($params->get('show_readmore_title', 0) == 0) :
								$readmoreText = version_compare(JVERSION, '4', 'ge') ? JText::_('JGLOBAL_READ_MORE') : JText::_('COM_CONTENT_READ_MORE_TITLE');
            		echo $readmoreText;
							else :
								$readmoreShowTitle = version_compare(JVERSION, '4', 'ge') ? JText::sprintf('JGLOBAL_READ_MORE_TITLE', HTMLHelper::_('string.truncate', $this->item->title, $params->get('readmore_limit'))) : JText::_('COM_CONTENT_READ_MORE') ." ".HTMLHelper::_('string.truncate', $this->item->title, $params->get('readmore_limit'));
            		echo $readmoreShowTitle;
							endif; ?>
							</span>
						</a>
					</section>
				<?php endif; ?>
	</article>
	<!-- //Article -->

	<?php if ($isUnpublished) : ?>
		</div>
	<?php endif; ?>

<?php echo $this->item->event->afterDisplayContent; ?>