<?php

namespace App\Models;

use stdClass;

abstract class Model implements ModelDataInterface
{
    /**
     * Fill the Model's attributes with the given attributes.
     *
     * @param array|stdClass $attributes
     * @return void
     */
    public function fill(array|stdClass $attributes): void
    {
        if ($attributes instanceof stdClass) {
            $attributes = get_object_vars($attributes);
        }
        $myAttributes = get_class_vars($this::class);
        foreach (array_keys($attributes) as $attribute) {
            if (array_key_exists($attribute, $myAttributes)) {
                $this->{'set' . ucwords($attribute)}($attributes[$attribute]);
            }
        }
    }

    public function __toString(): string
    {
        $array = $this->__toArray();
        return json_encode($array);
    }

    public function __toArray(): array
    {
        /*+----------------------------------------------------------------------------------------------+
        * | Verifica si el objeto es una subclase de Model y de ser así retorna sus atributos como array |
        * +----------------------------------------------------------------------------------------------+*/
        $isModel = function ($value): mixed {
            if (is_subclass_of($value, Model::class)) {
                return $value->__toArray();
            }
            return $value;
        };

        $attributes = array_keys(get_class_vars($this::class));
        $array = array();
        foreach ($attributes as $attribute) {
            $value = $this->{'get' . ucwords($attribute)}();
            if (is_subclass_of($value, Model::class)) {
                $value = $isModel($value);
            } else if (is_array($value)) {
                $value = array_map($isModel, $value);
            }

            $array[$attribute] = $value;
        }

        return $array;
    }

    public function __dataAttributes(): array
    {
        $attributes = get_class_vars($this::class);
        $transientAtt = $this->transientAttributes();

        foreach ($transientAtt as $att) {
            unset($attributes[$att]);
        }

        $attributes = array_keys($attributes);
        $array = array();
        foreach ($attributes as $attribute) {
            $array[$attribute] = $this->{'get' . ucwords($attribute)}();
        }

        return $array;
    }

}