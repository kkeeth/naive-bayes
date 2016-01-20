これは機械学習のためのベイジアンフィルタ（単純ベイズ）のプログラムになります。
はじめにカテゴリ・文章を学習させ、その後カテゴライズしたい文章を設定し、実行する形となります。

### 実行環境
以下がインストールされていることを想定しています。

- Apache 2.2
- PHP 5.4以上
- MySQL 5.6
- Mecab
- php-mecab
- Composer

### 環境構築
#### ソースのダウンロード
[こちら](https://github.com/k-kuwahara/naive_bayes/archive/webApp.zip)より圧縮したソースをダウンロードしてください。

#### パッケージ・ライブラリのインストール

```bash
$ composer self-update
$ composer install
```

#### Apacheの設定
以下を追記して再起動してください。
```apache
# ディレクティブの追記
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{REQUEST_URI} !\.(css|pdf|png|jpe?g|gif|js|swf|txt|ico|s?html?)$
    RewriteRule ^(.*)$ /index.php/$1 [L]
</IfModule>
```

#### データベース接続設定
`application/config/***/database.php`を編集してください。※`development, testing, production`の三つとも編集する必要があります。主に変更するのは以下の部分です。

```php
<?php
'hostname' => 'localhost',
'username' => 'username',
'password' => 'password',
'database' => 'database',
'dbdriver' => 'mysql',
```

開発用(development)、テスト用(testing)、本番用(production)と分かれていますので、適宜`index.php`の`define('ENVIRONMENT', 'development');`の部分を変更してください。

#### マイグレーションの設定
`application/config/migration.php`ファイルより、マイグレーションの有効化・バージョンの設定を行ってください。

```php
<?php
$config['migration_enabled'] = TRUE;
$config['migration_version'] = 2;   // カスタマイズした際は適宜変更
```

#### マイグレーションの実行
このアプリケーションでは、マイグレーションをコマンドラインから実行するため、先にデータベースを作成してください。
作成後、以下のコマンドをコマンドラインから実行してください。

```bash
# ディレクトリ移動
$ cd APP_ROOT

# マイグレーションの実行
$ php index.php migrate current

# 以下のコマンドでも実行できます
# ※このコマンドではマイグレーションの設定は不要です
$ php index.php migrate latest
```

### 使い方
- 学習する場合  
カテゴリ、テキスト入力フォームどちらにも入力し、「学習」ボタンを押下してください。

- 分類する場合  
テキスト入力フォームのみ入力、「分類」ボタンを押下してください。


### ラインセンス
ライセンスは「[MIT License](https://github.com/k-kuwahara/naive_bayes/blob/master/LICENSE.md)」です。

### その他
コードレビュー、ご意見をいつでもお待ちしております！
