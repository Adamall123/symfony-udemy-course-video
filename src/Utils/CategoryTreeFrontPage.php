<?php 

namespace App\Utils;

use App\Utils\AbstractClass\CategoryTreeAbstract;

class CategoryTreeFrontPage extends CategoryTreeAbstract
{
    public function getCategoryList(array $categories_array)
    {
        $this->categoryList .= '<ul>';
        foreach ($categories_array as $value)
        {
            
            $catName = $value->getName();
            $url = $this->urlgenerator->generate('video_list', ['categoryname'=>$catName, 'id'=>$value->getId()]);
            $this->categoryList .= '<li>' . '<a href="' . $url . '">' . $catName . '</a>';
            if(!empty($value->children))
            {
                $this->getCategoryList($value->children);
            }
            
        }
        $this->categoryList .= '</ul>';
        return $this->categoryList;
    }
}