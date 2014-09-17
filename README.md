Hadogun2
=========


require
=========

php==5.5
> phirehose will malfunction on php 5.6

zmq pecl extention

startup
=========

```
php collector.php
```

and

```
php run distoributor.php
```

and

```
cd public_html
php -S localhost:3333
```

ADMIN
=================

```
// 表示するツイートを 1/40に
connection.conn.publish('パスワードchangeリデュース', '40');

// 覇道砲発射閾値確認間隔変更
connection.conn.publish('パスワードchange単位時間', '60');

// 覇道砲発射閾値変更 （単位時間あたりのツイート数）
connection.conn.publish('パスワードchange波動砲発射閾値', '60');
```
