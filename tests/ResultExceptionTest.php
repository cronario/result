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
        try {
            throw new ResultException(ResultException::R_SUCCESS);
        } catch (ResultException $e) {
            $this->assertEquals(ResultException::R_SUCCESS, $e->getCode());
        }
    }

    public function testStatusSuccess()
    {
        try {
            throw new ResultException(ResultException::R_SUCCESS);
        } catch (ResultException $e) {
            $this->assertTrue($e->isSuccess());
        }
    }

    public function testStatusFailure()
    {
        try {
            throw new ResultException(ResultException::R_FAILURE);
        } catch (ResultException $e) {
            $this->assertTrue($e->isFailure());
        }
    }

    public function testStatusError()
    {
        try {
            throw new ResultException(ResultException::E_INTERNAL);
        } catch (ResultException $e) {
            $this->assertTrue($e->isError());
        }
    }

    public function testFailMessage()
    {
        try {
            throw new ResultException(ResultException::R_FAILURE);
        } catch (ResultException $e) {
            $this->assertEquals(
                ResultException::$results[ResultException::R_FAILURE][ResultException::P_MESSAGE],
                $e->getMessage()
            );
        }
    }

    public function testSuccessMessage()
    {
        try {
            throw new ResultException(ResultException::R_SUCCESS);
        } catch (ResultException $e) {
            $this->assertEquals(
                ResultException::$results[ResultException::R_SUCCESS][ResultException::P_MESSAGE],
                $e->getMessage()
            );
        }
    }

    public function testCustomStatus()
    {
        try {
            throw new ResultException(
                ResultException::TEST_E,
                array(ResultException::P_STATUS => ResultException::STATUS_CUSTOM)
            );
        } catch (ResultException $e) {
            $this->assertTrue($e->isCustomStatus());
        }
    }

    public function testAdminMessage()
    {
        try {
            throw new ResultException(ResultException::TEST_E_ADMIN_MESSAGE);
        } catch (ResultException $e) {
            $this->assertEquals(
                ResultException::$results[ResultException::TEST_E_ADMIN_MESSAGE][ResultException::P_MESSAGE_ADMIN],
                $e->getMessageAdmin()
            );
        }
    }

    /**
     * @throws \Result\RuntimeException
     */
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
}
