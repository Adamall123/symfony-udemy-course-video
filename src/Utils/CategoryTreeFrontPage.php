<?php 

namespace App\Utils;

use App\Utils\AbstractClass\CategoryTreeAbstract;

class CategoryTreeFrontPage extends CategoryTreeAbstract
{

    public $html_1 = '<ul>';
    public $html_2 = '<li>';
    public $html_3 = '<a href="';
    public $html_4 = '">';
    public $html_5 = '</a>';
    public $html_6 = '</li>';
    public $html_7 = '</ul>';
    
    public function getCategoryList(array $categories_array)
    {
        $this->categoryList .= $this->html_1;
        foreach ($categories_array as $value)
        {
            
            $catName = $value->getName();
            $url = $this->urlgenerator->generate('video_list', ['categoryname'=>$catName, 'id'=>$value->getId()]);
            $this->categoryList .= $this->html_2 . $this->html_3 .  $url . $this->html_4 . $catName . $this->html_5;
            if(!empty($value->children))
            {
                $this->getCategoryList($value->children);
            }
            $this->categoryList .= $this->html_6;
        }
        $this->categoryList .= $this->html_7;
        return $this->categoryList;
    }
}