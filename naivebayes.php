<?php

class NaiveBayes {
   function __construct() {
      $this->wordCount = [];
      $this->catCount  = [];
   }


   // 実行
   public function index() {
      // ダミーの初期カテゴリ配列　※重複はないものとする
      $arrCat = ['php', 'javascript', 'python'];

      // 初期化
      foreach ($arrCat as $cat) {
         // 分割したテキスト
         $file = file_get_contents("/path_to_text_directory/{$cat}.txt", true);
         // 改行コードをカンマに変換
         $file = str_replace(array("\r\n", "\r", "\n"), ",", $file);
         // カンマごとに分割
         $lines = explode(',', $file);
         // 頭の空白を除去
         foreach ($lines as $key => $line) {
            if ($line != "") $docs[$cat][] = trim($line);
         }
         $this->wordCount[$cat] = [];
         $this->catCount  = [];
      }

      // 各カテゴリの文章毎に学習
      foreach ($arrCat as $cat) {
         $this->train($docs[$cat], $cat);
      }
   }


   // 文章を単語に分割
   public function getWords($doc) {
      return mecab_split($doc);
   }


   // 単語の出現数を登録
   private function wordCountSet($words, $cat) {
      // 単語配列が空の場合終了
      if ($words == "") return -1;

      // 単語の出現回数をセット
      foreach ($words as $val) {
         if (array_key_exists($val, $this->wordCount[$cat]) === false) $this->wordCount[$cat][$val] = 0;
         $this->wordCount[$cat][$val]++;
      }
   }


   // カテゴリの文章出現数を登録
   private function catCountSet($cat) {
      if (array_key_exists($cat, $this->catCount) === false) $this->catCount[$cat] = 0;
      // 単語の出現回数をセット
      $this->catCount[$cat]++;
   }


   // 学習
   public function train($docs, $cat) {
      foreach ($docs as $key => $val) {
         $this->wordCountSet(mecab_split($val), $cat);
         $this->catCountSet($cat);
      }
   }


   // P(cat)を計算
   private function priorProb($cat) {
      return (float)$this->catCount[$cat] / array_sum($this->catCount);
   }


   // P(doc|cat)を計算
   private function docProb($words, $cat) {
      $prob = 1;
      foreach ($words as $key => $val) {
         $retWordProb = $this->wordProb($val, $cat);
         // P(word|cat)が0か否かで分岐
         // 0の場合はラプラス法より1をかける
         $prob += $retWordProb > 0 ? log($retWordProb) : 0;
      }

      return $prob;
   }


   // P(word|cat)を計算
   public function wordProb($word, $cat) {
      return (float)$this->getWordCountInCat($word, $cat) / $this->getAllWordCountInCat($cat);
   }


   // 単語$wordがカテゴリ$catに含まれる確率
   private function getWordCountInCat($word, $cat) {
      foreach ($this->wordCount[$cat] as $key => $val) {
         if ($key === $word) {
            return $val;
         }
      }
      return 0.0;
   }


   // 全単語がカテゴリ$catに含まれる確率
   private function getAllWordCountInCat($cat) {
      $total = 0;

      foreach ($this->wordCount as $key => $val) {
         if ($key === $cat) {
            $total += array_sum($this->wordCount[$cat]);
         }
      }

      return $total;
   }


   // 確率値の算出
   public function score($words, $cat) {
      $score = log($this->priorProb($cat));
      $score += $this->docProb($words, $cat);

      return $score;
   }


   // カテゴリの推定
   public function classifier($words) {
      $best = ''; // 最適なカテゴリ
      $max = 0;

      // カテゴリ毎に確率値求める
      foreach (array_keys($this->catCount) as $cat) {
         $prob = $this->score($words, $cat);
         if ($prob < $max) {
           $max  = $prob;
           $best = $cat;
         }
      }

      return $best;
   }
}

$obj = new NaiveBayes();
// 文章を読み込ませる
$obj->index();

$doc = "PHPとJavaScriptで機械学習を勉強する。";
var_dump("推定カテゴリ：　" . $obj->classifier(mecab_split($doc)));
