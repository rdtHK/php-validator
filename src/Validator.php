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

 namespace Rdthk\Validation;

class Validator
{
    private $data = [];
    private $rules = [];
    private $validations = [];
    private $errors = [];

    public function __construct($data = [])
    {
        $this->setData($data);
    }

    public function getData($field = null)
    {
        if (!isset($field)) {
            return $this->data;
        }
        if (is_array($field)) {
            $result = [];
            foreach ($field as $key) {
                if (isset($this->data[$key])) {
                    $result[$key] = $this->data[$key];
                }
            }
            return $result;
        }
        if (isset($this->data[$field])) {
            return $this->data[$field];
        }
        return null;
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->validate();
    }

    public function setRule($name, $callback)
    {
        $this->rules[$name] = $callback;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getErrors($fields = null)
    {
        $fields = is_string($fields)? [$fields]: $fields;
        $result = [];

        foreach ($this->errors as $name => $errors) {
            if ($fields === null || in_array($name, $fields)) {
                $result[$name] = $errors;
            }
        }

        return $result;
    }

    public function __call($rule, $args)
    {
        $fields = $args[0];
        $opts = isset($args[1])? $args[1]: [];

        if (!is_array($fields)) {
            $fields = [$fields];
        }
        foreach($fields as $field) {
            if (!isset($this->validations[$field])) {
                $this->validations[$field] = [];
            }
            $this->validations[$field][] = [$this->rules[$rule], $opts];
        }

        $this->validate();
    }

    private function validate()
    {
        $this->errors = [];
        foreach ($this->validations as $field => $validations) {
            foreach ($validations as $validation) {
                list($rule, $opts) = $validation;
                $errors = $rule($this, $field, $opts);
                if (empty($errors)) {
                    continue;
                }
                if (!isset($this->errors[$field])) {
                    $this->errors[$field] = [];
                }
                $this->errors[$field] = array_merge(
                    $this->errors[$field],
                    $errors
                );
            }
        }
    }
}
