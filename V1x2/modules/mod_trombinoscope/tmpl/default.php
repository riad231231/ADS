<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */
 
// no direct access
defined('_JEXEC') or die;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');

$i = 0;
$contactcount = count($list);
$previous_header = '';
$header = '';
?>

<!--[if lte IE 8]>
	<div class="te_<?php echo $class_suffix; ?><?php echo $arrow_class; ?> ie8">
<![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<div class="te_<?php echo $class_suffix; ?><?php echo $arrow_class; ?>">
<!--<![endif]-->

<?php if (empty($cat_order) && $show_category_header) : ?>
	<div class="extension-message warning">
		<dl>
			<dt><?php echo JText::_('WARNING'); ?></dt>
			<dd><?php echo JText::_('MOD_TROMBINOSCOPE_MESSAGE_ORDERFORCATEGORYHEADER'); ?></dd>
		</dl>
	</div>
<?php endif; ?>

	<?php if ($show_arrows && ($arrow_prev_left || $arrow_prev_top)) : ?>
		<div class="items_pagination top">
			<?php if ($arrow_prev_left) : ?>
				<a id="prev_<?php echo $class_suffix; ?>" class="previous" href="#"><span class="SYWicon-arrow-left2"></span></a>
			<?php endif; ?>
			
			<?php if ($arrow_prev_top) : ?>
				<a id="prev_<?php echo $class_suffix; ?>" class="previous" href="#"><span class="SYWicon-arrow-up2"></span></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="personlist">

		<?php foreach ($list as $item) : ?>	
			
			<?php
				// if the user is logged in and the user is linked to the contact, allow edit through the profile.
				$allow_edit = false;
				if ($user->get('guest') != 1 && $item->user_id == $user->get('id')) {
					$allow_edit = true;
				}
			
				// header
				if ($cat_order && $show_category_header) {
					$header = $item->category;
					if ($previous_header == $header) {
						$header = 'no_show';
					} else {
						$previous_header = $header;
					}
				}	
					
				// Convert parameter fields to objects.
				$itemparams = new JRegistry();
				$itemparams->loadString($item->params);				
				$itemparams->set('id', $item->slug);
				$itemparams->set('catid', $item->catid);				
			
				// link
				$link = '';
				$extra_attributes = '';
				if (in_array($link_access, $groups)) {
					switch ($contact_link) {
						case 'view' :
							$link = JRoute::_(modTrombinoscopeHelper::getContactRoute('trombinoscopeextended', $item->slug, $item->catid));
						break;
						case 'standard' :
							$link = JRoute::_(modTrombinoscopeHelper::getContactRoute('contact', $item->slug, $item->catid));
						break;
						case 'popup' : // open in a modal window
							$link = JRoute::_(modTrombinoscopeHelper::getContactRoute('contact', $item->slug, $item->catid).'&tmpl=component');
							JHtml::_('behavior.modal', 'a.modal');
							$extra_attributes = ' class="modal" rel="{handler: \'iframe\', size: {x:'.$popup_x.', y:'.$popup_y.'}}"';
						break;
						case 'linka' :
							$extra_attributes = ' target="_blank"';
						case 'linka_sw' :
							$link = $itemparams->get('linka');
						break;
						case 'linkb' :
							$extra_attributes = ' target="_blank"';
						case 'linkb_sw' :
							$link = $itemparams->get('linkb');
						break;
						case 'linkc' :
							$extra_attributes = ' target="_blank"';
						case 'linkc_sw' :
							$link = $itemparams->get('linkc');
						break;
						case 'linkd' :
							$extra_attributes = ' target="_blank"';
						case 'linkd_sw' :
							$link = $itemparams->get('linkd');
						break;
						case 'linke' :
							$extra_attributes = ' target="_blank"';
						case 'linke_sw' :
							$link = $itemparams->get('linke');
						break;
						case 'generic' :
							$extra_attributes = ' target="_blank"';
						case 'generic_sw' :
							$link = $generic_link;
						break;
						default :
						break;
					}
				}
				
				$link_category = '';
				if ($link_to_category || $link_to_category_header) {
					$link_category = JRoute::_(modTrombinoscopeHelper::getCategoryRoute($item->catid));
				}
				
				// name format
				$formatted_name = modTrombinoscopeHelper::getFormattedName($item->name, $format_style, $uppercase);
						
				$extraclasses = " personid-".$item->id." catid-".$item->catid;
				
				if ($i % 2) {
					$extraclasses .= " even";
				} else {
					$extraclasses .= " odd";
				}
				
				if ($item->featured && $show_featured) {
					$extraclasses .= " featured";
				}
				
				if ($style_social_icons) {
					$extraclasses .= " social";
				}
				
				if (!$show_picture) {
					$extraclasses .= " text_only";
				} else {
					$extraclasses .= " ";
					if (!$keep_picture_space && empty($item->image) && empty($default_picture) && $globalparams->get('default_image') == null) {
						$extraclasses .= "text_only ghost_";
					}
					switch ($photo_align) {
						case 'l':
							$extraclasses .= "picture_left";
							break;
						case 'r':
							$extraclasses .= "picture_right";
							break;
						case 't':
							$extraclasses .= "picture_top";
							break;
						case 'lr':
							if ($i % 2) {
								$extraclasses .= "picture_right";
							} else {
								$extraclasses .= "picture_left";
							}
							break;
						case 'rl':
							if ($i % 2) {
								$extraclasses .= "picture_left";
							} else {
								$extraclasses .= "picture_right";
							}
							break;
						default :
							$extraclasses .= "picture_left";
						break;
					}
				}
				
				if ($show_picture && $crop_picture) {	
					if (empty($item->image)) {
						if (empty($default_picture)) {
							if ($globalparams->get('default_image') != null) {
								$item->image = modTrombinoscopeHelper::getCroppedImage($class_suffix, 'global', $globalparams->get('default_image'), $tmp_path, $clear_cache, $picture_width, $picture_height, $crop_picture, $filter);
							} else {
								$item->image = '';
							}
						} else {
							$item->image = modTrombinoscopeHelper::getCroppedImage($class_suffix, 'default', $default_picture, $tmp_path, $clear_cache, $picture_width, $picture_height, $crop_picture, $filter);
						}
					} else {		
						$item->image = modTrombinoscopeHelper::getCroppedImage($class_suffix, $item->id, $item->image, $tmp_path, $clear_cache, $picture_width, $picture_height, $crop_picture, $filter);
					}
					
					if ($item->image == 'error') {
						$item->error[] = JText::_('MOD_TROMBINOSCOPE_ERROR_CREATINGTHUMBNAIL');
						$item->image = '';
					}
				}
				
				$i++;
				//$lastcontact = next($list) === false;
			?>
			
			<?php if ($header && $header != 'no_show') : ?>	
				<?php if ($i>1) : ?>
					</div>
				<?php endif; ?>	
				<div class="groupheader">
					<?php if ($show_category_header) : ?>
						<?php echo '<h'.$header_html_tag.' class="header">'; ?>
							<?php if ($link_to_category_header) : ?>
								<a href="<?php echo $link_category; ?>" title="<?php echo $header ?>">
									<span><?php echo $header ?></span>
								</a>
							<?php elseif ($link_to_view_category_header && !empty($header_view_id)) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_trombinoscopeextended&view=trombinoscope&Itemid='.$header_view_id.'&category='.$item->catid); ?>">
									<span><?php echo $header; ?></span>
								</a>
							<?php else : ?>	
								<span><?php echo $header ?></span>
							<?php endif; ?>
						<?php echo '</h'.$header_html_tag.'>'; ?>
					<?php endif; ?>
				</div>	
				<div class="contactgroup">
			<?php endif; ?>
			
			<div class="person<?php echo $extraclasses ?>">
				<div class="outerperson">
			
					<?php if ($show_errors && !empty($item->error)) : ?>
						<div class="extension-message error">
							<dl>
								<dt><?php echo JText::_('ERROR'); ?></dt>
								<?php foreach ($item->error as $error) :  ?>
									<dd><?php echo $error; ?></dd>
								<?php endforeach; ?>
							</dl>
						</div>
					<?php endif; ?>
						
					<?php if ($show_links) : ?>				
						<div class="iconlinks">	
							<ul>			
								<?php if (!empty($item->fieldl1)) { echo modTrombinoscopeHelper::getFieldOutput(1, $item->fieldl1, $item->fieldnamel1, $linkfield1_access, '', '', $itemparams, $globalparams, $trombparams, true);} ?>
								<?php if (!empty($item->fieldl2)) { echo modTrombinoscopeHelper::getFieldOutput(2, $item->fieldl2, $item->fieldnamel2, $linkfield2_access, '', '', $itemparams, $globalparams, $trombparams, true);} ?>
								<?php if (!empty($item->fieldl3)) { echo modTrombinoscopeHelper::getFieldOutput(3, $item->fieldl3, $item->fieldnamel3, $linkfield3_access, '', '', $itemparams, $globalparams, $trombparams, true);} ?>
								<?php if (!empty($item->fieldl4)) { echo modTrombinoscopeHelper::getFieldOutput(4, $item->fieldl4, $item->fieldnamel4, $linkfield4_access, '', '', $itemparams, $globalparams, $trombparams, true);} ?>
								<?php if (!empty($item->fieldl5)) { echo modTrombinoscopeHelper::getFieldOutput(5, $item->fieldl5, $item->fieldnamel5, $linkfield5_access, '', '', $itemparams, $globalparams, $trombparams, true);} ?>
							</ul>
						</div>	
					<?php endif; ?>
						
					<?php if ($show_vcard) : ?>
						<div class="vcard">
							<?php if ($standalone) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_contact&view=contact&id='.$item->id.'&format=vcf'); ?>" title="<?php echo JText::_('MOD_TROMBINOSCOPE_DOWNLOAD_VCARD');?>">
									<i class="icon SYWicon-vcard"></i><span><?php echo JText::_('MOD_TROMBINOSCOPE_VCARD');?></span>
								</a>
							<?php else : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_trombinoscopeextended&view=contact&id='.$item->id.'&format=vcf&type='.$vcard_type.'&af='.$address_format); ?>" title="<?php echo JText::_('MOD_TROMBINOSCOPE_DOWNLOAD_VCARD');?>">
									<i class="icon SYWicon-vcard"></i><span><?php echo JText::_('MOD_TROMBINOSCOPE_VCARD');?></span>
								</a>
							<?php endif; ?>
						</div>	
					<?php endif; ?>
					
					<div class="innerperson">
						<div class="personpicture">
							<div class="picture<?php echo ($picture_hover_type != 'none' && !empty($link)) ? ' '.$picture_hover_type : ''; ?>">
								<div class="picture_veil">
									<?php if ($item->featured && $show_featured) : ?>
										<div class="feature">
											<i class="icon SYWicon-star3"></i>
										</div>
									<?php endif; ?>
								</div>
								<?php if (!empty($link)) : ?>	
									<a<?php echo $extra_attributes; ?> href="<?php echo $link; ?>" title="<?php echo $formatted_name; ?>" >
										<?php if (!empty($item->image)) : ?>
											<?php echo JHTML::_('image', $item->image, $formatted_name, array('title' => $formatted_name)); ?>
										<?php elseif (!empty($default_picture)) : ?>
											<?php echo JHTML::_('image', $default_picture, $formatted_name, array('title' => $formatted_name)); ?>
										<?php elseif ($globalparams->get('default_image') != null) : ?>	
											<?php echo JHTML::_('image', $globalparams->get('default_image'), $formatted_name, array('title' => $formatted_name)); ?>
										<?php else : ?>							
											<span class="nopicture">&nbsp;</span>
										<?php endif; ?>
									</a>
								<?php else : ?>
									<?php if (!empty($item->image)) : ?>
										<?php echo JHTML::_('image', $item->image, $formatted_name, array('title' => $formatted_name)); ?>
									<?php elseif (!empty($default_picture)) : ?>
										<?php echo JHTML::_('image', $default_picture, $formatted_name, array('title' => $formatted_name)); ?>
									<?php elseif ($globalparams->get('default_image') != null) : ?>	
										<?php echo JHTML::_('image', $globalparams->get('default_image'), $formatted_name, array('title' => $formatted_name)); ?>
									<?php else : ?>							
										<span class="nopicture">&nbsp;</span>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						</div>
					
						<div class="personinfo">	
							<div class="text_veil">
								<?php if ($item->featured && $show_featured) : ?>
									<div class="feature">
										<i class="icon SYWicon-star3"></i>
									</div>
								<?php endif; ?>
							</div>				
							
							<?php if ($show_category) : ?>
								<div class="category">
									<?php if ($link_to_category) : ?>												
										<a href="<?php echo $link_category; ?>" title="<?php echo $item->category; ?>" >
											<span><?php echo $item->category; ?></span>
										</a>
									<?php elseif ($link_to_view_category && !empty($cat_view_id)) : ?>												
										<a href="<?php echo JRoute::_('index.php?option=com_trombinoscopeextended&view=trombinoscope&Itemid='.$cat_view_id.'&category='.$item->catid); ?>" title="<?php echo $item->category; ?>">
											<span><?php echo $item->category; ?></span>
										</a>	
									<?php else : ?>
										<span><?php echo $item->category; ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							
							<?php if ($show_tags) : ?>
								<?php if (isset($item->tags)) : ?>
									<div class="tags">
										<?php foreach ($item->tags as $tag) :  ?>
											<?php if ($link_to_tags) : ?>
												<i class="icon SYWicon-tag2"></i><span class="tag-<?php echo $tag->id; ?>"><a href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($tag->id . ':' . $tag->alias)) ?>"><?php echo $tag->title; ?></a></span>									
											<?php elseif ($link_to_view_tags && !empty($tags_view_id)) : ?>
												<i class="icon SYWicon-tag2"></i><span class="tag-<?php echo $tag->id; ?>"><a href="<?php echo JRoute::_('index.php?option=com_trombinoscopeextended&view=trombinoscope&Itemid='.$tags_view_id.'&tag='.$tag->id); ?>"><?php echo $tag->title; ?></a></span>
											<?php else : ?>
												<i class="icon SYWicon-tag2"></i><span class="tag-<?php echo $tag->id; ?>"><?php echo $tag->title; ?></span>
											<?php endif; ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>	
							<?php endif; ?>	
							
							<?php if (empty($link_label) && !empty($link)) : ?>									
								<?php
									$generated_link_tag = '<a'.$extra_attributes.' href="'.$link.'" title="'.$formatted_name.'">';
									$generated_link_tag .= '<span>'.$formatted_name.'</span>';
									$generated_link_tag .= '</a>';
								?>
								<?php echo modTrombinoscopeHelper::getFieldOutput(0, $generated_link_tag, 'name_link', 1, $pre_name, $name_label, $itemparams, $globalparams, $trombparams); ?>
							<?php else : ?>
								<?php echo modTrombinoscopeHelper::getFieldOutput(0, $formatted_name, 'name', 1, $pre_name, $name_label, $itemparams, $globalparams, $trombparams); ?>
							<?php endif; ?>
							
							<?php if (!empty($item->fieldname1)) { echo modTrombinoscopeHelper::getFieldOutput(1, $item->field1, $item->fieldname1, $field1_access, $show_field1_label, $field1_label, $itemparams, $globalparams, $trombparams);} ?>
							<?php if (!empty($item->fieldname2)) { echo modTrombinoscopeHelper::getFieldOutput(2, $item->field2, $item->fieldname2, $field2_access, $show_field2_label, $field2_label, $itemparams, $globalparams, $trombparams);} ?>				
							<?php if (!empty($item->fieldname3)) { echo modTrombinoscopeHelper::getFieldOutput(3, $item->field3, $item->fieldname3, $field3_access, $show_field3_label, $field3_label, $itemparams, $globalparams, $trombparams);} ?>				
							<?php if (!empty($item->fieldname4)) { echo modTrombinoscopeHelper::getFieldOutput(4, $item->field4, $item->fieldname4, $field4_access, $show_field4_label, $field4_label, $itemparams, $globalparams, $trombparams);} ?>				
							<?php if (!empty($item->fieldname5)) { echo modTrombinoscopeHelper::getFieldOutput(5, $item->field5, $item->fieldname5, $field5_access, $show_field5_label, $field5_label, $itemparams, $globalparams, $trombparams);} ?>				
							<?php if (!empty($item->fieldname6)) { echo modTrombinoscopeHelper::getFieldOutput(6, $item->field6, $item->fieldname6, $field6_access, $show_field6_label, $field6_label, $itemparams, $globalparams, $trombparams);} ?>				
							<?php if (!empty($item->fieldname7)) { echo modTrombinoscopeHelper::getFieldOutput(7, $item->field7, $item->fieldname7, $field7_access, $show_field7_label, $field7_label, $itemparams, $globalparams, $trombparams);} ?>
							
							<?php
								$show_go = false;
								if (!empty($link_label) && !empty($link)) {
									$show_go = true;
								}
							
								$show_edit = false;
								if ($allow_edit && $link_to_edit && JPluginHelper::isEnabled('user', 'editcontactinprofile')) {
									$show_edit = true;
								}
							?>
							
							<?php if ($show_go || $show_edit) : ?>				
								<div class="personlinks">
									<?php if ($show_go) : ?>	
										<div class="personlink go">
											<a<?php echo $extra_attributes; ?> href="<?php echo $link; ?>" title="<?php echo $formatted_name; ?>">									
												<i class="icon SYWicon-arrow-right"></i><span><?php echo $link_label; ?></span>
											</a>
										</div>
									<?php endif; ?>
									
									<?php if ($show_edit) : ?>					
										<div class="personlink edit">
											<a href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id='.$item->user_id);?>" title="<?php echo JText::_('MOD_TROMBINOSCOPE_EDIT_CONTACT'); ?>">
												<?php if (empty($edit_link_label)) : ?>
													<i class="icon SYWicon-pencil"></i><span><?php echo JText::_('MOD_TROMBINOSCOPE_EDIT_CONTACT'); ?></span>
												<?php else : ?>
													<i class="icon SYWicon-pencil"></i><span><?php echo trim($edit_link_label); ?></span>
												<?php endif; ?>
											</a>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div><!-- end personinfo -->					
					</div><!-- end innerperson -->
				</div><!-- end outerperson -->
			</div><!-- end person -->
			
			<?php if ($i == $contactcount && $header) : ?>
				</div>
			<?php endif; ?>
			
		<?php endforeach; ?>
	</div>
	
	<?php if ($show_arrows && ($arrow_prevnext_bottom || $arrow_next_right || $arrow_next_bottom)) : ?>
		<div class="items_pagination bottom">
		
			<?php if ($arrow_prevnext_bottom) : ?>
				<a id="prev_<?php echo $class_suffix; ?>" class="previous" href="#"><span class="SYWicon-arrow-left2"></span></a>
				<a id="next_<?php echo $class_suffix; ?>" class="next" href="#"><span class="SYWicon-arrow-right2"></span></a>
			<?php endif; ?>
			
			<?php if ($arrow_next_right) : ?>
				<a id="next_<?php echo $class_suffix; ?>" class="next" href="#"><span class="SYWicon-arrow-right2"></span></a>
			<?php endif; ?>
			
			<?php if ($arrow_next_bottom) : ?>
				<a id="next_<?php echo $class_suffix; ?>" class="next" href="#"><span class="SYWicon-arrow-down2"></span></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	
</div>
