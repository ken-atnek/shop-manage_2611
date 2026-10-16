---
name: kumanichi-admin-design
description: Design or update Figma screens and shared UI specifications for the kumanichi-mall-cp EC administration project. Use for Master or Client admin screens, shared components, form controls, design tokens, and screen-level design reviews in this project. Do not use for unrelated websites or HTML implementation alone.
---

# 熊日モール管理画面デザイン

`kumanichi-mall-cp`の管理画面を、共通UIの一貫性を保ちながらFigmaで設計する。

## 仕様の確認

作業前にプロジェクトの`AGENTS.md`を確認し、次のうち今回の変更に関係する資料を読む。

- 画面・機能・権限：`docs/SPEC.md`
- 色・文字・余白・サイズ：`DESIGN_TOKENS.md`
- Figma構造とデザイン方針：`docs/design/DESIGN.md`
- レイアウト検討：`docs/coding/LAYOUT_TEMPLATE.md`
- HTML実装への引き渡し：`docs/coding/IMPLEMENTATION_REFERENCE.md`
- 過去の判断：`docs/MEMORY.md`

資料と最新のユーザー指示が異なる場合は、最新指示を優先し、確定後に資料を更新する。

## 管理画面種別

対象を最初に判別する。

- `Master`：運営側のマスター管理画面
- `Client`：クライアント（代理店）管理画面

対象がデザイン判断へ影響するのに判別できない場合のみ確認する。

両者で基本レイアウト、フォーム部品、操作方法、タイポグラフィ、スペーシングを共有する。色、ロゴ、ナビゲーション、コンテンツ、権限だけを必要に応じて切り替える。

## Figma作業

- Figmaへ書き込む前に`figma-use`スキルを読み、`use_figma`を使用する
- 変更前に対象フレーム、親子構造、既存Variables、Styles、Componentsを確認する
- 既存の共通コンポーネントを優先し、画面内へ類似部品を直接作らない
- 画面と主要ブロックにはAuto Layoutを使い、リサイズ時の挙動を設定する
- `Master` / `Client`の色差は、原則として同じSemantic TokenのVariables Modeで管理する
- 色だけで管理画面種別や状態を伝えず、管理画面名、ラベル、文言を併用する
- 画面単位または主要セクション単位で反映し、スクリーンショット確認後に次へ進む

## 共通フォーム部品

Input、Select、Textarea、Checkbox、Radio、Date Picker、File Uploadは共通コンポーネントとして扱う。

新しい部品が必要な場合は、既存部品のPropertyやVariantで対応できるかを先に確認する。新規作成する場合は、今回必要な最小範囲で次を定義する。

- サイズ、ラベル、必須・任意表示
- Default / Hover / Focus / Filled / Disabled / Read only / Error
- 補助説明、エラー文、文字数表示
- アイコン、単位、幅、長文時の挙動

確定した値はFigma Variables / Stylesと`DESIGN_TOKENS.md`で名称を揃える。未確定値をスキル内で固定しない。

## 変更の範囲

- 既存デザインへ自然に組み込み、依頼されていない全面変更をしない
- 一度に複数画面を大幅変更せず、最小単位で確認する
- HTMLを自動生成・書き出ししない
- 実装難易度を理由にデザインを簡略化しない
- 確定した仕様だけを該当Markdownと`docs/MEMORY.md`へ反映する

## 完了確認

- 対象が`Master` / `Client`のどちらか明確か
- 共通コンポーネントを再利用しているか
- 必要な状態と権限差が表現されているか
- Auto Layoutとリサイズ設定に崩れがないか
- スクリーンショットで全体と主要状態を確認したか
- 確定した判断がMarkdownへ反映されているか
