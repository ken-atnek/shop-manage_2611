# DESIGN_TOKENS.md

## 位置づけ

ECサイト管理画面で共通利用するデザイントークンの管理ファイル。

初期値は画面制作を進めるための仮採用とし、Figma上で検証しながら調整する。トークンを変更した場合は、対象画面と変更理由を`docs/MEMORY.md`へ記録する。

## カラー

共通のニュートラルカラーと状態色は両管理画面で揃える。ブランド・アクセントに関する値のみ、`Master`と`Client`のModeで切り替える想定とする。

| トークン | 用途 | 値 |
|---|---|---|
| `color-bg-page` | ページ背景 | 未確定 |
| `color-bg-surface` | カード・パネル背景 | 未確定 |
| `color-bg-subtle` | 補助背景 | 未確定 |
| `color-border-default` | 標準ボーダー | 未確定 |
| `color-border-strong` | 強調ボーダー | 未確定 |
| `color-text-primary` | メインテキスト | 未確定 |
| `color-text-secondary` | 補助テキスト | 未確定 |
| `color-text-muted` | 非活性・注釈 | 未確定 |
| `color-action-primary` | 主要操作 | 未確定 |
| `color-action-danger` | 削除・危険操作 | 未確定 |
| `color-status-success` | 成功・公開中 | 未確定 |
| `color-status-warning` | 警告・注意 | 未確定 |
| `color-status-error` | エラー | 未確定 |
| `color-status-info` | 情報 | 未確定 |

### 管理画面種別ごとのカラーモード

| トークン | Master | Client |
|---|---|---|
| `color-brand-primary` | `#28384D` | `#9A4F2C` |
| `color-brand-subtle` | `#EFF3F7` | `#F8F1ED` |
| `color-brand-on-primary` | `#FFFFFF` | `#FFFFFF` |
| `color-nav-active` | `#28384D` | `#9A4F2C` |

種別ごとに別の色名を増やさず、同じ用途のトークンをModeで切り替える。

上記はA案として仮採用。最初の画面で面積比、視認性、状態色との干渉を確認してから本決定する。

## タイポグラフィ

| トークン | 用途 | 値 |
|---|---|---|
| `font-family-base` | 日本語本文 | 未確定 |
| `font-size-caption` | 注釈・補助情報 | 未確定 |
| `font-size-body` | 本文・フォーム | 未確定 |
| `font-size-heading-sm` | 小見出し | 未確定 |
| `font-size-heading-md` | セクション見出し | 未確定 |
| `font-size-heading-lg` | ページ見出し | 未確定 |

## スペーシング

4pxまたは8pxを基準とするか、既存Figmaを確認して決定する。

| トークン | 値 |
|---|---|
| `space-1` | 未確定 |
| `space-2` | 未確定 |
| `space-3` | 未確定 |
| `space-4` | 未確定 |
| `space-5` | 未確定 |
| `space-6` | 未確定 |

## サイズ・形状

| トークン | 用途 | 値 |
|---|---|---|
| `control-height-sm` | 小サイズの入力・ボタン | 未確定 |
| `control-height-md` | 標準の入力・ボタン | 未確定 |
| `control-height-lg` | 大サイズの入力・ボタン | 未確定 |
| `control-padding-x` | 入力・ボタンの左右余白 | 未確定 |
| `control-gap` | アイコンとテキストの間隔 | 未確定 |
| `radius-sm` | 入力・小要素 | 未確定 |
| `radius-md` | カード・パネル | 未確定 |
| `shadow-surface` | カード・浮遊要素 | 未確定 |

## フォーム部品

共通フォーム部品の数値・色は、最初の画面で検証してから確定する。

| 項目 | 対象 | 値 |
|---|---|---|
| 標準高さ | Input / Select / Button | 未確定 |
| 標準文字サイズ | 入力値・選択値 | 未確定 |
| ラベル文字サイズ | 項目ラベル | 未確定 |
| 補助文字サイズ | 説明・エラー | 未確定 |
| 標準ボーダー | Default | 未確定 |
| フォーカス | Focus | 未確定 |
| エラー | Error | 未確定 |
| 無効背景 | Disabled / Read only | 未確定 |

確定値は画面単位ではなく、このファイルとFigma Variables / Stylesの両方へ反映する。

## Figma運用

- 色、数値、文字スタイルは可能な限りFigma Variables / Stylesへ登録する
- 同じ用途の値を画面ごとに増やさない
- Light / Darkなどのモードは、要件が確定してから追加する
- トークン名は見た目ではなく用途を基準にする
- 既存トークンの変更は、影響範囲を確認してから行う
- 管理画面種別の違いは、原則としてVariablesの`Master` / `Client` Modeで管理する
- 共通コンポーネントを種別ごとに複製しない

