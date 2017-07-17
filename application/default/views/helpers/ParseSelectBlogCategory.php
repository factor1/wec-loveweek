<?php

require_once 'models/blog.phpm';

class Zend_View_Helper_ParseSelectBlogCategory
{
	public $view;

	public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

	public function parseSelectBlogCategory($intValue = 0)
	{
        $objCategories = Blog::getBlogCategoriesForSelect();

		if (count($objCategories)) {
            $arrAttribs['class'] = 'userinput';

            $arrOptions[0] = '';

            foreach ($objCategories AS $objCategory) {
               $arrOptions[$objCategory->id] = $objCategory->blog_category;
            }

            return $this->view->formSelect('category_id', $intValue, $arrAttribs, $arrOptions);
		} else{
		    return 'There are no categories at this time.';
		}
    }
}
