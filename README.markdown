# Fumocker [![Build Status](https://secure.travis-ci.org/formapro/Fumocker.png?branch=master)](http://travis-ci.org/formapro/Fumocker)

Are you wonna mock mail function?

```php
<?php

class FooTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fumocker\Fumocker
     */
    protected $fumocker;

    public function setUp()
    {
        $this->fumocker = new \Fumocker\Fumocker;
    }

    public function tearDown()
    {
        $this->fumocker->cleanup();
    }

    public function testMailNeverCalled()
    {
        /**
         * @var $mock \PHPUnit_Framework_MockObject_MockObject
         */
        $mock = $this->fumocker->getMock('Namespace/Where/Tested/Class/Is', 'mail');
        $mock
            ->expects($this->never())
            ->method('mail')
        ;

        //test your Namespace/Where/Tested/Class/Is/Foo
    }


    public function testMailSendToAdminWithStatisticSubject()
    {
        $expectedEmailTo = 'admin@example.com';

        /**
         * @var $mock \PHPUnit_Framework_MockObject_MockObject
         */
        $mock = $this->fumocker->getMock('Namespace/Where/Tested/Class/Is', 'mail');
        $mock
            ->expects($this->once())
            ->method('mail')
            ->with(
                $this->equalTo($expectedEmailTo),
                $this->equalTo('Statisitic of money earned by a week')
            )
        ;

        //test your Namespace/Where/Tested/Class/Is/Foo
    }


    public function testRealMailSend()
    {
        /**
         * Dont do a mock and real function will be called
         */
    }
}

```