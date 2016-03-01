<!doctype html>
<html lang="ja">
   <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <title>ナイーブベイズの実装</title>
      <link href="/css/bootstrap.min.css" rel="stylesheet">
      <script src="/js/jquery.js"></script>
      <script src="/js/bootstrap.min.js"></script>
      <script type="text/javascript">
         var method = '<!--{$discriminate|escape}-->';
         if (method !== null && method !== '') {
            window.addEventListener("DOMContentLoaded", function(eve) {
               alert(method + "しました！");
            });
         }
      </script>
   </head>
   <body>
      <div class="container">
         <p class="lead">ナイーブベイズのデモ</p>
         <form action="/naivebayes" name="filter" method="post">
            <div class="form-group">
               <label for="category">カテゴリ</label>
               <!--{if $errors.category != ''}-->
                  <div class="alert alert-danger" role="alert"><!--{$errors.category|escape}--></div>
               <!--{/if}-->
               <input type="text" name="category" class="form-control" id="category" placeholder="カテゴリを入力してください。" value="<!--{$category|default:''|escape}-->" />
            </div>
            <div class="form-group">
               <label for="document">登録・分類するテキスト（必須）</label>
               <!--{if $errors.document != ''}-->
                  <div class="alert alert-danger" role="alert"><!--{$errors.document|escape}--></div>
               <!--{/if}-->
               <textarea name="document" cols="70" rows="8" class="form-control" id="document" placeholder="学習・分類させるテキストを入力してください。"><!--{$document|default:''|escape}--></textarea>
            </div>
            <div class="form-group">
               <button name="btn_submit" class="btn btn-info btn-lg" value="learning"><b>学習</b></button>
               <button name="btn_submit" class="btn btn-success btn-lg" value="filter"><b>分類</b></button>
            </div>
         </form>
         <!--{if $result !== '' && $result !== NULL}--><p class="lead">推定カテゴリ：<!--{$result|escape}--></p><!--{/if}-->
      </div>
   </body>
</html>
