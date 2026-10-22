# DDEVのPHPを更新する手順

作成日：2026-10-22  
最終更新日：2026-10-22

他のDDEVプロジェクトへコピーして使う、ターミナル操作の手順書です。
PHPの現在の系統を維持し、修正版へ更新します。

この資料のDockerfileは、DDEV v1.25.4・Debian 13（trixie）・arm64の環境で、PHP 8.4.24から8.4.27へ更新できた実例です。
別環境では、以下の確認とシミュレーションを行ってから適用してください。

## 1. 対象プロジェクトへ移動する

Docker Desktopなど、普段利用しているDocker環境を起動します。
ターミナルで、更新したいプロジェクトのフォルダへ移動してください。
以下のパスは、自分のプロジェクトの絶対パスに置き換えます。

```bash
cd "/対象プロジェクトの絶対パス"
```

現在地と、DDEV・PHPのバージョンを確認します。

```bash
pwd
ddev --version
ddev php --version
```

PHP 8.4系なら、例えば以下の表示になります。

```text
PHP 8.4.24 (cli)
```

すでに目的の修正版以上なら、この資料の古い版へ下げる必要はありません。

## 2. 更新先と環境を確認する

2026-10-21公開の脆弱性修正版は以下です。将来の「最新版」を示す表ではありません。

| 現在の系統 | 今回の脆弱性の修正版 |
|---|---|
| PHP 8.2 | 8.2.35 |
| PHP 8.3 | 8.3.36 |
| PHP 8.4 | 8.4.27 |
| PHP 8.5 | 8.5.12 |

