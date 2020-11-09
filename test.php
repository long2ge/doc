<?php
/**
 * Created by PhpStorm.
 * User: long
 * Date: 2020/11/9
 * Time: 2:20 PM
 */

$variable = ['a', 'b', 'c'];

foreach ($variable as $key => &$value) {

}

foreach ($variable as $key => $value) {
    var_dump($variable);
}
echo "<pre />";
var_dump($variable);
echo "<pre />";
#结果
//array(3) {
//    [0]=>
//  string(1) "a"
//    [1]=>
//  string(1) "b"
//    [2]=>
//  &string(1) "b"
//}