# AGENTS.md

## プロジェクトの目的

熊日モールのMaster / Client管理画面を、1つのデザイン実装プロジェクトで制作する。制作用PHP（共通部品のインクルードのみ）・Sassをブラウザで確認しながら共通UIを固め、バックエンド担当者へ引き渡す。

Figmaは必須工程とせず、必要な場合のみ使用する。

## このリポジトリの担当範囲

- フロント（デザイン実装：Ken・Codex・Claude）とバックエンド担当者が同じリポジトリで作業する
- 以下のルールは、特に記載がない限りフロントの作業に適用する。バックエンド用のルールは `docs/backend/README.md` に置く（下記）
- 資料の置き場所・担当・仕様として正の資料は `docs/README.md` を参照する
- バックエンド（Laravel）のコードはルートの `backend/`、資料は `docs/backend/` に置く。フロントのCodexはどちらも依頼なく編集しない
- バックエンドの作業では、作業前に `docs/backend/README.md` の「AI作業のルール」を読み、それに従う。このファイルの以降のルールはフロントの作業用で、バックエンドの作業には適用しない（2026-10-21、QA No.10）

## 基本ワークフロー

1. 既存ファイル、フォルダ構成、関連資料を確認する
2. 要件、画面仕様、状態、操作、デザイン方針を整理する
3. `docs/`内のMarkdownを更新し、資料間の矛盾を確認する
4. Codexがプロンプトの依頼範囲で制作用PHP（HTML部分）・Sassを作成し、ユーザーが必要な箇所を調整し、Claudeが確認する
5. Sassをコンパイルし、ブラウザで表示と操作を確認して必要な範囲だけ調整する
6. 確定した仕様と制作用PHP・Sass・生成CSSをバックエンド担当者へ引き渡せる形に整理する（静的HTMLは納品物に含めない）

## Codexの役割

- 要件整理と仕様策定を支援する
- 曖昧な仕様、重複、矛盾を洗い出す
- 確定した内容をMarkdownへ反映する
- 既存コードとブラウザ表示を確認し、共通UIの一貫性を保つ
- Figmaを使用する場合は既存構造を確認し、`use_figma`で反映する
- 依頼された範囲外のPHP・HTMLを作成・変更しない
- ユーザーから依頼されていない範囲を変更しない

## 必須スキル

- このプロジェクトの管理画面デザイン、共通UI、フォーム部品、デザイントークンを扱う場合は、作業前に`$kumanichi-admin-design`を使用する
- Figmaへ書き込む場合は、あわせて`figma-use`を使用する

## 最初に確認する資料

0. `docs/README.md`（資料の目次と担当）
1. `docs/SPEC.md`
2. `DESIGN_TOKENS.md`
3. `docs/design/DESIGN.md`
4. `docs/coding/LAYOUT_TEMPLATE.md`
5. `docs/coding/IMPLEMENTATION_REFERENCE.md`
6. `docs/MEMORY.md`

資料間で内容が異なる場合は、案件固有の仕様とユーザーの最新指示を優先する。

## 作業ルール

- 回答と作業報告は日本語で簡潔に行う
- 現状確認、方針、変更内容の順で進める
- 最小修正を優先し、勝手に大幅な設計変更をしない
- 修正箇所と保存先を明確にする
- 複数画面の変更は段階的に進める
- Figmaを使用する場合はオートレイアウトを適切に使用する
- 実装難易度を理由にデザインを簡略化しない
- フロントの作業では、バックエンド処理やDB連携を実装しない
- 旧フォルダ`/Users/ken/site_data/__web_design/kumanichi-mall-cp`はバックアップとして残し、明示的な依頼なく削除しない
- 依頼内容に、仕様資料・既存コード・ユーザーの過去の決定と食い違う点や、判断に必要な情報の不足があれば、作業前に指摘する。軽微で作業を進められる場合は、仮の判断で進めて完了報告で明記する
- 完了報告には「判断に迷った点・不明な点」を必ず含める（なければ「なし」と書く）

### Gitコミット

