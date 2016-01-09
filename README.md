これは機械学習のためのベイジアンフィルタ（単純ベイズ）のプログラムになります。
各言語（PHP, JavaScript, Python）をカテゴリとし、それぞれのカテゴリの学習用ドキュメントを読み込み学習。
その後、カテゴライズしたい文章を設定し、実行する形となります。

### 開発環境
- PHP 5.5
- Mecab
- php-mecab 

最後に`composer install`コマンドを実行してください。

### 使い方
各カテゴリの学習用ドキュメントを`*.txt`を読み込む。保存先は`naivebayes.php`の18行目、

```php
<?php
$file = file_get_contents("/path_to_text_directory/{$cat}.txt", true);
```

にて設定してください。  
その後、同ファイルの終わりから1行前

```php
<?php
$doc = "PHPとJavaScriptで機械学習を勉強する。";
```

にてカテゴライズしたい文章を設定後、実行してください。

### テスト
本プログラムでは、testディレクトリの中の`___各カテゴリ名___.txt`のファイル学習用テキストファイルとして取り込み、
その後ダミー文言を判別しています。  
実行には以下のコマンドを叩いてください。

```bash
cd path_to_application/test/
php ../vendor/bin/phpunit naivebayes_test.php
```

### ラインセンス
ライセンスは「[MIT License](https://github.com/k-kuwahara/naive_bayes/blob/master/LICENSE.md)」です。

### その他
今後は、MySQLにて学習した文章と分割した単語をDBにて保存し、育てられるようにしたいと思います。  
コードレビュー、ご意見をいつでもお待ちしております！
