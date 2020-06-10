<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class NotDecimal extends Constraint
{
    public $message = "app.ui.not_decimal";

    /**
     * @return string
     */
    public function validateBy() {
        return \get_class($this).'Validator';
    }
}
