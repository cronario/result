<?php

namespace Result\Test;


class ResultExceptionTest extends \PHPUnit_Framework_TestCase
{

    protected static $_code;

    protected function setUp()
    {
        self::$_code['unknown'] = 999;
        self::$_code['outOfRange'] = 9999;
        self::$_code['globalCode'] = 2900;

        \Result\ResultException::setClassIndexMap([
            'Result\Test\ResultException' => 2
        ]);
    }

    /**
     * @expectedException \Result\InvalidArgumentException
     */
    public function testCodeNotNull()
    {
        throw new ResultException(null);
    }

    /**
     * @expectedException \Result\OutOfRangeException
     */
    public function testCodeOutOfRange()
    {
        throw new ResultException(self::$_code['outOfRange']);
    }

    /**
     * @expectedException \Result\InvalidArgumentException
     */
    public function testUnknownCode()
    {
        throw new ResultException(self::$_code['unknown']);
    }


    public function testCode()
    {
        $result = new ResultException(ResultException::R_SUCCESS);
        $this->assertEquals(ResultException::R_SUCCESS, $result->getCode());
    }

    public function testStatusSuccess()
    {
        $result = new ResultException(ResultException::R_SUCCESS);
        $this->assertTrue($result->isSuccess());
    }

    public function testStatusFailure()
    {
        $result = new ResultException(ResultException::R_FAILURE);
        $this->assertTrue($result->isFailure());
    }

    public function testStatusError()
    {
        $result = new ResultException(ResultException::E_INTERNAL);
        $this->assertTrue($result->isError());
    }

    public function testFailMessage()
    {
        $result = new ResultException(ResultException::R_FAILURE);
        $this->assertEquals(
            ResultException::$results[ResultException::R_FAILURE][ResultException::P_MESSAGE],
            $result->getMessage()
        );
    }

    public function testSuccessMessage()
    {
        $result = new ResultException(ResultException::R_SUCCESS);
        $this->assertEquals(
            ResultException::$results[ResultException::R_SUCCESS][ResultException::P_MESSAGE],
            $result->getMessage()
        );
    }

    public function testCustomStatus()
    {
        $result = new ResultException(
            ResultException::TEST_E,
            [ResultException::P_STATUS => ResultException::STATUS_CUSTOM]
        );
        $this->assertTrue($result->isCustomStatus());
        $result->setStatus(ResultException::STATUS_ERROR);
        $this->assertTrue($result->isError());
    }

    public function testAdminMessage()
    {
        $result = new ResultException(ResultException::TEST_E_ADMIN_MESSAGE);
        $this->assertEquals(
            ResultException::$results[ResultException::TEST_E_ADMIN_MESSAGE][ResultException::P_MESSAGE_ADMIN],
            $result->getMessageAdmin()
        );

    }

    public function testStringData()
    {
        $result = new ResultException(ResultException::TEST_STRING);
        $this->assertEquals('I am just string', $result->getMessage());
    }

    public function testFactory()
    {
        $result = ResultException::factory(
            self::$_code['globalCode']
        );
        $this->assertInstanceOf('\\Result\\Test\\ResultException', $result);
        $this->assertEquals(
            self::$_code['globalCode'], $result->getGlobalCode()
        );
    }

    public function testExceptionInput()
    {
        $str = 'Some exception';
        try {
            throw new \Exception($str);
        } catch (\Exception $ex){
            $result = new ResultException($ex);
            $this->assertEquals($str, $result->getMessage());
            $this->assertEquals(ResultException::STATUS_ERROR, $result->getStatus());
        }
    }

    public function testFactorySuccess()
    {
        $result = ResultException::factory(ResultException::R_SUCCESS);
        $this->assertInstanceOf('\\Result\\ResultException', $result);
        $this->assertTrue($result->isSuccess());
    }

    /**
     * @expectedException \Result\InvalidArgumentException
     */
    public function testFactoryUnknownCode()
    {
        ResultException::factory(123456);
    }

    public function testToString()
    {
        $result = ResultException::factory(ResultException::R_SUCCESS);
        $this->assertStringEndsWith('R_SUCCESS', (string) $result);
    }

    public function testToArray()
    {
        $result = ResultException::factory(ResultException::R_SUCCESS);
        $this->assertArrayHasKey('globalCode', $result->toArray());
        $this->assertArrayHasKey('data', $result->toArray());
    }

    public function testBuildGlobalCodeSuccess()
    {
        $code = ResultException::buildGlobalCode(ResultException::R_SUCCESS);
        $this->assertEquals(ResultException::R_SUCCESS, $code);
    }

