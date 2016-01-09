<?php
require_once "../src/naivebayes.php";

class NaiveBayesTest extends PHPUnit_Framework_TestCase {

   public function setUp() {
       $this->obj = new NaiveBayes();
       // 初期化
       $this->obj->index();
   }

   public function test_classifier() {
      $doc = 'PHPで機械学習を勉強する。';
      $this->assertEquals('php', $this->obj->classifier(mecab_split($doc)));

      $doc = 'JavaScriptで機械学習を勉強する。';
      $this->assertEquals('javascript', $this->obj->classifier(mecab_split($doc)));

      $doc = 'Pythonで機械学習を勉強する。';
      $this->assertEquals('python', $this->obj->classifier(mecab_split($doc)));
   }
}
