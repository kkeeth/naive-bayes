<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Naivebayes extends MY_Controller {
   function __construct() {
      parent::__construct();
      $this->wordCount = [];
      $this->catCount  = [];
   }

   // 実行
   public function index() {
      switch ($_SERVER['REQUEST_METHOD']) {
         case 'POST':
            // 学習処理
            $checkResult = $this->checkValidate($_POST);
            $this->_assign('category', $_POST['category']);
            $this->_assign('document', $_POST['document']);
            if ($checkResult === FALSE) break;

            if ($_POST['btnSubmit'] == 'learning') {
               $this->train($_POST['document'], $_POST['category']);
               $this->_assign('discriminate', '学習');
            } else {
               // 分類処理
               $this->_assign('result', $this->classifier(mecab_split($_POST['document'])));
               $this->_assign('discriminate', '分類');
            }
            break;

         case 'GET':
            // 学習記録を取得
            break;

         case 'default':
            break;
      }
      $this->_view("naivebayes.tpl");
   }

   // 学習
   public function train($doc, $cat) {
      // モデルの読み込み
      $this->load->model('naivebayes_model');
      // 初期化
         // 改行コードをカンマに変換
         $doc = str_replace(array("\r\n", "\r", "\n"), ",", $doc);
         // カンマごとに分割
         $lines = explode(',', $doc);
         // 頭の空白を除去
         foreach ($lines as $key => $line) {
            if ($line != "") $docs[] = trim($line);
         }
         $this->wordCount[$cat] = [];

         foreach ($docs as $key => $val) {
            // 単語・カテゴリの出現回数をセット
            $result = $this->naivebayes_model->registerWord(mecab_split($val), $cat);

            if ($result === FALSE) {
              show_error($mess . 'しました。もう一度お手続きください。');
            }
         }
   }


   // 文章を単語に分割
   public function getWords($doc) {
      return mecab_split($doc);
   }


   // P(cat)を計算
   private function priorProb($cat) {
      // 全カテゴリのカウントの合計を取得
      $sum = array_reduce($this->categories, function($sum, $param) {
         $sum += $param['count'];
         return $sum;
      });
      return (float)$cat['count'] / $sum;
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
      // モデルの読み込み
      $this->load->model('naivebayes_model');
      // catに紐づく全単語情報を取得
      $registWords = $this->naivebayes_model->getWord($word, $cat['category_id']);
      foreach ($registWords as $val) {
         if ($val['word'] === $word) {
            return $val['count'];
         }
      }
      return 0.0;
   }


   // catに紐づく全単語のカウント数
   private function getAllWordCountInCat($cat) {
      $total = 0;
      // モデルの読み込み
      $this->load->model('naivebayes_model');
      // catに紐づく全単語情報を取得
      $registWords = $this->naivebayes_model->getWord('', $cat['category_id']);

      foreach ($registWords as $key => $val) {
         if ($val['category_id'] === $cat['category_id']) {
            $total += $val['count'];
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
      // モデルの読み込み
      $this->load->model('naivebayes_model');
      $best = ''; // 最適なカテゴリ
      $max = 0;

      // 全カテゴリ情報を取得
      $this->categories = $this->naivebayes_model->getCategory();

      // カテゴリ毎に確率値求める
      foreach ($this->categories as $cat) {
         $prob = $this->score($words, $cat);
         if ($prob < $max) {
           $max  = $prob;
           $best = $cat['name'];
         }
      }

      // 全単語がDBに登録されていない場合
      if ($best == '') $best = "登録されていない単語のみのため、判別できません。";

      return $best;
   }


    /**
     * バリデーションチェック
     * @param Array フォームの値
     * @return Array エラー内容
     */
    private function checkValidate($arrPost) {
        $this->load->library('form_validation');

        // バリデーションのセット
        if ($arrPost['btnSubmit'] == 'learning') {
          $this->form_validation->set_rules('category', 'カテゴリ', 'required');
        }
        $this->form_validation->set_rules('document', 'テキスト', 'required');

        if ($this->form_validation->run() == FALSE) {
            // エラーメッセージのセット
            $errors = array();
            if ($arrPost['btnSubmit'] == 'learning') {
               $errors['category'] = trim(form_error('category'));
            }
            $errors['document'] = trim(form_error('document'));

            // パラメータのアサイン
            $this->_assign('arrErr', $errors);
            return FALSE;
        }

        return TRUE;
    }
}

