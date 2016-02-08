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
    * @return Bool
    */
    public function register_word($words, $cat)
    {
        $result = false;

        try {
            // カテゴリの新規登録or更新
            $this->register_category($cat);
            // カテゴリIDの取得
            $cat_id = $this->get_category($cat, 'category_id');
            if ($cat_id === false) {
                throw new Exception('データベース参照エラーが発生'); 
            }

            // 単語ごとにDB登録
            foreach ($words as $word) {
                // catに紐づく単語の登録チェック
                $exist_check = $this->get_word($word, $cat_id[0]['category_id']);
                if ($exist_check === false) {
                    throw new Exception('データベース参照エラーが発生'); 
                }

                if (empty($exist_check)) {
                    $data = [
                        'word'              => $word,
                        'category_id'       => $cat_id[0]['category_id'],
                        'last_learned_date' => date('Y-m-d H:i:s'),
                        'count'             => 1,
                    ];
                    $result = $this->db->insert('word_count', $data);
                } else {
                    $data = [
                        'last_learned_date' => date('Y-m-d H:i:s'),
                        'count'             => ++$exist_check[0]['count'],
                    ];
                    $this->db->where('word', $word);
                    $this->db->where('category_id', $cat_id[0]['category_id']);
                    $result = $this->db->update('word_count', $data);
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
     * @return Bool
     */
    public function register_category($cat)
    {
        $result = false;

        try {
            // カテゴリの存在チェック
            $exist_check = $this->get_category($cat, 'category_id, count');
            if ($exist_check === false) {
                throw new Exception('データベース参照エラーが発生'); 
            }

            if (empty($exist_check)) {
                $data = [
                    'name'              => $cat,
                    'last_learned_date' => date('Y-m-d H:i:s'),
                    'count'             => 1,
                ];
                $result = $this->db->insert('category_count', $data);
            } else {
                $data = [
                    'last_learned_date' => date('Y-m-d H:i:s'),
                    'count'             => ++$exist_check[0]['count'],
                ];
                $this->db->where('name', $cat);
                $result = $this->db->update('word_count', $data);
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
    public function get_category($cat = '', $col = '*')
    {
        $result = false;

        try {
            if ($cat != '') {
                $this->db->where('name', $cat);
            }
            $this->db->select($col);
            $ret = $this->db->get('category_count');

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
    public function get_word($word = '', $cat_id)
    {
        try {
            if ($word != '') {
                $this->db->where('word', $word);
            }
            $this->db->where('category_id', $cat_id);
            $ret = $this->db->get('word_count');

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
