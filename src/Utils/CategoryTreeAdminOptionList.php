<?php 

namespace App\Utils;

use App\Utils\AbstractClass\CategoryTreeAbstract;

class CategoryTreeAdminOptionList extends CategoryTreeAbstract
{
    public function getCategoryList(array $categories_array,int $repeat = 0)
    {
        foreach($categories_array as $value)
        {
            $this->categoryList[] = ['name' => str_repeat("-", $repeat). 
            $value->getName(), 'id' => $value->getId()];

            if(!empty($value->children))
            {
                $repeat += 2; 
                $this->getCategoryList($value->children, $repeat);
                $repeat -= 2; 
            }
        }
    }
}