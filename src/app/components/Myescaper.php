<?php

namespace App\Controller;

use Phalcon\Escaper;

class Myescaper
{
    public function sanitize($input)
    {
        $escaper = new Escaper();
        $arr =  $input;
        foreach ($arr as $key => $val) {
            $input[$key] = $escaper->escapeHtml($val);
        }
        return $input;
    }
}
