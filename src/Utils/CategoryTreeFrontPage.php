<?php 

namespace App\Utils;

use App\Twig\AppExtentsionExtension;
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
    
    public function getCategoryListAndParent(int $id): string 
    {
        $this->slugger = new AppExtentsionExtension; //Twig extension to slugify url's for categories
        $parentData = $this->getMainParent($id); //main parent of subcategory
        $this->mainParentName  = $parentData['name']; //for accessing in view
        $this->mainParentId  = $parentData['id']; //for accessing in view
        // dump($this->mainParentName); exit;
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));
        $this->currentCategoryName = $this->categoriesArrayFromDb[$key]->getName();

        $categories_array = $this->buildTree($parentData['id']);
        //array for generating nested html list
        //dump( $this->getCategoryList($categories_array)); exit;
        return $this->getCategoryList($categories_array);
    }

    public function getCategoryList(array $categories_array)
    {
        $this->categoryList .= $this->html_1;
        foreach ($categories_array as $value)
        {
            
            $catName = $this->slugger->slugify($value->getName());
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

    public function getMainParent(int $id): array
    {
        // dump($id);
        // dump($this->categoriesArrayFromDb); 
        $whichElementOfArray = 0;
        foreach($this->categoriesArrayFromDb as $category)
        {   
            if($category->getId() === $id) {
               break;
            }
            $whichElementOfArray++;
        }
        // dump($this->categoriesArrayFromDb[$whichElementOfArray]->getName()); exit;
        if($this->categoriesArrayFromDb[$whichElementOfArray]->getParent() !== null)
        {
            return $this->getMainParent($this->categoriesArrayFromDb[$whichElementOfArray]->getParent()->getId());
        }
        else 
        {
            return [
                'id' => $this->categoriesArrayFromDb[$whichElementOfArray]->getId(),
                'name' => $this->categoriesArrayFromDb[$whichElementOfArray]->getName()
            ];
        }
    }
}