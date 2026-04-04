<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.categories');

jimport('syw.image');
jimport('syw.tags');

abstract class modTrombinoscopeHelper
{		
	protected static $lookup;
		
	static $selection;
	static $categories;
	static $tags;
	static $show_tags;
	static $contact_id;
	static $contact_ids_exclude;
	static $metakeys;
	static $related_id;
	static $field1;
	static $field2;
	static $field3;
	static $field4;
	static $field5;
	static $field6;
	static $field7;
	static $linkfield1;
	static $linkfield2;
	static $linkfield3;
	static $linkfield4;
	static $linkfield5;
	static $count;
	static $catorder;
	static $order;
	static $manual_order_ids;
	static $address_format;	
	static $featured;	
	static $text_type;
	static $format_style;

	static $sort_locale;
	
	static function getContacts($params, $module)
	{		
		// get database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		self::$sort_locale = $params->get('sort_locale', 'en_US');		
		
		// contact selection
		self::$selection = $params->get('selection', 'categories');
		
		// specific contact
		self::$contact_id = $params->get('contact_id', '');
		
		// exclude		
		self::$contact_ids_exclude = trim($params->get('ex', ''));
		
		// categories	
		self::$categories = self::getCategories($params->get('cat', array()), $params->get('includesubcat', 'no'));
				
		// tags
		self::$tags = $params->get('tags', array());
		
		self::$show_tags = false;
		switch ($params->get('s_tag', 'h')) {
			case 's' :
			case 'sl' :
			case 'sv' :
				self::$show_tags = true;
				break;			
			default :
				break;
		}
				
		self::$metakeys = array();
		self::$related_id = ''; // to avoid the contact to be visible in the list of related contacts
		$keys = '';
		
		if (self::$selection == 'related') {
			$option = JRequest::getCmd('option');
			$view = JRequest::getCmd('view');
			$temp = JRequest::getString('id');
			$temp = explode(':', $temp);
			$id = $temp[0];			
			if (($option == 'com_contact' || $option == 'com_trombinoscopeextended') && $view == 'contact' && $id) { // the content is a standard contact or a TE contact page
				$query->select('metakey');
				$query->from('#__contact_details');
				$query->where('id = ' . (int) $id);
				$db->setQuery($query);
				$results = trim($db->loadResult());
				if (empty($results)) {
					return array(); // won't find a related contact if no key is present
				}				
				$keys = explode(',', $results);	
				$query->clear();				
				self::$related_id = $id;
			} else {
				return null; // no result if not a contact page
			}
		} else {
			// explode the meta keys on a comma
			$keys = explode(',', $params->get('keys', ''));
		}
		
		// assemble any non-blank word(s)
		foreach ($keys as $key) {
			$key = trim($key);
			if ($key) {
				self::$metakeys[] = $key;
			}
		}
	
		self::$field1 = $params->get('f1', 'none');
		self::$field2 = $params->get('f2', 'none');
		self::$field3 = $params->get('f3', 'none');
		self::$field4 = $params->get('f4', 'none');
		self::$field5 = $params->get('f5', 'none');
		self::$field6 = $params->get('f6', 'none');
		self::$field7 = $params->get('f7', 'none');
	
		self::$linkfield1 = $params->get('lf1', 'none');
		self::$linkfield2 = $params->get('lf2', 'none');
		self::$linkfield3 = $params->get('lf3', 'none');
		self::$linkfield4 = $params->get('lf4', 'none');
		self::$linkfield5 = $params->get('lf5', 'none');
	
		self::$count = intval($params->get('count', '0'));
		self::$catorder = $params->get('c_order', '');
		self::$order = $params->get('order', 'oa');
		
		self::$manual_order_ids = trim($params->get('manual_ids', ''));
		
		self::$featured = $params->get('f', 's');
		self::$format_style = $params->get('name_fmt', 'none');
			
		self::$address_format = $params->get('a_fmt', 'zss');		
		self::$text_type = $params->get('t', 'info');
				
		$query = self::_buildQuery();
		if ($query === null) {
			return null;
		}
		
		$db->setQuery($query);
	
		$list = $db->loadObjectList();
		
		if ($error = $db->getErrorMsg()) {
			throw new Exception($error);
		}
				
		// filter by tags, if any
		if (!empty(self::$tags)) {
			$contact_list = array();
			
			// if all selected, get all available tags
			$array_of_tag_values = array_count_values(self::$tags);
			if (isset($array_of_tag_values['all']) && $array_of_tag_values['all'] > 0) { // 'all' was selected
				self::$tags = array();
				$tag_objects = SYWTags::getTags('com_contact.contact');
				if ($tag_objects !== false) {					
					foreach ($tag_objects as $tag_object) {
						self::$tags[] = $tag_object->id;
					}
				}
			}			
			
			if (!empty(self::$tags)) { // will be empty if getting all tags fails
				$helper_tags = new JHelperTags;
				foreach ($list as $item) {
					$item->tags = $helper_tags->getItemTags('com_contact.contact', $item->id, true); // array of tag objects
					
					foreach ($item->tags as $tag) {					
						if (array_search($tag->tag_id, self::$tags) !== false) {
							$contact_list[] = $item;
							break;
						}
					}
				}
				
				$list = $contact_list;
			}
		}		
		
		foreach ($list as $item) {
			$item->firstpart = self::_substring_index(trim($item->name), ' ', 1);
			$item->secondpart = self::_substring_index(self::_substring_index(trim($item->name), ' ', 2), ' ', -1);
			$item->lastpart = self::_substring_index(trim($item->name), ' ', -1);
			
			if (empty(self::$tags) && self::$show_tags) { // did not have to go through the previous filter but still need to get the tags
				$helper_tags = new JHelperTags;
				$item->tags = $helper_tags->getItemTags('com_contact.contact', $item->id, true); // array of tag objects
			}
			
		}
		
		$catorder_param = $params->get('c_order', '');
		$order_param = $params->get('order', 'oa');
		
		if (self::$format_style != '') {
			if ($catorder_param == "oa") {
				switch ($order_param) {
					case 'fnf_fa' : // follow the name format - order on 1st part (asc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCascSascFasc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCascFascSasc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCascLascFasc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCascFascLasc"); break;
							default : break;
						}
						break;
					case 'fnf_fd' : // follow the name format - order on 1st part (desc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCascSdescFdesc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCascFdescSdesc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCascLdescFdesc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCascFdescLdesc"); break;
							default : break;
						}
						break;
					case 'fnf_la' : // follow the name format - order on 2nd part (asc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCascFascSasc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCascSascFasc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCascFascLasc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCascLascFasc"); break;
							default : break;
						}
						break;
					case 'fnf_ld' : // follow the name format - order on 2nd part (desc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCascFdescSdesc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCascSdescFdesc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCascFdescLdesc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCascLdescFdesc"); break;
							default : break;
						}
						break;
					default : break;
				}
			} else if ($catorder_param == "od") {
				switch ($order_param) {
					case 'fnf_fa' : // follow the name format - order on 1st part (asc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCdescSascFasc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCdescFascSasc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCdescLascFasc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCdescFascLasc"); break;
							default : break;
						}
						break;
					case 'fnf_fd' : // follow the name format - order on 1st part (desc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCdescSdescFdesc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCdescFdescSdesc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCdescLdescFdesc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCdescFdescLdesc"); break;
							default : break;
						}
						break;
					case 'fnf_la' : // follow the name format - order on 2nd part (asc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCdescFascSasc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCdescSascFasc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCdescFascLasc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCdescLascFasc"); break;
							default : break;
						}
						break;
					case 'fnf_ld' : // follow the name format - order on 2nd part (desc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortCdescFdescSdesc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortCdescSdescFdesc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortCdescFdescLdesc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortCdescLdescFdesc"); break;
							default : break;
						}
						break;
					default : break;
				}
			} else {
				switch ($order_param) {
					case 'fnf_fa' : // follow the name format - order on 1st part (asc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortSascFasc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortFascSasc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortLascFasc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortFascLasc"); break;
							default : break;
						}
						break;
					case 'fnf_fd' : // follow the name format - order on 1st part (desc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortSdescFdesc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortFdescSdesc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortLdescFdesc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortFdescLdesc"); break;
							default : break;
						}
						break;
					case 'fnf_la' : // follow the name format - order on 2nd part (asc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortFascSasc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortSascFasc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortFascLasc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortLascFasc"); break;
							default : break;
						}
						break;
					case 'fnf_ld' : // follow the name format - order on 2nd part (desc)
						switch (self::$format_style) {
							case 'rsf' : case 'rcf' : case 'rsfd' : case 'rdsf' : usort($list, "modTrombinoscopeHelper::sortFdescSdesc"); break;
							case 'fsr' : case 'fcr' : case 'fdsr' : case 'fsrd' : usort($list, "modTrombinoscopeHelper::sortSdescFdesc"); break;
							case 'lsp' : case 'lcp' : case 'ldsp' : case 'lspd' : usort($list, "modTrombinoscopeHelper::sortFdescLdesc"); break;
							case 'psl' : case 'pcl' : case 'psld' : case 'pdsl' : usort($list, "modTrombinoscopeHelper::sortLdescFdesc"); break;
							default : break;
						}
						break;
					default : break;
				}
			}
		}
		
		if (self::$count > 0) {
			$list = array_slice($list, 0, self::$count, true);
		}
		
		return $list;
	}
	
	protected static function _buildQuery()
	{
		// get database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		
		$subquery1 = ' CASE WHEN ';
		$subquery1 .= $query->charLength('cd.alias');
		$subquery1 .= ' THEN ';
		$cd_id = $query->castAsChar('cd.id');
		$subquery1 .= $query->concatenate(array($cd_id, 'cd.alias'), ':');
		$subquery1 .= ' ELSE ';
		$subquery1 .= $cd_id.' END AS slug';
		
		$subquery2 = ' CASE WHEN ';
		$subquery2 .= $query->charLength('cc.alias');
		$subquery2 .= ' THEN ';
		$cc_id = $query->castAsChar('cc.id');
		$subquery2 .= $query->concatenate(array($cc_id, 'cc.alias'), ':');
		$subquery2 .= ' ELSE ';
		$subquery2 .= $cc_id.' END AS catslug';
				
		$subquery = $subquery1.','.$subquery2;
	
		$subqueryfield1 = self::_getSubQuery(self::$field1, '1');
		$subqueryfield2 = self::_getSubQuery(self::$field2, '2');
		$subqueryfield3 = self::_getSubQuery(self::$field3, '3');
		$subqueryfield4 = self::_getSubQuery(self::$field4, '4');
		$subqueryfield5 = self::_getSubQuery(self::$field5, '5');
		$subqueryfield6 = self::_getSubQuery(self::$field6, '6');
		$subqueryfield7 = self::_getSubQuery(self::$field7, '7');
		
		$subqueryfields = $subqueryfield1.$subqueryfield2.$subqueryfield3.$subqueryfield4.$subqueryfield5.$subqueryfield6.$subqueryfield7;
	
		$subquerylinkfield1 = self::_getSubQuery(self::$linkfield1, 'l1');
		$subquerylinkfield2 = self::_getSubQuery(self::$linkfield2, 'l2');
		$subquerylinkfield3 = self::_getSubQuery(self::$linkfield3, 'l3');
		$subquerylinkfield4 = self::_getSubQuery(self::$linkfield4, 'l4');
		$subquerylinkfield5 = self::_getSubQuery(self::$linkfield5, 'l5');
		
		$subquerylinkfields = $subquerylinkfield1.$subquerylinkfield2.$subquerylinkfield3.$subquerylinkfield4.$subquerylinkfield5;
	
		$query->select('cd.id, cd.catid, trim(cd.name) AS name, cc.lft as c_order, cd.user_id, cd.featured, cc.title AS category, cd.params, cd.image,'.$subquery.$subqueryfields.$subquerylinkfields);
	
		$query->from('#__contact_details AS cd');
		$query->join('INNER', '#__categories AS cc ON cd.catid = cc.id');		
		
		if (self::$selection == 'categories' && self::$categories !== null) {
			if (self::$categories != '') {
				$query->where('cd.catid IN ('.self::$categories.')');
			}
		} else if (self::$selection == 'contact' && !empty(self::$contact_id)) {
			$query->where('cd.id='.self::$contact_id);
		} else if (self::$selection == 'user' && $user->id > 0) {
			$query->where('cd.user_id='.$user->id);
		} else if (self::$selection != 'related') {
			return null;
		}
		
		if (!empty(self::$contact_ids_exclude)) {
			$query->where('cd.id NOT IN ('.self::$contact_ids_exclude.')');
		}
		
		if (!empty(self::$metakeys)) {
			$concat_string = $query->concatenate(array('","', ' REPLACE(cd.metakey, ", ", ",")', ' ","')); // remove single space after commas in keywords
			$query->where('('.$concat_string.' LIKE "%'.implode('%" OR '.$concat_string.' LIKE "%', self::$metakeys).'%")');
		}
		
		$query->where('cd.access IN ('.$groups.')');
		$query->where('cc.access IN ('.$groups.')');		
		
		$nullDate = $db->Quote($db->getNullDate());
		$nowDate = $db->Quote(JFactory::getDate()->toSql());
		$query->where('cd.published = 1');
		$query->where('(cd.publish_up = ' . $nullDate . ' OR cd.publish_up <= ' . $nowDate . ')');
		$query->where('(cd.publish_down = ' . $nullDate . ' OR cd.publish_down >= ' . $nowDate . ')');		
		
		if (!empty(self::$related_id)) {
			$query->where('cd.id <> '.self::$related_id);
		}
		
		if (self::$featured == 'o') {
			$query->where('cd.featured = 1');
		} else if (self::$featured == 'h') {
			$query->where('cd.featured = 0');
		} else if (self::$featured == 'sf') {
			$query->order("cd.featured DESC");
		}
		
		switch (self::$catorder) {
			case 'oa' : $query->order('cc.lft ASC'); break;
			case 'od' : $query->order('cc.lft DESC'); break;
			default : break;
		}
		
		switch (self::$order) {
			case 'oa' : $query->order('cd.ordering ASC'); break;
			case 'od' : $query->order('cd.ordering DESC'); break;
			case 'na' : $query->order('cd.name ASC'); break;
			case 'nd' : $query->order('cd.name DESC'); break;
			case 'fnf_fa' : $query->order('cd.ordering ASC'); break;
			case 'fnf_fd' : $query->order('cd.ordering DESC'); break;
			case 'fnf_la' : $query->order('cd.ordering ASC'); break;
			case 'fnf_ld' : $query->order('cd.ordering DESC'); break;
			case 'random' : $query->order('rand()'); break;
			case 'manual' : 
				if (!empty(self::$manual_order_ids)) {
					//$query->order('FIELD(cd.id, '.self::$manual_order_ids.')'); // MySQL specific
					
					$array_ids = explode (',', self::$manual_order_ids);
					$order = 'CASE cd.id';
					$i = 0;
					foreach ($array_ids as $id) {
						$order .= ' WHEN '.$id.' THEN '.$i++;
					}
					$order .= ' ELSE 999 END, cd.id';
					$query->order($order);
				} else {
					$query->order('cd.id ASC');
				}
				break;			
			case 'sna' : $query->order($db->escape('cd.sortname1').' ASC');
				$query->order($db->escape('cd.sortname2').' ASC');
				$query->order($db->escape('cd.sortname3').' ASC'); 
				break;
			case 'snd' : $query->order($db->escape('cd.sortname1').' DESC');
				$query->order($db->escape('cd.sortname2').' DESC');
				$query->order($db->escape('cd.sortname3').' DESC'); 
				break;
			default : $query->order('cd.ordering ASC'); break;
		}
	
		return $query;
	}
	
	protected static function _getSubQuery($field, $index)
	{
		$subquery = '';
	
		if ($field != "none") {
			$subquery .= ', ';
				
			switch ($field) {
				case 'empty' :
					$subquery .= "'empty' AS field".$index.", 'empty' AS fieldname".$index;
					break;					
					
				case 'gen' : // gender
					$subquery .= "'gender' AS field".$index.", 'gender' AS fieldname".$index;
					break;
				case 'dob' : // birthdate
					$subquery .= "'birthdate' AS field".$index.", 'birthdate' AS fieldname".$index;
					break;
				case 'age' : // age
					$subquery .= "'age' AS field".$index.", 'age' AS fieldname".$index;
					break;
				case 'com' : // company
					$subquery .= "'company' AS field".$index.", 'company' AS fieldname".$index;
					break;
				case 'dep' : // department
					$subquery .= "'department' AS field".$index.", 'department' AS fieldname".$index;
					break;
				case 'map' : // map
					$subquery .= "'map' AS field".$index.", 'map' AS fieldname".$index;
					break;
				case 'skype' : // skype
					$subquery .= "'skype' AS field".$index.", 'skype' AS fieldname".$index;
					break;
				case 'facebook' : // facebook
					$subquery .= "'facebook' AS field".$index.", 'facebook' AS fieldname".$index;
					break;
				case 'twitter' : // twitter
					$subquery .= "'twitter' AS field".$index.", 'twitter' AS fieldname".$index;
					break;
				case 'linkedin' : // linkedin
					$subquery .= "'linkedin' AS field".$index.", 'linkedin' AS fieldname".$index;
					break;
				case 'googleplus' : // googleplus
					$subquery .= "'googleplus' AS field".$index.", 'googleplus' AS fieldname".$index;
					break;
				case 'youtube' : // youtube
					$subquery .= "'youtube' AS field".$index.", 'youtube' AS fieldname".$index;
					break;
				case 'instagram' : // instagram
					$subquery .= "'instagram' AS field".$index.", 'instagram' AS fieldname".$index;
					break;
				case 'pinterest' : // pinterest
					$subquery .= "'pinterest' AS field".$index.", 'pinterest' AS fieldname".$index;
					break;
					
				case 'c_p' : // con_position
					$subquery .= "trim(cd.con_position) AS field".$index.", 'con_position' AS fieldname".$index;
					break;
				case 'tel' : // telephone
					$subquery .= "trim(cd.telephone) AS field".$index.", 'telephone' AS fieldname".$index;
					break;
				case 'mob' : // mobile
					$subquery .= "trim(cd.mobile) AS field".$index.", 'mobile' AS fieldname".$index;
					break;
				case 'mail' : // email_to
					$subquery .= "trim(cd.email_to) AS field".$index.", 'email_to' AS fieldname".$index;
					break;
				case 'web' : // webpage
					$subquery .= "trim(cd.webpage) AS field".$index.", 'webpage' AS fieldname".$index;
					break;
				case 'add' : // address
					$subquery .= "trim(cd.address) AS field".$index.", 'address' AS fieldname".$index;
					break;
				case 'sub' : // suburb
					$subquery .= "trim(cd.suburb) AS field".$index.", 'suburb' AS fieldname".$index;
					break;
				case 'st' : // state
					$subquery .= "trim(cd.state) AS field".$index.", 'state' AS fieldname".$index;
					break;
				case 'p_c' : // postcode
					$subquery .= "trim(cd.postcode) AS field".$index.", 'postcode' AS fieldname".$index;
					break;
				case 'cou' : // country
					$subquery .= "trim(cd.country) AS field".$index.", 'country' AS fieldname".$index;
					break;					
				case 'a' : // links a..e
					$subquery .= "'linka' AS field".$index.", 'linka' AS fieldname".$index;
					break;
				case 'b' :
					$subquery .= "'linkb' AS field".$index.", 'linkb' AS fieldname".$index;
					break;
				case 'c' :
					$subquery .= "'linkc' AS field".$index.", 'linkc' AS fieldname".$index;
					break;
				case 'd' :
					$subquery .= "'linkd' AS field".$index.", 'linkd' AS fieldname".$index;
					break;
				case 'e' :
					$subquery .= "'linke' AS field".$index.", 'linke' AS fieldname".$index;
					break;					
				case 'a_sw' : // links a..e same window
					$subquery .= "'linka' AS field".$index.", 'linka_sw' AS fieldname".$index;
					break;
				case 'b_sw' :
					$subquery .= "'linkb' AS field".$index.", 'linkb_sw' AS fieldname".$index;
					break;
				case 'c_sw' :
					$subquery .= "'linkc' AS field".$index.", 'linkc_sw' AS fieldname".$index;
					break;
				case 'd_sw' :
					$subquery .= "'linkd' AS field".$index.", 'linkd_sw' AS fieldname".$index;
					break;
				case 'e_sw' :
					$subquery .= "'linke' AS field".$index.", 'linke_sw' AS fieldname".$index;
					break;					
				case 'f_a' : // formatted address
					$subqueryformattedaddress = '';
					switch (self::$address_format) {
						case 'ssz' :
							$subqueryformattedaddress = "CONCAT(trim(cd.suburb), ', ', trim(cd.state), ' ', trim(cd.postcode))";
							break;
						case 'zss' :
							$subqueryformattedaddress = "CONCAT(trim(cd.postcode), ' ', trim(cd.suburb), ', ', trim(cd.state))";
							break;
						case 'zs' :
							$subqueryformattedaddress = "CONCAT(trim(cd.postcode), ' ', trim(cd.suburb))";
							break;
						case 'sz' :
							$subqueryformattedaddress = "CONCAT(trim(cd.suburb), ' ', trim(cd.postcode))";
							break;
						case 'ss' :
							$subqueryformattedaddress = "CONCAT(trim(cd.suburb), ', ', trim(cd.state))";
							break;
						default :
							$subqueryformattedaddress = '';
						break;
					}
					$subquery .= $subqueryformattedaddress." AS field".$index.", 'formattedaddress' AS fieldname".$index;
					break;				
				case 'f_f_a' : // fully formatted address
					$subqueryformattedaddress = '';
					switch (self::$address_format) {
						case 'ssz' :
							$subqueryformattedaddress = "CONCAT(trim(cd.address), '$', trim(cd.suburb), ', ', trim(cd.state), ' ', trim(cd.postcode))";
							break;
						case 'zss' :
							$subqueryformattedaddress = "CONCAT(trim(cd.address), '$', trim(cd.postcode), ' ', trim(cd.suburb), ', ', trim(cd.state))";
							break;
						case 'zs' :
							$subqueryformattedaddress = "CONCAT(trim(cd.address), '$', trim(cd.postcode), ' ', trim(cd.suburb))";
							break;
						case 'sz' :
							$subqueryformattedaddress = "CONCAT(trim(cd.address), '$', trim(cd.suburb), ' ', trim(cd.postcode))";
							break;
						case 'ss' :
							$subqueryformattedaddress = "CONCAT(trim(cd.address), '$', trim(cd.suburb), ', ', trim(cd.state))";
							break;
						default :
							$subqueryformattedaddress = '';
						break;
					}
					$subquery .= $subqueryformattedaddress." AS field".$index.", 'fullyformattedaddress' AS fieldname".$index;
					break;
				case 'misc' :					
					if (self::$text_type == 'info') { // take misc
						$subquery .= "trim(cd.".$field.") AS field".$index.", '".$field."' AS fieldname".$index;
					} else { // take metadescription
						$subquery .= "trim(cd.metadesc) AS field".$index.", '".$field."' AS fieldname".$index;
					}
					break;
				default :
					$subquery .= "trim(cd.".$field.") AS field".$index.", '".$field."' AS fieldname".$index;
					break;
			}
		}
	
		return $subquery;
	}
	
	/**
	 * Get the categories as a string for the sql query
	 */
	protected static function getCategories($categories_array, $get_sub_categories)
	{
		$categories = '';
		
		if (count($categories_array) > 0) {
			
			$array_of_category_values = array_count_values($categories_array);
			if (isset($array_of_category_values['all']) && $array_of_category_values['all'] > 0) { // 'all' was selected
				return $categories;
			}
			
			if ($get_sub_categories != 'no') {
				$categories_object = JCategories::getInstance('Contact'); // new JCategories('com_contact');
				foreach ($categories_array as $category) {
					$category_object = $categories_object->get($category); // if category unpublished, unset
					if (isset($category_object) && $category_object->hasChildren()) {
						if ($get_sub_categories == 'all') {
							$sub_categories_array = $category_object->getChildren(true); // true is for recursive
						} else {
							$sub_categories_array = $category_object->getChildren();
						}
						foreach ($sub_categories_array as $subcategory_object) {
							$categories_array[] = $subcategory_object->id;
						}
					}
			
				}
			
				$categories_array = array_unique($categories_array);
			}
			
			if (!empty($categories_array)) {
				//JArrayHelper::toInteger($categories_array); // TODO: really needed?
				$categories = implode(',', $categories_array);
			}
		} else {
			$categories = null;
		}
	
		return $categories;
	}
	
	public static function getFormattedName($name, $style, $uppercase)
	{		
		$formatted_name = $name;
				
		// case Olivier-Daniel Buisard Something Jr
		// firstpart -> Olivier-Daniel
		// remainingpart -> Buisard Something Jr
		$name_parts = explode(' ', $name);
		
		$firstpart = $name_parts[0];		
		if ($uppercase) {
			$firstpart = ucfirst($firstpart);
		}		
		
		unset($name_parts[0]);
		$remainingpart = '';
		if (count($name_parts) > 0) {
			if ($uppercase) {
				$remainingpart = ucwords(implode(' ', $name_parts)); // make sure there is no extra space when testing
			} else {
				$remainingpart = implode(' ', $name_parts);
			}
		}		
		
		// case Buisard Something Jr Olivier-Daniel
		// lastpart -> Olivier-Daniel
		// previouspart -> Buisard Something Jr
		$name_parts = explode(' ', $name);
		$name_parts_length = count($name_parts);
		
		$lastpart = $name_parts[$name_parts_length - 1];
		if ($uppercase) {
			$lastpart = ucfirst($lastpart);
		}
		
		unset($name_parts[$name_parts_length - 1]);
		$previouspart = '';
		if (count($name_parts) > 0) {
			if ($uppercase) {
				$previouspart = ucwords(implode(' ', $name_parts)); // make sure there is no extra space when testing
			} else {
				$previouspart = implode(' ', $name_parts);
			}
		}
		
		switch ($style) {
			case 'rsf' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $remainingpart." ".$formatted_name;
				}
				break;
			case 'fsr' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name." ".$remainingpart;
				}
				break;
			case 'rcf' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $remainingpart.", ".$formatted_name;
				}
				break;
			case 'fcr' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name.", ".$remainingpart;
				}
				break;				
			case 'psl' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $previouspart." ".$formatted_name;
				}
				break;
			case 'lsp' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name." ".$previouspart;
				}
				break;
			case 'pcl' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $previouspart.", ".$formatted_name;
				}
				break;
			case 'lcp' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name.", ".$previouspart;
				}
				break;				
			case 'rsfd' :
				$formatted_name = substr($firstpart, 0, 1).'.';
				if (!empty($remainingpart)) {
					$formatted_name = $remainingpart." ".$formatted_name;
				}
				break;
			case 'fdsr' :
				$formatted_name = substr($firstpart, 0, 1).'.';
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name." ".$remainingpart;
				}
				break;
			case 'psld' :
				$formatted_name = substr($lastpart, 0, 1).'.';
				if (!empty($previouspart)) {
					$formatted_name = $previouspart." ".$formatted_name;
				}
				break;
			case 'ldsp' :
				$formatted_name = substr($lastpart, 0, 1).'.';
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name." ".$previouspart;
				}
				break;
			case 'rdsf' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = substr($remainingpart, 0, 1).". ".$formatted_name;
				}
				break;
			case 'fsrd' :
				$formatted_name = $firstpart;
				if (!empty($remainingpart)) {
					$formatted_name = $formatted_name." ".substr($remainingpart, 0, 1).'.';
				}
				break;
			case 'pdsl' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = substr($previouspart, 0, 1).". ".$formatted_name;
				}
				break;
			case 'lspd' :
				$formatted_name = $lastpart;
				if (!empty($previouspart)) {
					$formatted_name = $formatted_name." ".substr($previouspart, 0, 1).'.';
				}
				break;				
			default : 
				if ($uppercase) {
					$formatted_name = ucwords($formatted_name);
				}
				break;
		}
		
		return $formatted_name;
	}
	
	public static function getFieldOutput($index, $field, $fieldname, $fieldaccess, $prefield, $fieldlabel, $params, $globalparams, $trombparams, $iconlinkonly = false) 
	{		
		$html = '';
		
		// restricted access
		
		$user = JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		if (!in_array($fieldaccess, $groups)) {
			return $html;
		}		
		
		jimport('joomla.environment.browser');
		$browser = JBrowser::getInstance();
		
		$value = '';
		$value_substitute = '';
		$value_is_link = false;
		$show_link = false;
		$target = '_blank';
		$label = '';
		$title = '';
		$class = '';
		$icon_class = '';
		$generated_link_tag = '';
		
		switch ($fieldname) {
			case 'empty' :	
				$class = 'empty';
				break;
				
			case 'name' :
				$value = $field; 
				$class = 'fieldname'; $icon_class = 'user';
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_NAME') : $fieldlabel;
				break;
				
			case 'name_link' :
				$value = $field;
				$value_is_link = true;
				$generated_link_tag = $field;
				$class = 'fieldname'; $icon_class = 'user';
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_NAME') : $fieldlabel;
				break;
				
			case 'con_position' :
				$value = $field;
				if (strpos($field, 'POSITION_') !== false) {
					$field_array = explode(',', $field);
					$field_array_fixed = array();
					$last_field = '';
					foreach ($field_array as $field_element) {
						$field_array_fixed[] = JText::_(trim($field_element));
					}
					$count = count($field_array);
					if ($count > 1) {
						$last_field = $field_array_fixed[$count - 1];
						unset($field_array_fixed[$count - 1]);
						$value = implode(', ', $field_array_fixed);
						$value .= JText::_('TROMBINOSCOPEEXTENDED_AND').' '.$last_field;
					} else {
						$value = JText::_(trim($field));
					}
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_POSITION') : $fieldlabel;
				$class = 'fieldposition'; $icon_class = 'briefcase';
				break;
				
			case 'telephone' : 
				$value = $field;
				if (!empty($value) && $browser->isMobile()) {
					$value = 'tel:'.$field;
					$value_is_link = true;
					$show_link = true;
					$value_substitute = $field;
					$title = $field;
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_TELEPHONE') : $fieldlabel;
				$class = 'fieldtel'; $icon_class = 'phone';
				break;
				
			case 'mobile' :	 
				$value = $field;
				if (!empty($value) && $browser->isMobile()) {
					$value = 'tel:'.$field;
					$value_is_link = true;
					$show_link = true;
					$value_substitute = $field;
					$title = $field;
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_MOBILE') : $fieldlabel;
				$class = 'fieldmobile'; $icon_class = 'mobile';
				break;
				
			case 'fax' :	
				$value = $field;
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_FAX') : $fieldlabel;
				$class = 'fieldfax'; $icon_class = 'newspaper';
				break;
				
			case 'email_to' :				
				if (!empty($field)) {
					$value_substitute = ($trombparams->get('email_substitut') == '') ? $field : $trombparams->get('email_substitut');					
				}
				switch ($trombparams->get('link_email')) {
					case 1: // mailto
						if (!empty($field)) {
							$value = 'mailto:'.$field;
							$title = $field;
							$value_is_link = true;
							$show_link = true;
							if ($trombparams->get('cloak_email') && !empty($field)) {							
								if ($trombparams->get('email_substitut') != '') {
									$generated_link_tag = JHtml::_('email.cloak', $field, true, $trombparams->get('email_substitut'), false);
								} else {
									$generated_link_tag = JHtml::_('email.cloak', $field);
								}
							}
						}
						break;
					case 2: // contact
						if (!empty($field)) {
							$value_is_link = true;
							$target = '_self';
							$show_link = true;
							$title = JText::_('MOD_TROMBINOSCOPE_LABEL_EMAIL');
							$value = JRoute::_(self::getContactRoute('contact', $params->get('id'), $params->get('catid')));	
						}
						break;						
					case 3: // te contact form
						if (!empty($field)) {
							$value_is_link = true;
							$target = '_self';
							$show_link = true;
							$title = JText::_('MOD_TROMBINOSCOPE_LABEL_EMAIL');
							
							// tests if the module is part of the component package or is just a standalone
							$standalone = false;
							jimport('joomla.filesystem.folder');
							$folder = JPATH_ROOT.'/components/com_trombinoscopeextended/views/trombinoscope';
							if (!JFolder::exists($folder)) {
								$standalone = true;
							}
								
							if (!$standalone) {
								$value = JRoute::_(self::getContactRoute('trombinoscopeextended', $params->get('id'), $params->get('catid')).'&form=te#teform');
							} else { // defaults to standard contact page
								$value = JRoute::_(self::getContactRoute('contact', $params->get('id'), $params->get('catid')));
							}
						}				
						break;				
					default: // no link
						//if ($trombparams->get("cloak_email"]) { // if cloak and no link, show the email address no matter what
							//$value = JHtml::_('email.cloak', $field);                                                                           // creates a link?
						//} else {
							if (!$iconlinkonly) {
								$value = $field;
							}
						//}
						break;
				} 
				
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_EMAIL') : $fieldlabel;
				$class = 'fieldemail'; $icon_class = 'mail';
				break;
				
			case 'webpage' : 
				$value_is_link = true;
				$show_link = true;
				if (!empty($field)) {
					//$value = (0 === strpos($field, 'http')) ? $field : 'http://'.$field; // unnecessary because saved with it
					$value = $field;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
						$value_substitute = ($trombparams->get('webpage_substitut') == '') ? $title : $trombparams->get('webpage_substitut');
					} else {
						$title = $value;
						$value_substitute = ($trombparams->get('webpage_substitut') == '') ? $value : $trombparams->get('webpage_substitut');
					}
				}				
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_WEBPAGE') : $fieldlabel;
				$class = 'fieldwebpage'; $icon_class = 'earth';
				break;
				
			case 'address' : 
				$value = trim($field, "$, \t\n\r\0\x0B"); // single quotes won't work
				$value = str_replace('$', "\n", $value);
				$title = $value;
				if (!empty($value)) {
					if ($trombparams->get('link_address_with_map')) {
						$map_field = trim($params->get('te_map', ''));
						if ($map_field) {
							if (substr($map_field, 0, 4) != "http") {
								$mapvalue = 'https://'.$map_field;
							} else {
								$mapvalue = $map_field;
							}
							$value_is_link = true;
							$generated_link_tag = '<address><a class="fieldvalue" href="'.$mapvalue.'" target="_blank" title="'.$title.'">'.nl2br($value).'</a></address>';
						} else {
							$value = '<address>'.nl2br($value).'</address>';
						}
					} else {
						$value = '<address>'.nl2br($value).'</address>';
					}
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_ADDRESS') : $fieldlabel;
				$class = 'fieldaddress'; $icon_class = 'home';
				break;
				
			case 'fullyformattedaddress' : // address + zipcode... formatted
				$value = trim($field, "$, \t\n\r\0\x0B"); // single quotes won't work
				$value = str_replace('$', "\n", $value);
				$title = $value;
				if (!empty($value)) {
					if ($trombparams->get('link_address_with_map') == 1) { // auto
						$mapvalue = self::getAutoMapLink($value, $trombparams->get('auto_map_params'));
						$value_is_link = true;
						$generated_link_tag = '<address><a class="fieldvalue" href="'.$mapvalue.'" target="_blank" title="'.$title.'">'.nl2br($value).'</a></address>';
					} else if ($trombparams->get('link_address_with_map') == 2) { // field
						$map_field = trim($params->get('te_map', ''));
						if ($map_field) {
							if (substr($map_field, 0, 4) != "http") {
								$mapvalue = 'https://'.$map_field;
							} else {
								$mapvalue = $map_field;
							}
							$value_is_link = true;
							$generated_link_tag = '<address><a class="fieldvalue" href="'.$mapvalue.'" target="_blank" title="'.$title.'">'.nl2br($value).'</a></address>';
						} else {
							$value = '<address>'.nl2br($value).'</address>';
						}
					} else if ($trombparams->get('link_address_with_map') == 3) { // field first
						$mapvalue = self::getAutoMapLink($value, $trombparams->get('auto_map_params'));
						$map_field = trim($params->get('te_map', ''));
						if ($map_field) {
							if (substr($map_field, 0, 4) != "http") {
								$mapvalue = 'https://'.$map_field;
							} else {
								$mapvalue = $map_field;
							}
						}					
						$value_is_link = true;
						$generated_link_tag = '<address><a class="fieldvalue" href="'.$mapvalue.'" target="_blank" title="'.$title.'">'.nl2br($value).'</a></address>';
					} else {
						$value = '<address>'.nl2br($value).'</address>';
					}
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_FORMATTEDADDRESS') : $fieldlabel;
				$class = 'fieldformattedaddress'; $icon_class = 'home';
				break;
				
			case 'suburb' : 
				$value = $field;
				$class = 'fieldsuburb';
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_SUBURB') : $fieldlabel;
				break;

			case 'state' : 
				$value = $field;
				$class = 'fieldstate';
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_STATE') : $fieldlabel;
				break;
				
			case 'postcode' : 
				$value = $field; 
				$class = 'fieldpostcode';
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_POSTCODE') : $fieldlabel;
				break;
				
			case 'country' : 
				$value = $field; 				
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_COUNTRY') : $fieldlabel;
				$class = 'fieldcountry'; $icon_class = 'flag2';
				break;
				
			case 'misc' : 
				$temp = '';
				if (trim($trombparams->get('letter_count')) == '') { // take everything
					if ($trombparams->get('strip_tags')) {
						$temp = strip_tags($field);
					} else {
						if (trim($trombparams->get('keep_tags')) == '') {
							$temp = $field;
						} else {
							$temp = strip_tags($field, $trombparams->get('keep_tags'));
						}
					}
					$temp = self::stripPluginTags($temp);
				} else if ((int)$trombparams->get('letter_count') > 0) {
					$temp = strip_tags($field);
				
					// need to strip plugins before cutting off sentence...
					$temp = self::stripPluginTags($temp);
						
					$lenTemp = strlen($temp);
					if ($lenTemp > (int)$trombparams->get('letter_count')) {
						$temp = mb_substr($temp, 0, (int)$trombparams->get('letter_count'));
						$temp .= '...';
					}
				}
				$value = $temp;			
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_MISC') : $fieldlabel;
				$title = $label;
				$class = 'fieldmisc'; $icon_class = 'info';
				break;
				
			case 'linka' :
				$value = $params->get('linka', '');		 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? self::getLabelForLink('linka', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinka'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linkb' :
				$value = $params->get('linkb', '');		 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? self::getLabelForLink('linkb', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinkb'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linkc' :
				$value = $params->get('linkc', '');		 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? self::getLabelForLink('linkc', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinkc'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linkd' :
				$value = $params->get('linkd', '');		 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? self::getLabelForLink('linkd', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinkd'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linke' :
				$value = $params->get('linke', '');		 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? self::getLabelForLink('linke', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinke'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linka_sw' : 
				$value = $params->get('linka', ''); 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$target = '_self';
				$label = empty($fieldlabel) ? self::getLabelForLink('linka', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinka'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linkb_sw' : 
				$value = $params->get('linkb', ''); 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$target = '_self';
				$label = empty($fieldlabel) ? self::getLabelForLink('linkb', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinkb'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linkc_sw' : 
				$value = $params->get('linkc', ''); 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$target = '_self';
				$label = empty($fieldlabel) ? self::getLabelForLink('linkc', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinkc'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linkd_sw' : 
				$value = $params->get('linkd', ''); 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$target = '_self';
				$label = empty($fieldlabel) ? self::getLabelForLink('linkd', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinkd'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'linke_sw' : 
				$value = $params->get('linke', ''); 
				if (!empty($value)) {
					//$value = (0 === strpos($value, 'http')) ? $value : 'http://'.$value;
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$target = '_self';
				$label = empty($fieldlabel) ? self::getLabelForLink('linke', $value, $params, $globalparams) : $fieldlabel;
				$class = 'fieldlinke'; $icon_class = self::getIconForLink($value);
				break;
				
			case 'gender' : // gender
				$additional_field = $params->get('te_gender', '');
				if ($additional_field) {
					if ($additional_field == 'm') {
						$value = JText::_('MOD_TROMBINOSCOPE_VALUE_MALE');
					} else if ($additional_field == 'f') {
						$value = JText::_('MOD_TROMBINOSCOPE_VALUE_FEMALE');
					} else if ($value == 'c') {
						$value = JText::_('MOD_TROMBINOSCOPE_VALUE_THIRDGENDER');
					}
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_GENDER') : $fieldlabel;
				$class = 'fieldgender'; $icon_class = 'users';
				break;
				
			case 'birthdate':
				$additional_field = $params->get('te_birthdate', '');
				if ($additional_field) {
					$value = JHtml::_('date', $additional_field, $trombparams->get('birthdate_format'));
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_BIRTHDATE') : $fieldlabel;
				$class = 'fieldbirthdate'; $icon_class = 'gift';
				break;
				
			case 'age':
				$additional_field = $params->get('te_birthdate', '');
				if ($additional_field) {
					$value = date_create($additional_field)->diff(date_create('today'))->y;
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_AGE') : $fieldlabel;
				$class = 'fieldage'; $icon_class = 'gift';
				break;
				
			case 'company':
				$additional_field = trim($params->get('te_company', ''));
				if ($additional_field) {
					$value = $additional_field;
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_COMPANY') : $fieldlabel;
				$class = 'fieldcompany'; $icon_class = 'office';
				break;
				
			case 'department':
				$additional_field = trim($params->get('te_department', ''));
				if ($additional_field) {
					$value = $additional_field;
				}
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_DEPARTMENT') : $fieldlabel;
				$class = 'fielddepartment'; $icon_class = 'tree';
				break;
				
			case 'map':
				$additional_field = trim($params->get('te_map', ''));
				if ($additional_field) {
					if (substr($additional_field, 0, 4) != "http") {
						$value = 'https://'.$additional_field;
					} else {
						$value = $additional_field;
					}
					if (!$trombparams->get('protocol')) {
						$title = self::remove_protocol($value);
					}
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? JText::_('MOD_TROMBINOSCOPE_LABEL_MAP') : $fieldlabel;
				$class = 'fieldmap'; $icon_class = 'location';
				break;
				
			case 'skype':
				$additional_field = trim($params->get('te_skype', ''));
				if ($additional_field) {
					$value = $additional_field;
				}
				$label = empty($fieldlabel) ? 'Skype' : $fieldlabel;
				$class = 'fieldskype'; $icon_class = 'skype';
				break;
				
			case 'facebook':
				$additional_field = trim($params->get('te_facebook', ''));
				if ($additional_field) {
					$value = 'https://www.facebook.com/'.$additional_field;
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? 'Facebook' : $fieldlabel;
				$class = 'fieldfacebook'; $icon_class = 'facebook';
				break;
				
			case 'twitter':
				$additional_field = trim($params->get('te_twitter', ''));
				if ($additional_field) {
					$value = 'https://twitter.com/'.$additional_field;
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? 'Twitter' : $fieldlabel;
				$class = 'fieldtwitter'; $icon_class = 'twitter';
				break;
				
			case 'linkedin':
				$additional_field = trim($params->get('te_linkedin', ''));
				if ($additional_field) {
					$value = 'https://www.linkedin.com/in/'.$additional_field;
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? 'LinkedIn' : $fieldlabel;
				$class = 'fieldlinkedin'; $icon_class = 'linkedin';
				break;
				
			case 'googleplus':
				$additional_field = trim($params->get('te_googleplus', ''));
				if ($additional_field) {
					$value = 'https://plus.google.com/+'.$additional_field.'/posts';
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? 'Google+' : $fieldlabel;
				$class = 'fieldgoogleplus'; $icon_class = 'googleplus';
				break;
				
			case 'youtube':
				$additional_field = trim($params->get('te_youtube', ''));
				if ($additional_field) {
					$value = 'https://www.youtube.com/user/'.$additional_field;
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? 'YouTube' : $fieldlabel;
				$class = 'fieldyoutube'; $icon_class = 'youtube';
				break;
				
			case 'instagram':
				$additional_field = trim($params->get('te_instagram', ''));
				if ($additional_field) {
					$value = 'http://instagram.com/'.$additional_field;
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? 'Instagram' : $fieldlabel;
				$class = 'fieldinstagram'; $icon_class = 'instagram';
				break;
				
			case 'pinterest':
				$additional_field = trim($params->get('te_pinterest', ''));
				if ($additional_field) {
					$value = 'http://www.pinterest.com/'.$additional_field;
				}
				$value_is_link = true;
				$label = empty($fieldlabel) ? 'Pinterest' : $fieldlabel;
				$class = 'fieldpinterest'; $icon_class = 'pinterest';
				break;
		}
		
		// html code building
		
		if ($iconlinkonly) {
			if (!empty($value)) {
				$html .= '<li class="iconlink index'.$index;
				if (!empty($class)) {
					$html .= ' '.$class;
				}
				$html .= '">';
				if (!empty($value_substitute)) {
					$html .= '<a class="fieldvalue" href="'.$value.'" target="'.$target.'" title="'.$value_substitute.'"><i class="icon SYWicon-'.$icon_class.'"></i><span>'.$value_substitute.'</span></a>';
				} else {
					$html .= '<a class="fieldvalue" href="'.$value.'" target="'.$target.'" title="'.$label.'"><i class="icon SYWicon-'.$icon_class.'"></i><span>'.$value.'</span></a>';
				}
				$html .= '</li>';
			}
		} else {
			if (!$trombparams->get('keep_space') && empty($value) && $class != 'empty') {
				return $html;
			} else {				
				$html .= '<div class="personfield index'.$index;
				if (!empty($class)) {
					$html .= ' '.$class;
				}
				$html .= '">';
				if (!empty($value)) {
					if ($prefield == 1) { // labels	
						$html .= '<span class="fieldlabel">'.$label.$trombparams->get('label_separator').'</span>';
					} else if ($prefield == 2) { // icons	
						if (!empty($icon_class)) {
							$html .= '<i class="icon SYWicon-'.$icon_class.'"></i>';
						} else {
							$html .= '<i class="noicon"></i>';
						}
					} else { // no icon or no label for the field
						if ($trombparams->get('all_pre') == 1 && $class != 'fieldname') { // force 'no label' even if there is one
							$html .= '<span class="fieldlabel"></span>';
						} else if ($trombparams->get('all_pre') == 2 && $class != 'fieldname') { // force 'no icon' even if one exists for the field
							$html .= '<i class="noicon"></i>';
						}
					}
								
					if ($value_is_link) {
						if (!empty($generated_link_tag)) {
							$html .= $generated_link_tag;
						} else {
							if ($show_link) {
								$label_as_title = empty($title) ? $label : $title;
								if (!empty($value_substitute)) {
									$html .= '<a class="fieldvalue" href="'.$value.'" target="'.$target.'" title="'.$label_as_title.'"><span>'.$value_substitute.'</span></a>';
								} else {
									$html .= '<a class="fieldvalue" href="'.$value.'" target="'.$target.'" title="'.$label_as_title.'"><span>'.$value.'</span></a>';
								}
							} else {
								$value_as_title = empty($title) ? $value : $title;
								$html .= '<a class="fieldvalue" href="'.$value.'" target="'.$target.'" title="'.$value_as_title.'"><span>'.$label.'</span></a>';
							}
						}
					} else {
						$value_as_title = empty($title) ? $value : $title;
						if (!empty($value_substitute)) {
							$html .= '<span class="fieldvalue" title="'.$value_as_title.'">'.$value_substitute.'</span>';
						} else {
							$html .= '<span class="fieldvalue" title="'.$value_as_title.'">'.$value.'</span>';
						}
					}
				} else {
					$html .= '<span>&nbsp;</span>';
				}
					
				$html .= '</div>';
			}
		}
		
		return $html;
	}
	
	public static function getLabelForLink($field, $link, $params, $globalparams) {
		
		$label = $params->get($field.'_name');
		
		$label = ($label) ? $label : $globalparams->get($field.'_name');
		
		if (!empty($label)) {
			return $label;
		}		
			
		if (strpos($link, 'facebook') > 0) {
			return 'Facebook';
		}
		
		if (strpos($link, 'linkedin') > 0) {
			return 'LinkedIn';
		}
		
		if (strpos($link, 'twitter') > 0) {
			return 'Twitter';
		}
		
		if (strpos($link, 'plus.google') > 0) {
			return 'Google+';
		}
		
		if (strpos($link, 'instagram') > 0) {
			return 'Instagram';
		}
		
		if (strpos($link, 'tumblr') > 0) {
			return 'Tumblr';
		}
		
		if (strpos($link, 'pinterest') > 0) {
			return 'Pinterest';
		}
		
		if (strpos($link, 'youtube') > 0) {
			return 'YouTube';
		}
		
		if (strpos($link, 'vimeo') > 0) {
			return 'Vimeo';
		}
		
		if (strpos($link, 'wordpress') > 0) {
			return 'Wordpress';
		}
		
		if (strpos($link, 'skype') > 0) {
			return 'Skype';
		}
		
		if (strpos($link, 'blogspot') > 0) {
			return 'Blogger';
		}
		
		return JText::_('MOD_TROMBINOSCOPE_LABEL_LINK');
	}
	
	public static function getIconForLink($link)
	{
		if (strpos($link, 'facebook') > 0) {
			return 'facebook';
		}
	
		if (strpos($link, 'linkedin') > 0) {
			return 'linkedin';
		}
	
		if (strpos($link, 'twitter') > 0) {
			return 'twitter';
		}
	
		if (strpos($link, 'plus.google') > 0) {
			return 'googleplus';
		}
	
		if (strpos($link, 'instagram') > 0) {
			return 'instagram';
		}
	
		if (strpos($link, 'tumblr') > 0) {
			return 'tumblr';
		}
	
		if (strpos($link, 'pinterest') > 0) {
			return 'pinterest';
		}
	
		if (strpos($link, 'youtube') > 0) {
			return 'youtube';
		}
	
		if (strpos($link, 'vimeo') > 0) {
			return 'vimeo';
		}
	
		if (strpos($link, 'wordpress') > 0) {
			return 'wordpress';
		}
	
		if (strpos($link, 'skype') > 0) {
			return 'skype';
		}
	
		if (strpos($link, 'blogspot') > 0) {
			return 'blogger';
		}
	
		return 'earth';
	}
	
	static function stripPluginTags($output) {
			
		$plugins = array();
		
		preg_match_all('/\{\w*/', $output, $matches);
		foreach ($matches[0] as $match) {
			$match = str_replace('{', '', $match);
			if (strlen($match)) {
				$plugins[$match] = $match;
			}
		}
			
		$find = array();
		foreach ($plugins as $plugin) {
			$find[] = '\{'.$plugin.'\s?.*?\}.*?\{/'.$plugin.'\}';
			$find[] = '\{'.$plugin.'\s?.*?\}';
		}
		if(!empty($find)) {
			foreach($find as $key=>$f) {
				$f = '/'.str_replace('/','\/',$f).'/';
				$find[$key] = $f;
			}
			$output = preg_replace($find ,'', $output);
		}
		
		return $output;
	}
	
	/**
	* Create the contact link
	*/
	public static function getContactRoute($component, $id, $catid)
	{
		$needles = array(
			'contact'  => array((int) $id)
		);

		$link = 'index.php?option=com_'.$component.'&view=contact&te_referer=module&id='. $id;
		
		if ($catid > 1) {
			$categories = JCategories::getInstance('Contact');
			$category = $categories->get($catid);
			if ($category) {
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}
		
		if ($item = self::_findItem($component, $needles)) {
			$link .= '&Itemid='.$item;
		//} elseif ($item = self::_findItem($component)) {
			//$link .= '&Itemid='.$item;
		//} else {
			//$link .= '&Itemid=0';
		}
	
		return $link;
	}
	
	/**
	* Create the category link
	*/
	public static function getCategoryRoute($catid)
	{
		if ($catid instanceof JCategoryNode) {
			$id = $catid->id;
			$category = $catid;
		} else {
			$id = (int) $catid;
			$category = JCategories::getInstance('Contact')->get($id);
		}
	
		if ($id < 1 || !($category instanceof JCategoryNode)) {
			$link = '';
		} else {
			$needles = array();

			//if ($item = self::_findItem('contact', $needles)) {
				//$link = 'index.php?Itemid='.$item;
			//} else {
				$link = 'index.php?option=com_contact&view=category&id='.$id;
			
				//if($category) {
					$catids = array_reverse($category->getPath());
					$needles['category'] = $catids;
					$needles['categories'] = $catids;
					
					if ($item = self::_findItem('contact', $needles)) {
						$link .= '&Itemid='.$item;
					//} elseif ($item = self::_findItem('contact')) {
						//$link .= '&Itemid='.$item;
					//} else {
						//$link .= '&Itemid=0';
					}
				//}
			//}
		}
	
		return $link;
	}
	
	protected static function _findItem($component, $needles = null)
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu('site');
	
		// Prepare the reverse lookup array.
		if (!isset(self::$lookup)) {
			self::$lookup = array();
	
			$thecomponent = JComponentHelper::getComponent('com_'.$component);
			$attributes = array('component_id');
			$values = array($thecomponent->id);
			
			$items = $menus->getItems($attributes, $values);
			
			if ($items != null) {			
				foreach ($items as $item) {
					if (isset($item->query) && isset($item->query['view'])) {
						$view = $item->query['view'];
						if (!isset(self::$lookup[$view])) {
							self::$lookup[$view] = array();
						}
						if (isset($item->query['id'])) {
							self::$lookup[$view][$item->query['id']] = $item->id;
						}
					}
				}
			}
		}
	
		if ($needles) {
			foreach ($needles as $view => $ids) {
				if (isset(self::$lookup[$view])) {
					foreach($ids as $id) {
						if (isset(self::$lookup[$view][(int)$id])) {
							return self::$lookup[$view][(int)$id];
						}
					}
				}
			}
		}
	
		return null;
	}
	
	/**
	* Create the cropped image
	*/
	public static function getCroppedImage($module_id, $id, $imagesrc, $tmp_path, $clear_cache, $head_width, $head_height, $crop_picture, $filter)
	{	
		$extensions = get_loaded_extensions();
		if (!in_array('gd', $extensions)) {
			return $imagesrc;
		} else {
			$imageext = explode('.', $imagesrc);
			$imageext = $imageext[count($imageext) - 1];
			$imageext = strtolower($imageext);
				
			$filename = $tmp_path.'/contact_thumb_'.$module_id.'_'.$id.'.'.$imageext;
			if (is_file($filename) && !$clear_cache) {
				// thumbnail already exists
			} else { // create the thumbnail
				
				$image = new SYWImage($imagesrc);
				
				if (is_null($image->getImagePath())) {
					return 'error';
				} else if (is_null($image->getImageMimeType())) {
					return 'error';
				} else if (is_null($image->getImage()) || $image->getImageWidth() == 0) {
					return 'error';
				} else {
									
					switch ($imageext){
						case 'jpg': case 'jpeg': $quality = 100; break; // 0 to 100
						case 'png': $quality = 0; break; // compression: 0 to 9
						default : $quality = -1; break;
					}
					
					switch ($filter) {
						case 'grayscale': $filter = IMG_FILTER_GRAYSCALE; break;
						case 'sketch': $filter = IMG_FILTER_MEAN_REMOVAL; break;
						case 'negate': $filter = IMG_FILTER_NEGATE; break;
						case 'emboss': $filter = IMG_FILTER_EMBOSS; break;
						case 'edgedetect': $filter = IMG_FILTER_EDGEDETECT; break;
						default: $filter = null; break;
					}
					
					$creation_success = $image->createThumbnail($head_width, $head_height, $crop_picture, $quality, $filter, $filename);
					if (!$creation_success) {
						return 'error';
					}
				} 
			}
			return $filename;
		}
	}
	
	protected static function remove_protocol($url) 
	{
		$disallowed = array('http://', 'https://');
		foreach($disallowed as $d) {
			if(strpos($url, $d) === 0) {
				return str_replace($d, '', $url);
			}
		}
		return $url;
	}
	
	static function getAutoMapLink($address, $params = '', $embed = false) {
	
		$address_array = explode("\n", $address);
		$address = '';
		foreach ($address_array as $address_line) {
			$address_line = str_replace(',', ' ', $address_line);
			$address_line = str_replace('#', ' ', $address_line);
			$address .= trim($address_line, ", \t\n\r\0\x0B").' ';
		}
	
		$address = trim($address, ", \t\n\r\0\x0B");
		
		$address = preg_replace('/\s+/', ' ', $address); // to replace multiple occurences of white space into one
	
		$url = 'https://maps.google.com/maps?q='.urlencode($address);
		
		if (!empty($params)) {
			$url .= '&'.$params;
		}
		
		if ($embed) {
			$url .= '&output=embed';
		}
	
		return $url;	
	}
	
	/**
	 * get file's content
	 */
	public static function getFileContent($file) 
	{
		$content = '';
		
		if (function_exists('curl_version')) {
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $file);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($curl);
			curl_close($curl);
			if (!$content) {
				return false;
			}			
		} else if (ini_get('allow_url_fopen')) {
			$content = file_get_contents($file);
			if ($content === false) {
				return false;
			}
		} else {
			return false;
		}
		
		return $content;
	}
	
	protected static function compare($a, $b) {
		if (class_exists('Collator') && self::$sort_locale !== 'en_US' && self::$sort_locale !== 'en_GB') { // needs php_intl
			$collator = new Collator(self::$sort_locale);
			return $collator->compare($a, $b);
		} else {
			return ($a < $b) ? -1 : 1;
		}
	}
	
	/* sort by category ASC lastpart ASC firstpart ASC */
	protected static function sortCascLascFasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->lastpart < $b->lastpart) ? -1 : 1;
			return self::compare($a->lastpart, $b->lastpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category ASC by lastpart DESC firstpart DESC */
	protected static function sortCascLdescFdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->lastpart > $b->lastpart) ? -1 : 1;
			return self::compare($b->lastpart, $a->lastpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category ASC by firstpart ASC lastpart ASC */
	protected static function sortCascFascLasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart < $b->lastpart) ? -1 : 1;
				return self::compare($a->lastpart, $b->lastpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category ASC by firstpart DESC lastpart DESC */
	protected static function sortCascFdescLdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart > $b->lastpart) ? -1 : 1;
				return self::compare($b->lastpart, $a->lastpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category ASC by secondpart ASC firstpart ASC */
	protected static function sortCascSascFasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->secondpart < $b->secondpart) ? -1 : 1;
			return self::compare($a->secondpart, $b->secondpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category ASC by secondpart DESC firstpart DESC */
	protected static function sortCascSdescFdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->secondpart > $b->secondpart) ? -1 : 1;
			return self::compare($b->secondpart, $a->secondpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category ASC by firstpart ASC secondpart ASC */
	protected static function sortCascFascSasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart < $b->secondpart) ? -1 : 1;
				return self::compare($a->secondpart, $b->secondpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category ASC by firstpart DESC secondpart DESC */
	protected static function sortCascFdescSdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart > $b->secondpart) ? -1 : 1;
				return self::compare($b->secondpart, $a->secondpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order < $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC lastpart ASC firstpart ASC */
	protected static function sortCdescLascFasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->lastpart < $b->lastpart) ? -1 : 1;
			return self::compare($a->lastpart, $b->lastpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC by lastpart DESC firstpart DESC */
	protected static function sortCdescLdescFdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->lastpart == $b->lastpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->lastpart > $b->lastpart) ? -1 : 1;
			return self::compare($b->lastpart, $a->lastpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC by firstpart ASC lastpart ASC */
	protected static function sortCdescFascLasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart < $b->lastpart) ? -1 : 1;
				return self::compare($a->lastpart, $b->lastpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC by firstpart DESC lastpart DESC */
	protected static function sortCdescFdescLdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->lastpart == $b->lastpart) {
					return 0;
				}
				//return ($a->lastpart > $b->lastpart) ? -1 : 1;
				return self::compare($b->lastpart, $a->lastpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC by secondpart ASC firstpart ASC */
	protected static function sortCdescSascFasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart < $b->firstpart) ? -1 : 1;
				return self::compare($a->firstpart, $b->firstpart);
			}
			//return ($a->secondpart < $b->secondpart) ? -1 : 1;
			return self::compare($a->secondpart, $b->secondpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC by secondpart DESC firstpart DESC */
	protected static function sortCdescSdescFdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->secondpart == $b->secondpart) {
				if ($a->firstpart == $b->firstpart) {
					return 0;
				}
				//return ($a->firstpart > $b->firstpart) ? -1 : 1;
				return self::compare($b->firstpart, $a->firstpart);
			}
			//return ($a->secondpart > $b->secondpart) ? -1 : 1;
			return self::compare($b->secondpart, $a->secondpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC by firstpart ASC secondpart ASC */
	protected static function sortCdescFascSasc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart < $b->secondpart) ? -1 : 1;
				return self::compare($a->secondpart, $b->secondpart);
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by category DESC by firstpart DESC secondpart DESC */
	protected static function sortCdescFdescSdesc($a, $b) {
	
		if ($a->c_order == $b->c_order) {
			if ($a->firstpart == $b->firstpart) {
				if ($a->secondpart == $b->secondpart) {
					return 0;
				}
				//return ($a->secondpart > $b->secondpart) ? -1 : 1;
				return self::compare($b->secondpart, $a->secondpart);
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		return ($a->c_order > $b->c_order) ? -1 : 1;
	}
	
	/* sort by lastpart ASC firstpart ASC */
	protected static function sortLascFasc($a, $b) {
	
		if ($a->lastpart == $b->lastpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		//return ($a->lastpart < $b->lastpart) ? -1 : 1;
		return self::compare($a->lastpart, $b->lastpart);
	}
	
	/* sort by lastpart DESC firstpart DESC */
	protected static function sortLdescFdesc($a, $b) {
	
		if ($a->lastpart == $b->lastpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		//return ($a->lastpart > $b->lastpart) ? -1 : 1;
		return self::compare($b->lastpart, $a->lastpart);
	}
	
	/* sort by firstpart ASC lastpart ASC */
	protected static function sortFascLasc($a, $b) {
	
		if ($a->firstpart == $b->firstpart) {
			if ($a->lastpart == $b->lastpart) {
				return 0;
			}
			//return ($a->lastpart < $b->lastpart) ? -1 : 1;
			return self::compare($a->lastpart, $b->lastpart);
		}
		//return ($a->firstpart < $b->firstpart) ? -1 : 1;
		return self::compare($a->firstpart, $b->firstpart);
	}
	
	/* sort by firstpart DESC lastpart DESC */
	protected static function sortFdescLdesc($a, $b) {
	
		if ($a->firstpart == $b->firstpart) {
			if ($a->lastpart == $b->lastpart) {
				return 0;
			}
			//return ($a->lastpart > $b->lastpart) ? -1 : 1;
			return self::compare($b->lastpart, $a->lastpart);
		}
		//return ($a->firstpart > $b->firstpart) ? -1 : 1;
		return self::compare($b->firstpart, $a->firstpart);
	}
	
	/* sort by secondpart ASC firstpart ASC */
	protected static function sortSascFasc($a, $b) {
	
		if ($a->secondpart == $b->secondpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart < $b->firstpart) ? -1 : 1;
			return self::compare($a->firstpart, $b->firstpart);
		}
		//return ($a->secondpart < $b->secondpart) ? -1 : 1;
		return self::compare($a->secondpart, $b->secondpart);
	}
	
	/* sort by secondpart DESC firstpart DESC */
	protected static function sortSdescFdesc($a, $b) {
	
		if ($a->secondpart == $b->secondpart) {
			if ($a->firstpart == $b->firstpart) {
				return 0;
			}
			//return ($a->firstpart > $b->firstpart) ? -1 : 1;
			return self::compare($b->firstpart, $a->firstpart);
		}
		//return ($a->secondpart > $b->secondpart) ? -1 : 1;
		return self::compare($b->secondpart, $a->secondpart);
	}
	
	/* sort by firstpart ASC secondpart ASC */
	protected static function sortFascSasc($a, $b) {
	
		if ($a->firstpart == $b->firstpart) {
			if ($a->secondpart == $b->secondpart) {
				return 0;
			}
			//return ($a->secondpart < $b->secondpart) ? -1 : 1;
			return self::compare($a->secondpart, $b->secondpart);
		}
		//return ($a->firstpart < $b->firstpart) ? -1 : 1;
		return self::compare($a->firstpart, $b->firstpart);
	}
	
	/* sort by firstpart DESC secondpart DESC */
	protected static function sortFdescSdesc($a, $b) {
	
		if ($a->firstpart == $b->firstpart) {
			if ($a->secondpart == $b->secondpart) {
				return 0;
			}
			//return ($a->secondpart > $b->secondpart) ? -1 : 1;
			return self::compare($b->secondpart, $a->secondpart);
		}
		//return ($a->firstpart > $b->firstpart) ? -1 : 1;
		return self::compare($b->firstpart, $a->firstpart);
	}
	
	protected static function _substring_index($subject, $delim, $count)
	{
	    if ($count < 0) {
	        return implode($delim, array_slice(explode($delim, $subject), $count));
	    } else {
	        return implode($delim, array_slice(explode($delim, $subject), 0, $count));
	    }
	}

}
