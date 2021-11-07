<?php

namespace App\Tests\Twig;

use PHPUnit\Framework\TestCase;
use App\Twig\AppExtentsionExtension;

class SluggerTest extends TestCase
{
    /**
     * @dataProvider getSlugs
     */
    public function testSlugify(string $string, string $slug): void
    {
        $slugger = new AppExtentsionExtension;

        $this->assertSame($slug, $slugger->slugify($string));
    }

    public function getSlugs()
    {
        
            yield ['Lorem Ipsum', 'lorem-ipsum'];
            yield [' Lorem Ipsum', 'lorem-ipsum'];
            yield [' lOrEm iPsUm','lorem-ipsum'];
            yield ['!Lorem Ipsum!','lorem-ipsum'];
            yield ['lorem-ipsum','lorem-ipsum'];
            yield ['Children\'s books','childrens-books'];
            yield ['Five star movies','five-star-movies'];
            yield ['Adults 60+','adults-60'];
    }
}
/*
./tests.sh tests all tests
./tests.sh tests tests/Twig tests for this specifics method
./tests.sh tests -db rebuilding database and tests
*/