    public function testMsgArg()
    {
        $result = new ResultException(ResultException::TEST_RESULT,
            [
                ResultException::P_MESSAGE => 'Test %s',
                ResultException::P_MESSAGE_ARG => 'argument'
            ]);
        $this->assertEquals('Test argument', $result->getMessage());
    }

    public function testMsgArgMissingFormat()
    {
        $result = new ResultException(ResultException::TEST_RESULT,
            [
                ResultException::P_MESSAGE => 'Test',
                ResultException::P_MESSAGE_ARG => 'argument'
            ]);
        $this->assertStringEndsWith('[argument]', $result->getMessage());
    }

    public function testMsgArgUnchanged()
    {
        $result = new ResultException(ResultException::TEST_RESULT,
            [
                ResultException::P_MESSAGE => 'Test %s',
                ResultException::P_MESSAGE_ARG => 'argument'
            ]);
        $result->setMessageArg('argument');
        $this->assertEquals('Test argument', $result->getMessage());
    }

    public function testMsgArgParam()
    {
        $result = new ResultException(ResultException::TEST_MSG_ARG, 'argument');
        $this->assertEquals('Test argument', $result->getMessage());
    }

    public function testMsgArgParamPreset()
    {
        $result = new ResultException(ResultException::TEST_PRESET_MSG_ARG);
        $this->assertEquals('Test argument', $result->getMessage());
    }

    public function testInnerException()
    {
        $innerFailure = new ResultException(ResultException::R_FAILURE);
        // let's set exception as 3-d parameter
        $innerSuccess = new ResultException(ResultException::R_SUCCESS, null, $innerFailure);
        $result = new ResultException(ResultException::R_SUCCESS, $innerFailure);
        $this->assertTrue($result->hasInnerException());
        $this->assertTrue($result->getInnerException()->isFailure());
        $this->assertInstanceOf('\\Result\\ResultException', $result->getInnerException());
        // Change exception
        $result->setInnerException($innerSuccess);
        $this->assertTrue($result->getInnerException()->isSuccess());
    }

    public function testGettersAndSetters()
    {
        $result = new ResultException(ResultException::R_SUCCESS,
            [
                'foo' => 'bar'
            ]);

        $this->assertEquals('bar', $result->foo);
        $this->assertNull($result->bar);
        $this->assertFalse(isset($result->bar));
        $result->bar = 'foo';
        $this->assertEquals('foo', $result->bar);
        unset($result->foo);
        $this->assertNull($result->foo);
    }

    public function testArrayAccess()
    {
        $result = new ResultException(ResultException::R_SUCCESS,
            [
                'foo' => 'bar'
            ]);

        $this->assertArrayHasKey('foo', $result);
        $this->assertEquals('bar', $result['foo']);
        $this->assertArrayNotHasKey('bar', $result);
        $this->assertFalse(isset($result['bar']));
        $result['bar'] = 'foo';
        $this->assertEquals('foo', $result['bar']);
        unset($result['foo']);
        $this->assertNull($result['foo']);
    }

    public function testMsgAdmin()
    {
        $result = new ResultException(ResultException::R_SUCCESS,
            [
                ResultException::P_MESSAGE_ADMIN => 'Admin message'
            ]);

        $this->assertEquals('Admin message', $result->getMessageAdmin());
        $result->setMessageAdmin('New admin message');
        $this->assertStringStartsWith('New', $result->getMessageAdmin());
    }

    public function testData()
    {
        $result = new ResultException(ResultException::R_SUCCESS,
            [
                'foo' => 'bar'
            ]);
        $this->assertEquals(1, $result->countData());
        $this->assertArrayHasKey('foo', $result->getData(null));
        $this->assertArrayHasKey('foo', $result->addData(null));
        $result->clearData();
        $this->assertEquals(0, $result->countData());
    }

    /**
     * @expectedException \Result\RuntimeException
     */
    public function testClassIndexError()
    {
         ResultException::getClassIndex('Unknown');
    }

    public function testIgnoreLogging()
    {
        $result = new ResultException(ResultException::TEST_IGNORE);
        $this->assertTrue($result->isIgnoreLogging());
    }

    public function testMessageAlias()
    {
        $result = new ResultException(ResultException::TEST_ALIAS);
        $this->assertEquals('Alias', $result->getMessage());
    }

    public function testTranslator()
    {
        ResultException::setTranslatorFunction('\\Result\\Test\\ResultException::t');
        $result = new ResultException(ResultException::TEST_TRANSLATE_STRING);
        $this->assertStringEndsWith('-translated', $result->getMessage());
    }

    public function testTranslatorFail()
    {
        ResultException::setTranslatorFunction('\\Result\\Test\\ResultException::tUnchanged');
        $result = new ResultException(ResultException::TEST_TRANSLATE_STRING);
        $this->assertStringStartsWith('TEST', $result->getMessage());
    }
}
