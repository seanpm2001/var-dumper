<?php

namespace Yiisoft\VarDumper\Tests;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\VarDumper as VD;
use Yiisoft\VarDumper\VarDumper;
use Yiisoft\VarDumper\VarDumper as Dumper;

/**
 * @group helpers
 */
final class VarDumperTest extends TestCase
{
    /**
     * @dataProvider exportDataProvider
     * @param mixed $var
     * @param string $expectedResult
     */
    public function testExport($var, $expectedResult): void
    {
        $exportResult = VarDumper::create($var)->export();
        $this->assertEqualsWithoutLE($expectedResult, $exportResult);
    }

    public function exportDataProvider(): array
    {
        $customDebugInfo = new CustomDebugInfo();
        $customDebugInfo->volume = 10;
        $customDebugInfo->unitPrice = 15;

        $incompleteObject = unserialize('O:16:"nonExistingClass":0:{}');

        $emptyObject = new stdClass();

        $objectWithReferences1 = new stdClass();
        $objectWithReferences2 = new stdClass();
        $objectWithReferences1->object = $objectWithReferences2;
        $objectWithReferences2->object = $objectWithReferences1;

        return [
            'custom debug info' => [
                $customDebugInfo,
                <<<S
                unserialize('O:39:"Yiisoft\\\VarDumper\\\Tests\\\CustomDebugInfo":2:{s:6:"volume";i:10;s:9:"unitPrice";i:15;}')
                S,
            ],
            'incomplete object' => [
                $incompleteObject,
                <<<S
                unserialize('O:16:"nonExistingClass":0:{}')
                S,
            ],
            'empty object' => [
                $emptyObject,
                <<<S
                unserialize('O:8:"stdClass":0:{}')
                S,
            ],
            'short function' => [
                // @formatter:off
                fn () => 1,
                // @formatter:on
                'fn () => 1',
            ],
            'short static function' => [
                // @formatter:off
                static fn () => 1,
                // @formatter:on
                'fn () => 1',
            ],
            'function' => [
                function () {
                    return 1;
                },
                'function () {
                    return 1;
                }',
            ],
            'static function' => [
                static function () {
                    return 1;
                },
                'function () {
                    return 1;
                }',
            ],
            'string' => [
                'Hello, Yii!',
                "'Hello, Yii!'",
            ],
            'empty string' => [
                '',
                "''",
            ],
            'null' => [
                null,
                'null',
            ],
            'integer' => [
                1,
                '1',
            ],
            'integer with separator' => [
                1_23_456,
                '123456',
            ],
            'boolean' => [
                true,
                'true',
            ],
            'resource' => [
                fopen('php://input', 'rb'),
                'NULL',
            ],
            'empty array' => [
                [],
                '[]',
            ],
            'array of 3 elements, automatic keys' => [
                [
                    'one',
                    'two',
                    'three',
                ],
                <<<S
                [
                    'one',
                    'two',
                    'three',
                ]
                S,
            ],
            'array of 3 elements, custom keys' => [
                [
                    2 => 'one',
                    'two' => 'two',
                    0 => 'three',
                ],
                <<<S
                [
                    2 => 'one',
                    'two' => 'two',
                    0 => 'three',
                ]
                S,
            ],
            'closure in array' => [
                // @formatter:off
                [fn () => new \DateTimeZone('')],
                // @formatter:on
                <<<S
                [
                    fn () => new \DateTimeZone(''),
                ]
                S,
            ],
            'original class name' => [
                // @formatter:off
                static fn (VarDumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'class alias' => [
                // @formatter:off
                fn (Dumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'namespace alias' => [
                // @formatter:off
                fn (VD\VarDumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'closure with null-collision operator' => [
                // @formatter:off
                fn () => $_ENV['var'] ?? null,
                // @formatter:on
                "fn () => \$_ENV['var'] ?? null",
            ],
            'object with references'=> [
                $objectWithReferences1,
                <<<S
                unserialize('O:8:"stdClass":1:{s:6:"object";O:8:"stdClass":1:{s:6:"object";r:1;}}')
                S,
            ]
        ];
    }

    /**
     * @dataProvider asPhpStringDataProvider
     *
     * @param mixed $var
     * @param string $expectedResult
     */
    public function testAsPhpString($var, $expectedResult): void
    {
        $exportResult = VarDumper::create($var)->asPhpString();
        $this->assertEqualsWithoutLE($expectedResult, $exportResult);
    }

    public function asPhpStringDataProvider(): array
    {
        return [
            'short function' => [
                // @formatter:off
                fn () => 1,
                // @formatter:on
                'fn () => 1',
            ],
            'short static function' => [
                // @formatter:off
                static fn () => 1,
                // @formatter:on
                'static fn () => 1',
            ],
            'function' => [
                function () {
                    return 1;
                },
                'function () {
                    return 1;
                }',
            ],
            'static function' => [
                static function () {
                    return 1;
                },
                'static function () {
                    return 1;
                }',
            ],
            'string' => [
                'Hello, Yii!',
                "'Hello, Yii!'",
            ],
            'empty string' => [
                '',
                "''",
            ],
            'null' => [
                null,
                'null',
            ],
            'integer' => [
                1,
                '1',
            ],
            'integer with separator' => [
                1_23_456,
                '123456',
            ],
            'boolean' => [
                true,
                'true',
            ],
            'resource' => [
                fopen('php://input', 'rb'),
                'NULL',
            ],
            'empty array' => [
                [],
                '[]',
            ],
            'array of 3 elements' => [
                [
                    'one',
                    'two',
                    'three',
                ],
                "['one','two','three']"
            ],
            'array of 3 elements, custom keys' => [
                [
                    2 => 'one',
                    'two' => 'two',
                    0 => 'three',
                ],
                "[2 => 'one','two' => 'two',0 => 'three']"
            ],
            'closure in array' => [
                // @formatter:off
                [fn () => new \DateTimeZone('')],
                // @formatter:on
                "[fn () => new \DateTimeZone('')]",
            ],
            'original class name' => [
                // @formatter:off
                fn (VarDumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'class alias' => [
                // @formatter:off
                fn (Dumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'namespace alias' => [
                // @formatter:off
                fn (VD\VarDumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'closure with null-collision operator' => [
                // @formatter:off
                fn () => $_ENV['var'] ?? null,
                // @formatter:on
                "fn () => \$_ENV['var'] ?? null",
            ],
        ];
    }

    /**
     * @dataProvider asJsonObjectMap
     *
     * @param mixed $var
     * @param string $expectedResult
     * @group JOM
     */
    public function testAsJsonObjectsMap($var, $expectedResult): void
    {
        $exportResult = VarDumper::create($var)->asJsonObjectsMap();
        $this->assertStringContainsString($expectedResult, $exportResult);
    }

    public function asJsonObjectMap(): array
    {
        $user = new stdClass();
        $user->id = 1;
        $objectId = spl_object_id($user);

        $decoratedUser = clone $user;
        $decoratedUser->name = 'Name';
        $decoratedUser->originalUser = $user;
        $decoratedObjectId = spl_object_id($decoratedUser);

        return [
            [
                $user,
                <<<S
                "stdClass#{$objectId}":{"public::id":1}
                S,
            ],
            [
                $decoratedUser,
                <<<S
                "stdClass#{$decoratedObjectId}":{"public::id":1,"public::name":"Name","public::originalUser":"object@stdClass#{$objectId}"}
                S,
            ],
        ];
    }

    /**
     * @dataProvider jsonDataProvider()
     */
    public function testAsJson($variable, string $result): void
    {
        $output = VarDumper::create($variable)->asJson();
        $this->assertEqualsWithoutLE($result, $output);
    }

    /**
     * Asserting two strings equality ignoring line endings.
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     */
    protected function assertEqualsWithoutLE(string $expected, string $actual, string $message = ''): void
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);
        $this->assertEquals($expected, $actual, $message);
    }

    public function jsonDataProvider(): array
    {
        $var = new stdClass();
        $var->name = 'Dmitry';
        $binaryString = pack('H*', md5('binary string'));

        $var2 = new stdClass();
        $var2->a = fn () => 1;

        return [
            'object1'=>[
                $var,
                '{"stdClass":{"public::name":"Dmitry"}}',
            ],
            'emoji supported' => [
                ['emoji' => '🤣'],
                '{"emoji":"🤣"}',
            ],
            'closure supported' => [
                $var2,
                '{"stdClass":{"public::a":"fn () => 1"}}',
            ],
            'hex supported' => [
                ['string' => $binaryString],
                '{"string":"ɍ��^��\u00191\u0017�]�-f�"}',
            ],
            [
                fopen('php://input', 'rb'),
                '{"timed_out":false,"blocked":true,"eof":false,"wrapper_type":"PHP","stream_type":"Input","mode":"rb","unread_bytes":0,"seekable":true,"uri":"php:\/\/input"}',
            ],
        ];
    }

    /**
     * @dataProvider asStringDataProvider
     * @param mixed $variable
     * @param string $result
     */
    public function testAsString($variable, string $result): void
    {
        $output = VarDumper::create($variable)->asString();
        $this->assertEqualsWithoutLE($result, $output);
    }

    public function asStringDataProvider(): array
    {
        $customDebugInfo = new CustomDebugInfo();
        $customDebugInfo->volume = 10;
        $customDebugInfo->unitPrice = 15;
        $customDebugInfoObjectId = spl_object_id($customDebugInfo);

        $incompleteObject = unserialize('O:16:"nonExistingClass":0:{}');
        $incompleteObjectId = spl_object_id($incompleteObject);

        $emptyObject = new stdClass();
        $emptyObjectId = spl_object_id($emptyObject);

        return [
            'custom debug info' => [
                $customDebugInfo,
                <<<S
                Yiisoft\VarDumper\Tests\CustomDebugInfo#{$customDebugInfoObjectId}
                (
                    [volume] => 10
                    [totalPrice] => 150
                )
                S,
            ],
            'incomplete object' => [
                $incompleteObject,
                <<<S
                __PHP_Incomplete_Class#{$incompleteObjectId}
                (
                    [__PHP_Incomplete_Class_Name] => 'nonExistingClass'
                )
                S,
            ],
            'empty object' => [
                $emptyObject,
                <<<S
                stdClass#{$emptyObjectId}
                (
                )
                S,
            ],
            'short function' => [
                // @formatter:off
                fn () => 1,
                // @formatter:on
                'fn () => 1',
            ],
            'short static function' => [
                // @formatter:off
                static fn () => 1,
                // @formatter:on
                'fn () => 1',
            ],
            'function' => [
                function () {
                    return 1;
                },
                'function () {
                    return 1;
                }',
            ],
            'static function' => [
                static function () {
                    return 1;
                },
                'function () {
                    return 1;
                }',
            ],
            'string' => [
                'Hello, Yii!',
                "'Hello, Yii!'",
            ],
            'empty string' => [
                '',
                "''",
            ],
            'null' => [
                null,
                'null',
            ],
            'integer' => [
                1,
                '1',
            ],
            'integer with separator' => [
                1_23_456,
                '123456',
            ],
            'boolean' => [
                true,
                'true',
            ],
            'resource' => [
                fopen('php://input', 'rb'),
                '{resource}',
            ],
            'empty array' => [
                [],
                '[]',
            ],
            'array of 3 elements, automatic keys' => [
                [
                    'one',
                    'two',
                    'three',
                ],
                <<<S
                [
                    0 => 'one'
                    1 => 'two'
                    2 => 'three'
                ]
                S,
            ],
            'array of 3 elements, custom keys' => [
                [
                    2 => 'one',
                    'two' => 'two',
                    0 => 'three',
                ],
                <<<S
                [
                    2 => 'one'
                    'two' => 'two'
                    0 => 'three'
                ]
                S,
            ],
            'closure in array' => [
                // @formatter:off
                [fn () => new \DateTimeZone('')],
                // @formatter:on
                <<<S
                [
                    0 => fn () => new \DateTimeZone('')
                ]
                S,
            ],
            'original class name' => [
                // @formatter:off
                static fn (VarDumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'class alias' => [
                // @formatter:off
                fn (Dumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'namespace alias' => [
                // @formatter:off
                fn (VD\VarDumper $date) => new \DateTimeZone(''),
                // @formatter:on
                "fn (\Yiisoft\VarDumper\VarDumper \$date) => new \DateTimeZone('')",
            ],
            'closure with null-collision operator' => [
                // @formatter:off
                fn () => $_ENV['var'] ?? null,
                // @formatter:on
                "fn () => \$_ENV['var'] ?? null",
            ],
        ];
    }
}
