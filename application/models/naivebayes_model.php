<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ナイーブベイズのモデル
 */
class Naivebayes_model extends CI_Model
{
    /**
    * 単語の登録
    *
    * @param  Array  $words 単語（String）の配列
    * @param  String $cat   カテゴリ
    * @return bool
    */
    public function registerWord($words, $cat) 
    {
        // モデルの読み込み
        $this->load->model('query_model');
        $result = false;

        try {
            // カテゴリの新規登録or更新
            $this->registerCategory($cat);
            // カテゴリIDの取得
            $cat_id = $this->getCategory($cat, 'category_id');
            if ($cat_id === false) {
                throw new Exception('データベース参照エラーが発生'); 
            }

            // 単語ごとにDB登録
            foreach ($words as $word) {
                // catに紐づく単語の登録チェック
                $exist_check = $this->getWord($word, $cat_id[0]['category_id']);
                if ($exist_check === false) {
                    throw new Exception('データベース参照エラーが発生'); 
                }

                if (empty($exist_check)) {
                    $result = $this->query_model->insert(
                        'word_count', 
                        [
                            'word'              => $word,
                            'category_id'       => $cat_id[0]['category_id'],
                            'last_learned_date' => date('Y-m-d H:i:s'),
                            'count'             => 1,
                        ]
                    );
                } else {
                    $result = $this->query_model->update(
                        'word_count', 
                        [
                            'last_learned_date' => date('Y-m-d H:i:s'),
                            'count'             => ++$exist_check[0]['count'],
                        ],
                        [
                            'word'        => $word,
                            'category_id' => $cat_id[0]['category_id'],
                        ]
                    );
                }
                if ($result === false) {
                    throw new Exception('データベース登録エラーが発生'); 
                }
            }
            return array(true);

        } catch (Exception $e) {
            return array(false, $e->getMessage());
        }
    }

    /**
     * カテゴリの登録
     *
     * @param  String $cat カテゴリ
     * @return bool
     */
    public function register_category($cat)
    {
        // モデルの読み込み
        $this->load->model('query_model');
        $result = false;

        try {
            // カテゴリの存在チェック
            $exist_check = $this->getCategory($cat, 'category_id, count');
            if ($exist_check === false) {
                throw new Exception('データベース参照エラーが発生'); 
            }

            if (empty($exist_check)) {
                $result = $this->query_model->insert(
                    'category_count', 
                    [
                        'name'              => $cat,
                        'last_learned_date' => date('Y-m-d H:i:s'),
                        'count'             => 1,
                    ]
                );
            } else {
                $result = $this->query_model->update(
                    'category_count', 
                    [
                        'last_learned_date' => date('Y-m-d H:i:s'),
                        'count'             => ++$exist_check[0]['count'],
                    ],
                    [
                        'name' => $cat,
                    ]
                );
            }
            if ($result === false) {
                throw new Exception('データベース登録エラーが発生'); 
            }
            return array(true, "");

        } catch (Exception $e) {
            return array(false, $e->getMessage());
        }
    }

    /**
     * カテゴリ情報の取得
     *
     * @param  String $cat カテゴリ
     * @param  String $col 抽出カラム
     * @return Array カテゴリ情報の配列
     */
    public function getCategory($cat = '', $col = '*')
    {
        // モデルの読み込み
        $this->load->model('query_model');

        try {
            $where = '';
            if ($cat != '') {
                $where = array(
                'name' => $cat,
                );
            }
            $ret = $this->query_model->select('category_count', $col, $where);
            if ($ret === false) {
                throw new Exception('データベース参照エラーが発生'); 
            }
            $result = $ret->result_array();

            return $result;

        } catch (Exception $e) {
            return array(false, $e->getMessage());
        }
    }

    /**
     * 単語情報の取得
     *
     * @param  String $word   単語
     * @param  Int    $cat_id カテゴリID
     * @return ret:Array 単語情報の配列
     */
    public function getWord($word = '', $cat_id)
    {
        // モデルの読み込み
        $this->load->model('query_model');

        try {
            $where = '';
            if ($word != '') {
                $where = [
                    'word'        => $word,
                    'category_id' => $cat_id,
                ];
            } else {
                $where = [
                    'category_id' => $cat_id,
                ];
            }
            $ret = $this->query_model->select('word_count', '*', $where);
            if ($ret === false) {
                throw new Exception('データベース参照エラーが発生'); 
            }
            $result = $ret->result_array();

            return $result;

        } catch (Exception $e) {
            return array(false, $e->getMessage());
        }
    }
}
