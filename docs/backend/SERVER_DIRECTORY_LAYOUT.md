# サーバー側ディレクトリ構成（案）

更新日：2026-10-21  
状態：Master／ClientのLaravel共通化と商品画像の保存先は確定（2026-10-20）。その他の配置方針は検討案で、サブドメイン設定とLaravelの公開入口の接続方法は未確定。

## 前提

- XServerビジネス上で、デモ用と公開用のLaravelアプリを分ける。
- 各環境では、Master管理画面とClient（Partner）管理画面が同じLaravelアプリを使用する（担当者決定：2026-10-20）。
- 管理画面用のログインは今回のLaravelで新規実装する（担当者決定：2026-10-20）。ログインID・ユーザーテーブル・ログイン状態の共有方法は別途決める。Master／Clientの管理範囲と初期の2区分の権限は`DB_DESIGN.md`の確定内容に従う。購入者側の認証方式は今回の決定に含めない。
- 公開サイトはNext.jsを別環境で静的書き出しし、生成物をサーバーへ配置する。サーバー上ではNext.jsをビルドしない。
- カート・注文確定の処理は今回のLaravelで新規実装する（担当者決定：2026-10-20）。公開画面からの接続方法・API契約・決済連携は別途決める。
- 店舗ごとの注文分割と送料計算もLaravelのサーバー側で行う（担当者決定：2026-10-20）。実行タイミング・決済との関係は未確定。
- 商品一覧・商品詳細は、管理画面操作に伴い生成されるJSONを公開サイトが表示時に参照する想定。商品詳細は共通ページにパラメータ付きリンクで表示する。
- JSONの公開先とキャッシュによる反映遅れは、今回の配置検討では保留する。

## 実行環境の確認（2026-10-20）

