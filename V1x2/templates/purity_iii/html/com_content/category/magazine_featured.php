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

// get featured items
$params        = $this->params;
$count_leading = $params->get ('featured_leading', 1);
$count_intro   = $params->get ('featured_intro', 3);
$intro_columns = $params->get ('featured_intro_columns', 3);
$featured_links= $params->get ('featured_links', 5);
$leading       = $intro = $links = array();

$i = 0;
foreach ($this->items as &$item) {

	$item->event = new stdClass;

	// Old plugins: Ensure that text property is available
	if (!isset($item->text))
	{
		$item->text = $item->introtext;
	}
	JPluginHelper::importPlugin('content');
	jFactory::getApplication()->triggerEvent('onContentPrepare', array ('com_content.featured', &$item, &$this->params, 0));

	// Old plugins: Use processed text as introtext
	$item->introtext = $item->text;

	$results = jFactory::getApplication()->triggerEvent('onContentAfterTitle', array('com_content.featured', &$item, &$item->params, 0));
	$item->event->afterDisplayTitle = trim(implode("\n", $results));

	$results = jFactory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_content.featured', &$item, &$item->params, 0));
	$item->event->beforeDisplayContent = trim(implode("\n", $results));

	$results = jFactory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_content.featured', &$item, &$item->params, 0));
	$item->event->afterDisplayContent = trim(implode("\n", $results));

	if ($i < $count_leading) {
		$leading[] = $item;
	} elseif ($i < $count_leading + $count_intro) {
		$intro[] = $item;
	} elseif ($i < $count_leading + $count_intro + $featured_links) {
		$links[] = $item;
	}

	$i++;
}

//show info block?
$useDefList =
	($params->get('show_modify_date') ||
	$params->get('show_publish_date') ||
	$params->get('show_create_date') ||
	$params->get('show_hits') ||
	$params->get('show_category') ||
	$params->get('show_parent_category') ||
	$params->get('show_author'));

$info_positions = $params->get('featured_info_positions', array());
?>

<div class="magazine-featured">
	<div class="row">
		<div class="col-md-8">
			<?php if (count ($leading)): ?>
				<div class="magazine-leading magazine-featured-leading">
					<?php foreach ($leading as $item) :?>
						<div class="magazine-item">

							<?php echo JLayoutHelper::render('joomla.content.intro_image', $item); ?>

							<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>

							<?php // Todo Not that elegant would be nice to group the params ?>

							<?php if ($useDefList && in_array('leading', $info_positions) && in_array($params->get('info_block_position', 0), array(0, 2))) : ?>
							<aside class="article-aside clearfix">
								<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
							</aside>
							<?php endif; ?>

							<?php if (!$params->get('show_intro')) : ?>
								<?php echo $item->event->afterDisplayTitle; ?>
							<?php endif; ?>

							<?php echo $item->event->beforeDisplayContent; ?>
							<div class="magazine-item-ct">
								<?php echo $item->introtext; ?>
							</div>

							<?php if ($useDefList && in_array('leading', $info_positions) && in_array($params->get('info_block_position', 0), array(1, 2))) : ?>
								<aside class="article-aside clearfix">
									<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'below')); ?>
								</aside>
							<?php  endif; ?>

							<?php if ($params->get('show_readmore') && $item->readmore) :
							    if ($params->get('access-view')) :
							      $link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
							    else :
							      $menu = JFactory::getApplication()->getMenu();
							      $active = $menu->getActive();
							      $itemId = $active->id;
							      $link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
							      $link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)));
							    endif; ?>

							    <?php echo JLayoutHelper::render('joomla.content.readmore', array('item' => $item, 'params' => $params, 'link' => $link)); ?>

							<?php endif; ?>

							<?php echo $item->event->afterDisplayContent; ?>

						</div>
					<?php endforeach; ?>
				</div>
			<?php endif ?>
		</div> <!-- //Left Column -->

		<div class="col-md-4">
			<?php if (count ($links)): ?>
				<div class="magazine-links magazine-featured-links">
					<?php foreach ($links as $item) :?>
						<div class="magazine-item link-item">
							<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>
							<?php if ($useDefList && in_array('link', $info_positions)) : ?>
								<aside class="article-aside clearfix">
									<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
								</aside>
							<?php  endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif ?>
		</div> <!-- //Right Column -->
	</div> <!-- //Row -->

	<div class="row">
		<?php if ($intro_count = count ($intro)): ?>
			<div class="col-sm-12 magazine-intro magazine-featured-intro">
				<?php $intro_index = 0; ?>
				<?php foreach ($intro as $item) : ?>
					<?php if($intro_index % $intro_columns == 0) : ?>
						<div class="row">
					<?php endif ?>
							<div class="magazine-item col-sm-<?php echo round((12 / $intro_columns)) ?>">
								<?php echo JLayoutHelper::render('joomla.content.intro_image', $item); ?>
								<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>

								<?php if ($useDefList && in_array('intro', $info_positions)) : ?>
									<aside class="article-aside clearfix">
										<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
									</aside>
								<?php  endif; ?>

								<?php echo $item->event->afterDisplayTitle; ?>
								<?php echo $item->event->beforeDisplayContent; ?>
								<?php echo $item->event->afterDisplayContent; ?>
							</div>
					<?php $intro_index++; ?>
					<?php if(($intro_index % $intro_columns == 0) || $intro_index == $intro_count) : ?>
						</div>
					<?php endif ?>
				<?php endforeach; ?>
			</div>
		<?php endif ?>
	</div>

</div>