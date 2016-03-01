<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Naivebayes extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->wordCount = [];
        $this->catCount  = [];
        // アプリケーションIDを設定
        $this->appid     = "";
    }

    public function index()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // 学習処理
            $check_result = $this->check_validate($_POST);
            $this->_assign('category', $_POST['category']);
            $this->_assign('document', $_POST['document']);
            if ($check_result === false) {
                break;
            }

            if ($_POST['btn_submit'] == 'learning') {
                $this->train($_POST['document'], $_POST['category']);
                $this->_assign('discriminate', '学習');
            } else {
                // 分類処理
                $words = $this->split_text($_POST['document']);
                $this->_assign('result', $this->classifier($words));
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

    /**
     * 学習
     *
     * @param  String $doc 学習文章
     * @param  String $cat カテゴリ
     * @return Void
     */
    public function train($doc, $cat)
    {
        // モデルの読み込み
        $this->load->model('naivebayes_model');
        // 初期化
        // 改行コードをカンマに変換
        $doc = str_replace(["\r\n", "\r", "\n"], ",", $doc);
        // カンマごとに分割
        $lines = explode(',', $doc);
        // 頭の空白を除去
        foreach ($lines as $key => $line) {
            if ($line != "") {
                $docs[] = trim($line);
            }
        }
        $this->word_count[$cat] = [];

        foreach ($docs as $key => $val) {
            // Yahoo WebAPIにより単語分割
            $words = $this->split_text($val);

            // 単語・カテゴリの出現回数をセット
            $result = $this->naivebayes_model->register_word($words, $cat);

            if ($result === false) {
                show_error($mess . 'しました。もう一度お手続きください。');
            }
        }
    }


    /**
 * P(cat)を計算
     *
     * @param  String $cat カテゴリ
     * @return Float カテゴリの出現確率
     */
    private function prior_prob($cat)
    {
        // 全カテゴリのカウントの合計を取得
        $sum = array_reduce(
            $this->categories, function ($sum, $param) {
                $sum += $param['count'];
                return $sum;
            }
        );
        return (float)$cat['count'] / $sum;
    }


    /**
 * P(doc|cat)を計算
     *
     * @param  Array  $words 単語配列
     * @param  String $cat   カテゴリ
     * @return Float $prob $catに$wordが登場する確率の合計
     */
    private function doc_prob($words, $cat)
    {
        $prob = 0;
        foreach ($words as $key => $val) {
            $ret_word_prob = $this->word_prob($val, $cat);
            // P(word|cat)が0か否かで分岐
            // 0の場合はラプラス法より1をかける
            $prob += $ret_word_prob > 0 ? log($ret_word_prob) : 0;
        }

        return $prob;
    }


    /**
     * P(word|cat)を計算
     *
     * @param  String $word 単語
     * @param  String $cat  カテゴリ
     * @return Float $catに$wordが登場する確率
     */
    public function word_prob($word, $cat)
    {
        return (float)$this->get_word_count_in_cat($word, $cat) / $this->get_all_word_count_in_cat($cat);
    }


    /**
     * $catに紐づく$wordのカウント数
     *
     * @param  String $word 単語
     * @param  String $cat  カテゴリ
     * @return Int カウント数
     */
    private function get_word_count_in_cat($word, $cat)
    {
        // モデルの読み込み
        $this->load->model('naivebayes_model');
        // catに紐づく全単語情報を取得
        $registWords = $this->naivebayes_model->get_word($word, $cat['category_id']);
        foreach ($registWords as $val) {
            if ($val['word'] === $word) {
                return $val['count'];
            }
        }
        return 0;
    }


    /**
     * $catに紐づく全単語のカウント数
     *
     * @param  String $cat カテゴリ
     * @return Int    $total カウント数
     */
    private function get_all_word_count_in_cat($cat)
    {
        $total = 0;
        // モデルの読み込み
        $this->load->model('naivebayes_model');
        // catに紐づく全単語情報を取得
        $regist_words = $this->naivebayes_model->get_word('', $cat['category_id']);

        foreach ($regist_words as $key => $val) {
            if ($val['category_id'] === $cat['category_id']) {
                $total += $val['count'];
            }
        }

        return $total;
    }


    /**
     * 確率値の算出
     *
     * @param  Array  $words 単語配列
     * @param  String $cat   カテゴリ
     * @return Float $score 確率の対数の合計
     */
    public function score($words, $cat)
    {
        $score = log($this->prior_prob($cat));
        $score += $this->doc_prob($words, $cat);

        return $score;
    }


    /**
     * カテゴリの推定
     *
     * @param  Array $words 分割した単語配列
     * @return String $best カテゴリ
     */
    public function classifier($words)
    {
        // モデルの読み込み
        $this->load->model('naivebayes_model');
        $best = ''; // 最適なカテゴリ
        $max = 0;

        // 全カテゴリ情報を取得
        $this->categories = $this->naivebayes_model->get_category();

        // カテゴリ毎に確率値求める
        foreach ($this->categories as $cat) {
            $prob = $this->score($words, $cat);
            if ($prob < $max) {
                $max  = $prob;
                $best = $cat['name'];
            }
        }

        // 全単語がDBに登録されていない場合
        if ($best == '') {
            $best = "登録されていない単語のみのため、判別できません。";
        }

        return $best;
    }


    /**
     * バリデーションチェック
     * 
     * @param  Array $post_data フォームの値
     * @return Array エラー内容
     */
    private function check_validate($post_data)
    {
        $this->load->library('form_validation');

        // バリデーションのセット
        if ($post_data['btn_submit'] == 'learning') {
            $this->form_validation->set_rules('category', 'カテゴリ', 'required');
        }
        $this->form_validation->set_rules('document', 'テキスト', 'required');

        if ($this->form_validation->run() == false) {
            // エラーメッセージのセット
            $errors = [];
            if ($post_data['btn_submit'] == 'learning') {
                $errors['category'] = trim(form_error('category'));
            }
            $errors['document'] = trim(form_error('document'));

            // パラメータのアサイン
            $this->_assign('errors', $errors);
            return false;
        }

        return true;
    }


   /**
    * 単語に分割
    * 
    * @param  String $text フォームのテキスト文章
    * @return Array 単語配列
    */
   private function split_text($text)
   {
       $return = [];
       $url = "http://jlp.yahooapis.jp/MAService/V1/parse?appid=" . $this->appid . "&results=ma";
       $url .= "&sentence=".urlencode($text);
       $xml = simplexml_load_file($url);
       foreach ($xml->ma_result->word_list->word as $word) {
          $return[] = (string)$word->surface;
       }

       return $return;
   }
}

