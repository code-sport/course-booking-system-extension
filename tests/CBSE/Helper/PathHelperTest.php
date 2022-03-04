<?php declare(strict_types=1);

namespace CBSE\Helper;

use PHPUnit\Framework\TestCase;

class PathHelperTest extends TestCase
{

    public function testRealPathBasic()
    {
        $this->assertEquals('hallo' . DIRECTORY_SEPARATOR . 'test',
            PathHelper::realPath('hallo/test')
        );
    }

    public function testRealPathAdvanced()
    {
        $this->assertEquals('hallo' . DIRECTORY_SEPARATOR . 'test',
            PathHelper::realPath('hallo/dummy/../test')
        );
    }

    public function testCombineBasic()
    {
        $this->assertEquals('hallo' . DIRECTORY_SEPARATOR . 'test',
            PathHelper::combine('hallo', 'test')
        );
    }

    public function testCombineAdvanced()
    {
        $this->assertEquals('hallo' . DIRECTORY_SEPARATOR . 'dummy' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'test',
            PathHelper::combine('hallo', 'dummy', '..', 'test')
        );
    }
}