出典：[PHP公式の2026年リリース情報](https://www.php.net/archive/2026.php)

以下の手順はPHP 8.4専用です。8.2・8.3・8.5・旧バージョンの場合は、同じDockerfileをそのまま使わず、その系統の配布パッケージと依存関係を確認してください。
PHP 8.1以前は、サポート中の系統への移行または提供元の修正状況を別途確認します。

コンテナのOSも確認します。

```bash
ddev exec cat /etc/os-release
```

今回の実例はDebian 13（trixie）です。異なるOS・DDEVイメージの場合は、以下のパッケージ版を流用せず、対象環境で利用できる修正版を確認します。

プロジェクト内の`.ddev/config.yaml`を開き、PHP 8.4を使っていることを確認します。

```yaml
php_version: "8.4"
```

この設定は`8.4`のままです。`8.4.27`へ書き換えません。
`.ddev/config.local.yaml`などにも`php_version`が指定されている場合は、その上書き設定も確認してください。

## 3. 配布元に修正版があるか確認する

パッケージ一覧だけを更新します。このコマンドではPHP本体は更新しません。

```bash
ddev exec -u root apt-get update
```

CLI・ブラウザ用PHP・共通パッケージの配布状況を確認します。

```bash
ddev exec apt-cache policy php8.4-cli php8.4-fpm php8.4-common
```

確認済みの環境では、修正版のパッケージ名に以下のバージョンが表示されました。

```text
8.4.27-1~deb13u1
```

この文字列全体が、次のDockerfileで使うパッケージ版です。
表示されない場合は、そのまま次へ進まず、配布元の更新状況と利用可能な修正版を確認してください。

## 4. 更新をシミュレーションする

以下は実際の更新を行わず、依存関係を確認するコマンドです。
複数行をまとめて貼り付けて実行できます。

```bash
ddev exec apt-get -s install --only-upgrade --no-install-recommends \
  php8.4-cli=8.4.27-1~deb13u1 \
  php8.4-fpm=8.4.27-1~deb13u1 \
  php8.4-common=8.4.27-1~deb13u1
```

実例では関連する拡張機能も含め18パッケージが更新対象となり、追加・削除は0件でした。
件数はプロジェクトごとに異なります。依存関係エラーや想定外の削除が出た場合は、内容を確認してから進めます。

## 5. 更新用Dockerfileを作成する

保存先は、対象プロジェクト内の以下の場所です。

```text
.ddev/web-build/Dockerfile
```

拡張子なしのファイルです。既存の`Dockerfile.example`は残します。
すでに`Dockerfile`がある場合は内容を確認し、既存の処理を丸ごと置き換えないでください。別の`Dockerfile.*`でPHPのインストールを行っていないかも確認します。

ターミナルから編集画面を開きます。

```bash
nano .ddev/web-build/Dockerfile
```

新規ファイルには以下を貼り付けます。コードを囲むバッククォート3つは貼り付けません。

```dockerfile
# DDEVのPHP 8.4を8.4.27へ更新する
# Created: 2026-10-22
# Last updated: 2026-10-22

ARG PHP84_PACKAGE_VERSION=8.4.27-1~deb13u1

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
        --only-upgrade \
        --no-install-recommends \
        -o Dpkg::Options::="--force-confold" \
        "php8.4-cli=${PHP84_PACKAGE_VERSION}" \
        "php8.4-fpm=${PHP84_PACKAGE_VERSION}" \
        "php8.4-common=${PHP84_PACKAGE_VERSION}" \
    && apt-get clean
```

コメントの日付は、そのプロジェクトで実際に作成・更新した日に合わせてください。
既存ファイルへ追記する場合、`Created`は元の値を保持します。

- `cli`：ターミナルから実行するPHP
- `fpm`：ブラウザからのリクエストを処理するPHP
- `common`：共通パッケージ
- `--only-upgrade`：指定した既存パッケージの更新を行う
- `--force-confold`：変更されている既存の設定ファイルを保持する

DDEVがベースイメージを用意するため、この追加用Dockerfileに`FROM`は不要です。

保存操作は次の順です。CommandキーではなくControlキーを使います。

1. `Control + O`（英字のオー）で保存する
2. 保存先のファイル名を変えずに`Enter`を押す
3. `Control + X`で編集画面を閉じる

## 6. DDEVを再起動する

ターミナルのプロンプト（`%`など）が戻ったら実行します。

```bash
ddev restart
```

Dockerfileを使ってイメージが構築されます。処理中は対象プロジェクトのページへ一時的にアクセスできなくなります。
成功のメッセージが表示され、プロンプトが戻るまで待ってください。

## 7. CLI・FPMと画面を確認する

```bash
ddev php --version
```

期待する表示：

```text
PHP 8.4.27 (cli)
```

ブラウザ用のPHPも確認します。

```bash
ddev exec php-fpm8.4 --version
```

期待する表示：

```text
PHP 8.4.27 (fpm-fcgi)
```

両方を確認したら、普段のプロジェクトURLをブラウザで開き、ページ表示や主要な操作に問題がないか確認します。
URLは以下でも確認できます。

```bash
ddev describe
```

## 8. Dockerfileを残しておく

追加したDockerfileは削除せず、そのプロジェクトの設定として保持します。
DDEVのコンテナを再作成した場合も、イメージ構築時に更新処理が適用されます。
チームで同じ環境を再現したい場合は、内容を確認したうえでDockerfileをGit共有します。

この設定はPHP 8.4.27を指定しています。将来の修正版へ自動で追従するものではないため、更新時は配布状況を確認して指定を見直します。
配布元がこのパッケージ版を提供しなくなった場合も、利用可能な修正版への指定変更が必要です。

## うまくいかない場合

### 再起動しても古い版のまま

Dockerfileのファイル名・保存場所・保存できているかを確認します。
キャッシュを使わずに再構築する場合は以下を実行します。

```bash
ddev restart --no-cache
```

### Version was not foundなどのエラー

配布元に指定したパッケージ版がありません。手順3へ戻って確認してください。
OSやPHP系統の違いを確認し、古いバージョンの強制インストールは行いません。

### 依存関係エラーや起動失敗

エラー出力を保存し、既存のDockerfile・追加拡張機能との関係を確認します。
再構築を繰り返す前に、手順4のシミュレーション結果と照合してください。

### 今回追加した設定を一時的に外したい

今回新規作成したファイルだけなら、ファイル名を`Dockerfile`から`php84-update.disabled`へ変更し、`ddev restart`します。
`Dockerfile.backup`などの名前では、追加Dockerfileとして読み込まれるため無効化できません。
既存のDockerfileへ追記した場合は、今回追記したブロックだけを取り除きます。
この操作で元のイメージのPHPへ戻る可能性があるため、バージョンを再確認し、修正版への更新を改めて検討します。

## 適用範囲と確認記録

- 適用先は、そのDockerfileを置いたDDEVプロジェクトだけです。
- Mac本体のPHP・他のDDEVプロジェクト・本番サーバーのPHPは別管理です。
- 実例ではCLI・FPMともにPHP 8.4.27になったことを確認済みです。
- 実例の更新後の画面表示は、この手順書作成時点では未確認です。
- 判断に迷った点・不明な点：他プロジェクトのOS・追加設定・PHP系統は未確認です。移植先で手順1〜4を行ってください。

## 公式資料

- [PHP公式：2026年リリース情報](https://www.php.net/archive/2026.php)
- [DDEV公式：追加Dockerfileによるイメージのカスタマイズ](https://docs.ddev.com/en/stable/users/extend/customizing-images/#adding-extra-dockerfiles-for-webimage-and-dbimage)
