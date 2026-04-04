<?php
defined('_JEXEC') or die;
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;

if(version_compare(JVERSION, '4', 'ge')){
		class ModArticlesCategoryHelper extends \Joomla\Module\ArticlesCategory\Site\Helper\ArticlesCategoryHelper{};
		class ModArticlesCategoriesHelper extends \Joomla\Module\ArticlesCategories\Site\Helper\ArticlesCategoriesHelper{};
	}else{
		// Include the helper functions mod_articles_category
		JLoader::register('ModArticlesCategoryHelper',JPATH_ROOT . '/modules/mod_articles_category/helper.php');
		JLoader::register('ModArticlesCategoriesHelper',JPATH_ROOT . '/modules/mod_articles_categories/helper.php');
	}
class JATemplateHelper
{
	public function __construct()
	{
		
	}
	public static function getArticles($params, $catid, $count, $front = 'show')
	{
		$aparams = clone $params;
		$aparams->set('count', $count);
		$aparams->set('show_front', $front);
		$aparams->set('catid', (array)$catid);
		$aparams->set('show_child_category_articles', 1);
		$aparams->set('levels', 10);
		$aparams->set('created_by_alias', -1);
		$alist = ModArticlesCategoryHelper::getList($aparams);
		return $alist;
	}

	public static function getCategories($parent = 'root', $count = 0)
	{
		$params = new JRegistry();
		$params->set('parent', $parent);
		$params->set('count', $count);
		return ModArticlesCategoriesHelper::getList($params);
	}

	public static function loadModule($name, $style = 'raw')
	{
		jimport('joomla.application.module.helper');
		$module = JModuleHelper::getModule($name);
		$params = array('style' => $style);
		echo JModuleHelper::renderModule($module, $params);
	}

	public static function loadModules($position, $style = 'raw')
	{
		jimport('joomla.application.module.helper');
		$modules = JModuleHelper::getModules($position);
		$params = array('style' => $style);
		foreach ($modules as $module) {
			echo JModuleHelper::renderModule($module, $params);
		}
	}

}

?>