<?php

/**
 * Copyright 2015 Mário Camargo Palmeira
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Rdthk\Validation\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $validator = new Validator(['foo' => 'bar']);
        $this->assertEquals('bar', $validator->getData('foo'));

        $validator = new Validator();
        $this->assertEmpty($validator->getData());
    }

    public function testSetData()
    {
        $validator = new Validator();
        $validator->setData(['foo' => 'bar']);
        $this->assertEquals('bar', $validator->getData('foo'));

        $validator = new Validator(['foo' => 'bar']);
        $validator->setData(['foo' => 'baz']);
        $this->assertEquals('baz', $validator->getData('foo'));
    }

    public function testSetRule()
    {
        $validator = new Validator(['foo' => 'bar']);
        $validator->setRule('custom', function ($validator, $field, $opts) {
            $errors = [];
            if ($validator->getData('foo') !== $opts['cmp']) {
                $errors[] = 'Error';
            }
            return $errors;
        });

        $validator->custom('foo', ['cmp' => 'bar']);
        $this->assertEmpty($validator->getErrors());

        $validator->custom('foo', ['cmp' => 'php is great']);
        $this->assertEquals(['foo' => ['Error']], $validator->getErrors());
    }

    public function testGetData()
    {
        $validator = new Validator([
            'foo' => 'bar',
            'one' => 1,
            'true' => true,
        ]);

        $this->assertEquals('bar', $validator->getData('foo'));
        $this->assertEquals(
            ['foo' => 'bar', 'one' => 1],
            $validator->getData(['foo', 'one'])
        );
        $this->assertEquals(
            ['foo' => 'bar'],
            $validator->getData(['foo', 'x'])
        );
        $this->assertNotEmpty($validator->getData());
        $this->assertEmpty($validator->getData([]));
    }

    public function testHasErrors()
    {
        $validator = new Validator([
            'foo' => 'bar',
        ]);

        $validator->setRule('required', function($validator, $field, $opts) {
            $errors = [];
            if (empty($validator->getData($field))) {
                $errors[] = "$field is required";
            }
            return $errors;
        });

        $validator->required('foo');
        $this->assertFalse($validator->hasErrors());

        $validator->required('baz');
        $this->assertTrue($validator->hasErrors());
    }

    public function testGeErrors()
    {
        $validator = new Validator([
            'foo' => 'bar',
            'one' => '1',
        ]);
        $validator->setRule('length', function($validator, $field, $opts) {
            $errors = [];
            if (
                isset($opts['min']) &&
                strlen($validator->getData($field)) < $opts['min']
            ) {
                $errors[] = 'Too short!';
            }
            return $errors;
        });
        $validator->length(['foo', 'one'], ['min' => 2]);

        $this->assertTrue($validator->hasErrors());
        $this->assertNotEmpty($validator->getErrors());
        $this->assertEmpty($validator->getErrors('foo'));
        $this->assertEmpty($validator->getErrors(['foo']));
        $this->assertNotEmpty($validator->getErrors('one'));
        $this->assertNotEmpty($validator->getErrors(['foo', 'one']));
    }
}
