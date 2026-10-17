# AGENTS.md

## プロジェクトの目的

熊日モールのMaster / Client管理画面を、1つのデザイン実装プロジェクトで制作する。静的HTML・Sassをブラウザで確認しながら共通UIを固め、バックエンド担当者へ引き渡す。

Figmaは必須工程とせず、必要な場合のみ使用する。

## 基本ワークフロー

1. 既存ファイル、フォルダ構成、関連資料を確認する
2. 要件、画面仕様、状態、操作、デザイン方針を整理する
3. `docs/`内のMarkdownを更新し、資料間の矛盾を確認する
4. HTMLはユーザーが手打ちし、Codexは依頼された範囲でコードの提案・修正を支援する
5. Sassをコンパイルし、ブラウザで表示と操作を確認して必要な範囲だけ調整する
6. 確定した仕様と静的HTML・Sass・生成CSSをバックエンド担当者へ引き渡せる形に整理する

## Codexの役割

- 要件整理と仕様策定を支援する
- 曖昧な仕様、重複、矛盾を洗い出す
- 確定した内容をMarkdownへ反映する
- 既存コードとブラウザ表示を確認し、共通UIの一貫性を保つ
- Figmaを使用する場合は既存構造を確認し、`use_figma`で反映する
- 依頼なくHTMLを自動生成・書き出ししない
- ユーザーから依頼されていない範囲を変更しない

## 必須スキル

- このプロジェクトの管理画面デザイン、共通UI、フォーム部品、デザイントークンを扱う場合は、作業前に`$kumanichi-admin-design`を使用する
- Figmaへ書き込む場合は、あわせて`figma-use`を使用する

## 最初に確認する資料

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
- バックエンド処理やDB連携は実装しない
- 旧フォルダ`/Users/ken/site_data/__web_design/kumanichi-mall-cp`はバックアップとして残し、明示的な依頼なく削除しない

## HTML実装について

- HTMLはユーザーが手打ちで実装する
- Codexは依頼なくHTMLを自動生成・書き出ししない
- 仕様資料では、実装時に必要な要素、状態、命名候補を整理する
- `form`、`input`、`button`、`label`など、本来の用途に合う要素を提案する
- 不要な抽象化や全面リファクタは提案しない

## Sassと引き渡し

- 共通Sassはルートの`scss/`へ置き、各管理画面のSassから`@use`で読み込む
- `shop-master/assets/scss/`から`shop-master/assets/css/`へ、`shop-partner/assets/scss/`から`shop-partner/assets/css/`へコンパイルする
- 最初は各管理画面の`admin.css`を1ファイルとし、必要になってからページ別CSSへ分割する
- Node.js環境でnpmパッケージの`sass`を使用し、`node-sass`は使用しない
- Vite・Astroは現時点では使用しない
- Laravel・Bladeへの組み込みはバックエンド担当者が行い、このプロジェクトではLaravelを構築しない

