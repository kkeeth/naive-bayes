<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Exchange_model
 */
class Naivebayes_model extends CI_Model {
   /**
    * 単語の登録
    * @param words:Array 単語（String）の配列
    * @param cat:String カテゴリ
    * @return bool
    */
   public function registerWord($words, $cat) {
      // モデルの読み込み
      $this->load->model('query_model');
      $result = FALSE;

      try {
         // カテゴリの新規登録or更新
         $this->registerCategory($cat);
         // カテゴリIDの取得
         $catId = $this->getCategory($cat, 'category_id');
         if ($catId === FALSE) throw new Exception('データベース参照エラーが発生');

         // 単語ごとにDB登録
         foreach ($words as $word) {
            // catに紐づく単語の登録チェック
            $existCheck = $this->getWord($word, $catId[0]['category_id']);
            if ($existCheck === FALSE) throw new Exception('データベース参照エラーが発生');

            if (empty($existCheck)) {
               $result = $this->query_model->insert('word_count', 
                  array(
                     'word'              => $word,
                     'category_id'       => $catId[0]['category_id'],
                     'last_learned_date' => date('Y-m-d H:i:s'),
                     'count'             => 1,
                  )
               );
            } else {
               $result = $this->query_model->update('word_count', 
                 array(
                    'last_learned_date' => date('Y-m-d H:i:s'),
                    'count'             => ++$existCheck[0]['count'],
                 ),
                 array(
                    'word'        => $word,
                    'category_id' => $catId[0]['category_id'],
                 )
               );
            }
            if ($result === FALSE) throw new Exception('データベース登録エラーが発生');
         }
         return array(TRUE);

      } catch (Exception $e) {
         return array(FALSE, $e->getMessage());
      }
   }

   /**
    * カテゴリの登録
    * @param cat:String カテゴリ
    * @return bool
    */
   public function registerCategory($cat) {
      // モデルの読み込み
      $this->load->model('query_model');
      $result = FALSE;

      try {
         // カテゴリの存在チェック
         $existCheck = $this->getCategory($cat, 'category_id, count');
         if ($existCheck === FALSE) throw new Exception('データベース参照エラーが発生');

         if (empty($existCheck)) {
            $result = $this->query_model->insert('category_count', 
               array(
                  'name'              => $cat,
                  'last_learned_date' => date('Y-m-d H:i:s'),
                  'count'             => 1,
               )
            );
         } else {
            $result = $this->query_model->update('category_count', 
               array(
                  'last_learned_date' => date('Y-m-d H:i:s'),
                  'count'             => ++$existCheck[0]['count'],
               ),
               array(
                  'name' => $cat,
               )
            );
         }
         if ($result === FALSE) throw new Exception('データベース登録エラーが発生');
         return array(TRUE, "");

      } catch (Exception $e) {
         return array(FALSE, $e->getMessage());
      }
   }

   /**
    * カテゴリ情報の取得
    * @param cat:String カテゴリ
    * @return ret:Array カテゴリ情報の配列
    */
   public function getCategory($cat = '', $col = '*') {
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
         if ($ret === FALSE) throw new Exception('データベース参照エラーが発生');
         $result = $ret->result_array();

         return $result;

      } catch (Exception $e) {
         return array(FALSE, $e->getMessage());
      }
   }

   /**
    * 単語情報の取得
    * @param word:String 単語
    * @param catId:Int カテゴリID
    * @return ret:Array 単語情報の配列
    */
   public function getWord($word = '', $catId) {
      // モデルの読み込み
      $this->load->model('query_model');

      try {
         $where = '';
         if ($word != '') {
            $where = array(
               'word'        => $word,
               'category_id' => $catId,
            );
         } else {
            $where = array(
               'category_id' => $catId,
            );
         }
         $ret = $this->query_model->select('word_count', '*', $where);
         if ($ret === FALSE) throw new Exception('データベース参照エラーが発生');
         $result = $ret->result_array();

         return $result;

      } catch (Exception $e) {
         return array(FALSE, $e->getMessage());
      }
   }
}
