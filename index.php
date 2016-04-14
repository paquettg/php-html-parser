<?php
/**
 * User: Asror Zakirov
 * Date: 14.02.2016
 */


$sample_array =   array();

$sample_array[]  =    'asasf';
$sample_array[]  =    'asasf';
$sample_array[]  =    'asasf';


class SampleClass {
    public $asrorName;
    public $second;
}

$sample =    new SampleClass;

$sample->asrorName ='asasdfa';
$sample->second = 'safasfsadf';

print_r($sample);
