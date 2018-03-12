<?php
/**
 * @Copyright (c) 2018, sunny-daisy.
 * All Rights Reserved.
 *
 * Daisy\MongoDB\MongoDB Class phpunit 
 *
 * @author      wenqiang1 <wenqiang1@staff.sina.com.cn>
 * @createdate  2018-03-06
 */

use PHPUnit\Framework\TestCase;

class MongoDBTest extends TestCase{

    public function testBuildUriErrorDB()
    {
        $mongo = new Daisy\MongoDB\MongoDB($db = 'errordb');
        $debug = $mongo->__debugInfo();
        $this->assertNull($debug['client']);
    }

    /**
     * @dataProvider provideConstructInvalidDriverOptions
     */
    public function testConstructInvalidDriverOptions(array $driverOptions)
    {
        $mongo = new Daisy\MongoDB\MongoDB('db0', [], $driverOptions);
        $debug = $mongo->__debugInfo();
        $this->assertNull($debug['client']);
    } 

    public function provideConstructInvalidDriverOptions()
    {
        $invalidArrs = [11, '11', true, new StdClass];
        foreach ($invalidArrs as $arr) {
            $options[][] = ['typeMap' => $arr];
        }
        return $options;
    }

    public function testClassAttributeType()
    {
        $mongo = new Daisy\MongoDB\MongoDB('db0');
        $debug = $mongo->__debugInfo();
        $this->assertInstanceOf('MongoDB\Client', $debug['client']);
    } 

    public function testMultiDatabase()
    {
        $mongo = new Daisy\MongoDB\MongoDB('db0');
        $mongo = new Daisy\MongoDB\MongoDB('db1');
        $debug = $mongo->__debugInfo();
        $this->assertEquals(2, sizeof($debug['clients']));
    }

    public function testInvalidDatabaseCollectionName() 
    {
        $mongo = new Daisy\MongoDB\MongoDB('db0');
        $ret = $mongo->findOne();
        $this->assertFalse($ret);
        $ret = $mongo->database->findOne();
        $this->assertFalse($ret);
    }

    public function testDrop()
    {
        $mongo = new Daisy\MongoDB\MongoDB('db0'); 
        $ret = $mongo->test->test->insertOne(['x' => 1]);
        $this->assertEquals(1, $ret->getInsertedCount());
        $ret = $mongo->test->test->drop();
        $this->assertEquals(1, $ret->ok);
    }
}