- 作業が終わってもコミットしない。ユーザーが内容を確認・調整し、Claudeが確認したあと、ユーザーから「コミットして」と依頼されたときだけコミットする
- 依頼にコミットメッセージが含まれている場合は、そのとおりに使う。含まれていない場合は、変更内容を日本語の1行で要約して作る
- コミットする前に`git status`と`git diff --stat`を確認し、確認した範囲のファイルだけを`git add`する。依頼範囲外の変更や、Git管理外にするファイル（`.ddev/config.local.yaml`・静的HTMLなど）は含めない
- 資料の変更とコードの変更は、[資料の一覧と担当](docs/README.md#資料の一覧と担当)のルールに従い、なるべく別のコミットにする
- ユーザーは、コミット補助スクリプト（`~/site_data/art-factory-ai/infra/ai-ken-commit.sh`）を使ってよい。AI-KEN（ローカルLLM）がメッセージを下書きし、ユーザーが確認して確定する。push もスクリプトの y/n で確認する。この案件では AI-KEN へのソースコードの入力を許可済み（2026-10-22、Ken）。スクリプトはユーザーが端末で実行し、AI は実行しない。
- `push`・`merge`・`rebase`・相手のブランチの取り込みは、依頼があったときだけ行う
- コミットしたら、コミット番号・メッセージ・含めたファイルを報告する

## PHP・HTML実装について

- 制作の流れ：Codexがプロンプトの依頼範囲で制作用PHP（HTML部分）・Sassを作成し、ユーザーが必要な箇所を調整し、Claudeが確認する。ユーザーが調整した箇所は、Codexが次回以降の作業で元に戻さない
- Codexはプロンプトで依頼された画面・範囲だけを作成・変更する。依頼のない画面のPHP・HTMLを作らない
- Clientの制作・編集元は`shop-partner/orders.php`・`shop-partner/products.php`と`shop-partner/includes/inc_header.php`・`inc_sidebar.php`。固有Sassは`shop-partner/assets/scss/order.scss`・`shop-partner/assets/scss/products.scss`。PHPは共通部品の読み込み・現在地・静的出力時のリンク切り替えと制作確認用の表示分岐に使い、Laravelや業務処理は実装しない
- バックエンド担当者への納品はPHPのみとし、静的HTMLは納品・Git管理しない（`.gitignore`で`shop-partner/*.html`・`shop-master/*.html`を除外）。PHPが動かない環境で見せる必要がある場合だけ、`ddev exec php scripts/export-html.php`で書き出す。HTMLの組み立てにNodeは使わない
- 仕様資料では、実装時に必要な要素、状態、命名候補を整理する
- `form`、`input`、`button`、`label`など、本来の用途に合う要素を提案する
- 不要な抽象化や全面リファクタは提案しない
- 本番サーバーのPHPは8.4.27へ更新予定（現在8.3.35、2026-10-22 Kenの決定）。切り替えまでは、制作用PHP・資料に書くコード例を含め、PHP 8.3互換で書く

## Sassと引き渡し

- ルートの`scss/`はreset・変数・既存の関数やmixinなどの読み込み専用とし、各管理画面のSassから相対パスの`@use`で読み込む
- `shop-master/assets/scss/`から`shop-master/assets/css/`へ、`shop-partner/assets/scss/`から`shop-partner/assets/css/`へコンパイルする
- Clientは`common.scss`（共通部分）＋ページ別Sass（受注一覧は`order.scss`、商品一覧は`products.scss`）の構成を継続し、`common.css`→ページ別CSSの順に読み込む。resetは`common.scss`だけで読み込む。`admin.scss`への再編は前提にしない
- クラス名・ネスト・セクションコメントは既存コードを優先する。`.admin-*`／`.order-*`の接頭辞やBEMを強制せず、依頼なく一括改名しない
- サイズは既存の`fz()`・流動フォント設定を使い、余白・幅・罫線などは`px`・`rem`・`em`の直接指定も認める。すべての数値を変数化せず、削除済みのサイズ変数を資料の旧仮案に合わせて復活させない
- Node.js環境でnpmパッケージの`sass`を使用し、`node-sass`は使用しない
- Vite・Astroは現時点では使用しない
- Laravel・Bladeへの組み込みはバックエンド担当者が行う。フロントの作業ではLaravelを構築しない

## HTML・Sassのコメントヘッダー

- 手動作成するHTML・制作用PHP・Sassには、用途のタイトル・`URL`・`Referenced in`・`Created`・`Last updated`を記載したファイル冒頭のコメントヘッダーを付ける
- 新規作成時に記載し、既存ファイルにない場合は、そのファイルを次に修正するときに追加する。`Created`は保持し、修正時は`Last updated`を更新する。日付は`YYYY-MM-DD`とし、不明な作成日を推測しない
- HTMLは`<!doctype html>`の直後に`<!-- ... -->`で、Sassは先頭の`@use`群の直後（なければ先頭。`@charset`があればその後）に`/* ... */`で記載する
- 詳細・テンプレートは[実装連携ガイドのコメント記述ルール](docs/coding/IMPLEMENTATION_REFERENCE.md#コメント記述ルール)を参照する。既存のセクション区切りは維持し、ヘッダー追加だけを理由に依頼範囲外のHTML・Sassを一括変更しない

