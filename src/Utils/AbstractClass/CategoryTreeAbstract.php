<?php 

namespace App\Utils\AbstractClass;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class CategoryTreeAbstract
{

    public $categoriesArrayFromDb;
    public $categoryList; 
    protected static $dbconnection; 

    public function __construct(EntityManagerInterface $entitymanager,
    UrlGeneratorInterface $urlgenerator)
    {
        $this->entitymanager = $entitymanager;
        $this->urlgenerator = $urlgenerator;
        $this->categoriesArrayFromDb = $this->getCategories();
    }

    abstract public function getCategoryList(array $categories_array);

    // public function buildTree(int $parent_id = null): array
    // {
    //     $subcategory = [];
    //      dump($this->categoriesArrayFromDb); exit;
    //     foreach($this->categoriesArrayFromDb as $category)
    //     {   
    //         dump($category);
    //         $categoryEr = $this->object_to_array($category);
    //         dump($categoryEr); 
    //         exit;
    //          $parentId = $category->getParent() ? $category->getParent()->getId() : null;
    //          $category_array = ['parent_id' => $parentId, 'id' => $category->getId()];

            
    //          //dump($category_array); exit;
    //         // dump($category);exit;
    //         // $arrayElement = get_object_vars($category);
    //         // dump($arrayElement); exit;
    //         // dump(gettype($category->getParent()));exit;
    //         // dump($category); 
    //         // dump($parent_id);
    //         // dump($this->categoriesArrayFromDb); exit;
    //         // dump($category->getName());
    //         // dump($category->getParent());
    //             // dump($category_array['parent_id']);
    //             // dump($category_array['id']);
    //             if($category_array['parent_id'] == $parent_id)
    //             {
    //                 dump($category_array['id']);
    //                 $children = $this->buildTree($category_array['id']);
    //                 dump($children);
    //                 if($children)
    //                 {
    //                     $category_array['children'] = $children; 
    //                     //dump($category_array); 
    //                 }
    //                 $subcategory[] = $category_array; 
                   
    //             }
            
    //     }
    //     dump($subcategory); 
    //     exit;
    //     return $subcategory;
    // }
    public function buildTree(int $parent_id = null): array
    {
        $subcategory = [];
        // dump(gettype($this->categoriesArrayFromDb)); 
        foreach($this->categoriesArrayFromDb as $category)
        {   
            // $category = json_decode(json_encode((array)$category), true);
            // dump($category["\x00App\Entity\Category\x00id"]); exit;
            // dump($categoryArr);
            // dump($category);exit;
            // $arrayElement = get_object_vars($category);
            // dump($arrayElement); exit;
            // dump(gettype($category->getParent()));exit;
            // dump($category); 
            // dump($parent_id);
            // dump($this->categoriesArrayFromDb); exit;
            // dump($category->getName());
            // dump($category->getParent());
            if($category->getParent() !== null)
            {
                if($category->getParent()->getId() === $parent_id)
                {
                    
                    $children = $this->buildTree($category->getId());
                    if($children)
                    {
                        $category->children = $children; 
                    }
                    $subcategory[] = $category; 
                }
            }
            
        }
        return $subcategory;
    }

    private function getCategories(): array
    {

        if(self::$dbconnection)
        {
            return self::$dbconnection;
        }
        else
        {
            // $conn = $this->entitymanager->getConnection(); 
            // $sql = "SELECT * FROM categories";
            // $stmt = $conn->prepare($sql);
            // $stmt->execute();

           // return $stmt->findAll();

            return   $this->entitymanager->getRepository(Category::class)->findAll();
        }
        
    }

        private function object_to_array($data)
        { 
            dump($data);
            if (is_array($data) || is_object($data))
            {
                $result = [];
                
                foreach ($data as $key => $value)
                {
                    $result[$key] = (is_array($data) || is_object($data)) ? $this->object_to_array($value) : $value;
                }
                return $result;
            }
            return $data;
        }
}