- サーバー側で使用するPHPは8.3.35（担当者からの共有：2026-10-20）
- Laravelは13.xを採用する（担当者決定：2026-10-20）。PHP 8.3.35と組み合わせる
- Laravel公式資料では13.xの対応PHPは8.3〜8.5、セキュリティ修正期限は2028-03-17。確認元：[リリース・サポート方針](https://laravel.com/framework/docs/13.x/releases#support-policy)、[サーバー要件](https://laravel.com/framework/docs/13.x/deployment#server-requirements)（2026-10-20確認）
- サーバー側のDBはMariaDB 10.11.xを使用する（担当者からの共有：2026-10-20）。[Laravel 13.x公式のDB対応条件](https://laravel.com/framework/docs/13.x/database#introduction)を満たすことを確認
- 管理画面には[Laravel標準のセッション認証](https://laravel.com/framework/docs/13.x/authentication#laravels-built-in-browser-authentication-services)を採用し、ログイン後の状態をサーバー側で管理する（担当者決定：2026-10-20）。セッションの保存先・有効期限・Master／Client間の共有方法は未確定。ログイン入力項目をフロント方針に合わせる決定は維持する
- SPEC「バックエンドへの確認事項」6（現在は[QA No.6](../QA_BACKEND.md#no6-技術構成)）の回答欄には、2026-10-20に担当者の了承を得て技術構成と未確定事項を反映済み。回答欄以外の全体資料の更新は別途確認後に行う

## バックエンドのローカル開発環境（方針決定：2026-10-20）

- バックエンドのローカル環境はXAMPPを使う予定（担当者決定：2026-10-20）
- リポジトリのDDEVはフロントの制作確認用として扱う。バックエンドのためにフロント用DDEVのドキュメントルートを切り替える方針は採らない
- XAMPP側のPHPは8.3.31（2026-10-21、Laravel導入時に実機で確認。2026-10-20の共有時点では8.3.30と記録）。Laravel 13.xのPHPバージョン条件を満たす。サーバー側のPHP 8.3.35とは区別して記録する。拡張機能の確認結果は「Laravelの導入記録」を参照
- XAMPP側のDBはMariaDB 10.4.32（担当者からの共有：2026-10-20）。[Laravel 13.xのDB対応条件](https://laravel.com/framework/docs/13.x/database#introduction)（MariaDB 10.3以上）を満たす。サーバー側のMariaDB 10.11.xとはバージョンが異なるため、DBの機能・照合順序等の差による影響は実装時に確認する。ポートは3308（担当者からの共有：2026-10-21）。2026-10-21にLaravelからの接続とmigrateを確認済み（下記「Laravelの導入記録」）
- XAMPPのApacheにVirtualHostを設定し、リポジトリ内の`backend/public`をドキュメントルートとして表示する方式を採用する（担当者決定：2026-10-20）。リポジトリをXAMPPの`htdocs`へ移動せず、現在の配置のまま使用する
- ローカルURLは`http://demo-kumanichi-shop-manage.test`とする（担当者決定：2026-10-20）。VirtualHostの`ServerName`は`demo-kumanichi-shop-manage.test`とし、Windowsの`hosts`で同名を`127.0.0.1`に対応付ける。Master／ClientのローカルでのURL振り分けは別途決める
- ApacheとWindowsの`hosts`の具体的な設定は環境構築時に行う。既存VirtualHostと使用ポートの競合は設定前に確認する。Laravelは2026-10-21に`backend/`へ導入済み（下記「Laravelの導入記録」）。DDEVの設定は変更しない
- 生成CSS・JS・画面用画像はLaravelの`public`配下へコピーして取り込む方式を採用する（担当者決定：2026-10-20）。取り込み運用の詳細は次節を参照
- SPEC「バックエンドへの確認事項」7（現在は[QA No.7](../QA_BACKEND.md#no7-ddev生成css)）の回答欄には、2026-10-20に担当者の了承を得てローカル環境・アセット取り込み方針と未確定事項を反映済み。回答欄以外の全体資料の更新は別途確認後に行う

## Laravelの導入記録（2026-10-21）

- `backend/`にLaravel 13.38.0を導入した。`composer create-project laravel/laravel:^13.0 --no-scripts`で作成し、`backend/`直下へ移動した
- 導入時のローカル環境：PHP 8.3.31、Composer 2.10.3。Laravelに必要なPHP拡張（ctype・curl・dom・fileinfo・filter・hash・mbstring・openssl・pcre・pdo・pdo_mysql・session・tokenizer・xml）とzipが有効であることを確認。`composer.lock`はこのPHPで作成し、`composer check-platform-reqs`は全項目success
- `--no-scripts`のため、`post-create-project-cmd`のうち`database.sqlite`の作成と`migrate --graceful`は実行していない。`.env`は`.env.example`から作成し、`APP_KEY`の生成だけを行った
- ローカルDB：DB名`kumanichi_shop_manage`（utf8mb4_unicode_ci）、この DB だけに権限を持つ専用ユーザーで接続する（担当者決定：2026-10-21）。`.env`は`DB_CONNECTION=mariadb`・`DB_HOST=127.0.0.1`・`DB_PORT=3308`。`.env.example`はひな形として`DB_PORT=3306`とし、ユーザー名・パスワードは空欄にする
- セッション・キャッシュ・キューは、ローカルではLaravelの初期値のDB保存（`database`）を仮に使う（担当者決定：2026-10-21）。要確認：本番の保存先・有効期限、Master／Client間の共有方法。`SESSION_DOMAIN`は空のまま（アクセスしたホストだけにCookieを送る）とする案
- `php artisan migrate`でLaravel標準のテーブル（users・password_reset_tokens・sessions・cache・cache_locks・jobs・job_batches・failed_jobs）を作成した（2026-10-21）。`users`は管理画面ユーザーの仮のテーブルで、認証用ユーザーテーブルの設計（`DB_DESIGN.md`）が決まったら追加のマイグレーションで変更する
- Laravel付属のREADMEは`backend/README.laravel.md`として残した。`backend/.gitattributes`（Laravel付属）により、`backend/`配下の改行コードはLFで管理する
- XAMPPのVirtualHost・Windowsの`hosts`の設定とブラウザでの表示確認は担当者が行う。設定例：

```apache
<VirtualHost *:80>
    ServerName demo-kumanichi-shop-manage.test
    DocumentRoot "E:/web_data/sv973.xbiz.ne.jp/kumanichi.com/shop-manage_2611/backend/public"
    <Directory "E:/web_data/sv973.xbiz.ne.jp/kumanichi.com/shop-manage_2611/backend/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

`hosts`：`127.0.0.1 demo-kumanichi-shop-manage.test`

## CSS・JS・画面用画像の取り込み（確定部分：2026-10-20）

- フロント側で生成したCSS、JS、画面用画像を、バックエンド担当者がLaravelの`public`配下へコピーする方式を採用する（担当者決定：2026-10-20）
- Master用とClient用はフォルダを分けて配置し、Bladeから読み込む。具体的な配置パスは組み込み時に決める
- Sassは既存のフロント側の編集元・出力先で管理し、生成後のCSSを取り込む。フロントのSass出力先をLaravel側へ変更する方式は採らない
- フロント更新時は差分を確認して再度取り込む。Laravel連携のために修正したJSは、その変更内容を確認して取り込み内容を調整する
- 今回は仕様の記録のみを行い、ファイルのコピー・Bladeの修正・コピー用スクリプトの作成は行わない。自動化の有無・具体的な手順は未確定
- この取り込み対象は画面の静的アセット。アップロードされた商品画像・入稿画像の保存方針とは区別する
- 根拠：担当者の承認、`../SPEC.md`「フォルダとSass」、[QA No.7](../QA_BACKEND.md#no7-ddev生成css)、`../coding/IMPLEMENTATION_REFERENCE.md`「制作環境と引き渡し」、`../../backend/README.md`「バックエンド担当者と決めること」

## Laravel本体（公開ディレクトリの外）

```text
/home/{サーバーID}/laravel-apps/
├─ demo-shop-kumanichi-control/   # デモ用Laravel
│  ├─ app/
│  ├─ bootstrap/
│  ├─ config/
│  ├─ database/
│  ├─ public/                     # Laravel標準の公開用ファイル
│  ├─ resources/
│  ├─ routes/
│  ├─ storage/
│  ├─ vendor/
│  ├─ .env
│  └─ artisan
└─ shop-kumanichi-control/        # 公開用Laravel
   ├─ app/
   ├─ bootstrap/
   ├─ config/
   ├─ database/
   ├─ public/
   ├─ resources/
   ├─ routes/
   ├─ storage/
   ├─ vendor/
   ├─ .env
   └─ artisan
```

`.env`、アプリケーションコード、`vendor/`、`storage/` はWebの公開フォルダに置かない。デモ用と公開用の設定・保存データは混在させない。

## ローカルとサーバーの対応（方針：2026-10-21）

リポジトリの `backend/` の中身を、サーバーのLaravel本体のフォルダにそのまま配置する。Laravelは自分のフォルダを基準にパスを解決するため、置き場所が違っても同じコードで動かす。

| 区分 | Laravel本体 | 公開する入口 |
| --- | --- | --- |
| ローカル（XAMPP） | リポジトリの `backend/` | `backend/public`（VirtualHostのDocumentRoot） |
| サーバー・デモ | `/home/{サーバーID}/laravel-apps/demo-shop-kumanichi-control/` | `public_html/demo-shop-master/`・`demo-shop-partner/` |
| サーバー・公開 | `/home/{サーバーID}/laravel-apps/shop-kumanichi-control/` | `public_html/shop-master/`・`shop-partner/` |

- `backend/public/index.php` などリポジトリ内のファイルを、サーバーの配置に合わせて書き換えない。サーバー側で必要な調整は、配置時の手順として別に扱う
- コードの中にローカル・サーバーの絶対パスを書かない。`base_path()`・`storage_path()`・`public_path()` などのLaravelのヘルパーを使う
- 環境ごとの違い（URL・DB接続・保存先など）は、各環境の `.env` で切り替える。`.env` はリポジトリに含めない
- 要確認：サーバーの公開フォルダとLaravel本体をつなぐ方法（公開フォルダを `public/` に直接向ける、入口の `index.php` だけを置く、シンボリックリンクでつなぐ等）。XServerの設定・制限を確認して決める（下記「配置前の確認事項」2）
- 要確認：商品画像の公開URLに使う `php artisan storage:link`（シンボリックリンク）がXServerで使えるか
- 要確認：ローカルでのMaster／PartnerのURLの分け方（サーバーはサブドメインで分ける。[QA No.7](../QA_BACKEND.md#no7-ddev生成css)）

## Web公開フォルダ（想定）

次のパスは、各サブドメインのドキュメントルートをXServer側で対応付ける前提の案。フォルダ名だけでURLとの対応は決まらない。

```text
/home/{サーバーID}/kumanichi.com/public_html/
├─ demo-shop/          # デモ用Next.jsの静的書き出し一式
├─ demo-shop-master/   # デモ用LaravelへのMaster公開入口
├─ demo-shop-partner/  # 同じデモ用LaravelへのPartner公開入口
├─ shop/               # 公開用Next.jsの静的書き出し一式
├─ shop-master/        # 公開用LaravelへのMaster公開入口
└─ shop-partner/       # 同じ公開用LaravelへのPartner公開入口
```

Next.jsの公開フォルダには、`index.html` だけでなく、書き出された各ページ、`_next/`、その他の静的アセットを配置する。管理画面の公開フォルダには、対応するLaravelの `public/` 相当の入口と公開アセットだけを置く。2つの管理画面から同一Laravelへ接続する具体的な方法は、XServer上の制約を確認して決める。

| 環境 | 公開フォルダ | 想定URL | 接続先 |
| --- | --- | --- | --- |
| デモ | `demo-shop/` | `https://demo-shop.kumanichi.com/` | Next.js静的書き出し |
| デモ | `demo-shop-master/` | `https://demo-shop-master.kumanichi.com/` | デモ用Laravel |
| デモ | `demo-shop-partner/` | `https://demo-shop-partner.kumanichi.com/` | デモ用Laravel |
| 公開 | `shop/` | `https://shop.kumanichi.com/` | Next.js静的書き出し |
| 公開 | `shop-master/` | `https://shop-master.kumanichi.com/` | 公開用Laravel |
| 公開 | `shop-partner/` | `https://shop-partner.kumanichi.com/` | 公開用Laravel |

## 商品画像の保存先（確定：2026-10-20）

Laravelを設置するXServerビジネス内に保存する。公開用の保存先は次の構成とする。

```text
/home/{サーバーID}/laravel-apps/shop-kumanichi-control/
└─ storage/app/
   ├─ private/tmp/product-images/  # 商品画像の一時保存
   └─ public/product-images/       # 商品保存後の画像
```

- 一時画像はWebから直接アクセスできない場所に置き、認可を確認したLaravelの処理を通してプレビューする
- 本保存画像は購入側サイトから表示できる公開URLを用意する。URLとフォルダの接続方法はXServerの配置方法に合わせて別途決める。`storage/`全体を公開するものではない
- 画像ファイルはフォルダに保存し、DBには保存パス・表示順などの管理情報を記録する
- デモ用は`/home/{サーバーID}/laravel-apps/demo-shop-kumanichi-control/storage/app/`配下に同じ構成を持ち、公開用の画像とは分離する
- 商品に本保存されなかった一時画像はアップロードから24時間で期限切れとし、定期処理で削除する（確定：2026-10-20）。本保存済みの商品画像は対象外とする
- 商品画像のアップロード許可形式はJPEG・PNG・WebPとする（確定：2026-10-20）
- 商品画像の容量上限は1枚あたり5MBとする（確定：2026-10-20）
- 商品画像は当面、メイン画像と詳細画像を合わせて1商品あたり最大10枚とする（決定：2026-10-20、将来見直し可）
- 期限切れ一時画像の削除処理は1時間ごとに実行し、実行時点でアップロードから24時間を過ぎた一時画像を削除する（確定：2026-10-20）
- 定期削除の実行環境・起動方法は未確定。採用方式と残る確認事項は[`DB_DESIGN.md`](DB_DESIGN.md#商品画像の一時保存方針確定2026-10-20)を参照

## 配置前の確認事項

1. XServerに登録する各サブドメインと、実際のドキュメントルートの対応。親ドメインのパス経由でのアクセス可否も確認する。
2. Master・Partnerの2つの公開入口を、同一Laravelアプリへ安全に接続する方法。Laravel標準の `public/index.php` を基準とし、本体を公開しない。
3. デモ用と公開用のDB接続先、セッション、保存先、管理画面のホスト別ルーティング・権限の分離。
4. 購入者側などでSSOを利用する場合の対象範囲、Cookieのデモ・公開サブドメインへの送信、会員基盤APIへの接続条件。管理画面用ログインは今回のLaravelで新規実装する方針で確定しており、この確認項目を管理画面へのSSO採用決定と解釈しない。

商品・注文・送料などの業務仕様は [`../SPEC.md`](../SPEC.md) を正とする。この資料は配置案のみを扱い、既存の業務仕様を変更しない。
