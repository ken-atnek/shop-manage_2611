# 共通レイアウトテンプレート

## 位置づけ

ECサイト管理画面の共通構造を整理するためのテンプレート。

画面構成が確定するまでは、特定のレイアウトを強制しない。`docs/SPEC.md`と実装済みの共通構造を優先する。Figmaは必要な場合のみ参照する。

## 管理画面の基本構造

```text
管理画面
├── ヘッダー
│   ├── サービス名・ロゴ
│   ├── 補助ナビゲーション
│   └── アカウントメニュー
├── サイドバー
│   ├── 開閉ボタン（Client）
│   └── メインナビゲーション
└── メイン
    ├── パンくず（必要な場合）
    ├── ページヘッダー
    │   ├── ページタイトル・説明
    │   └── 主要操作
    ├── フィルター・検索（必要な場合）
    ├── コンテンツ
    └── ページ下部操作（必要な場合）
```

## HTML構造の参考

以下は共通構造の参考。Clientの制作・編集元は制作用PHPと共通インクルードで、依頼された範囲をCodexが作成しKenが調整する。

```html
<body class="admin-page">
  <header class="admin-header">
    <!-- サービス名 / アカウントメニュー -->
  </header>

  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="sidebar-content">
        <button class="sidebar-toggle" type="button" aria-expanded="true"
          aria-controls="client-navigation" aria-label="メニューを畳む" title="メニューを畳む">
          <!-- panel-left-close / panel-left-open（装飾扱いのSVG） -->
        </button>
        <nav id="client-navigation" aria-label="管理メニュー">
          <!-- 各リンク：Lucide SVG＋.nav-label、同じ名前のtitle。現在地だけaria-current="page" -->
        </nav>
      </div>
    </aside>

    <main class="admin-main">
      <header class="page-header">
        <div>
          <h1>ページタイトル</h1>
          <p>ページの説明</p>
        </div>
        <div class="page-actions">
          <!-- 主要操作 -->
        </div>
      </header>

      <section class="page-content">
        <!-- ページ固有の内容 -->
      </section>
    </main>
  </div>
</body>
```

## Clientサイドバー（確定、2026-10-19）

- PC（1194pxより広い）：18remの開いた状態を初期値とし、6.4remへ畳むと本文幅が広がる。保存した開閉状態をページ間で共有する
- タブレット（1194px以下、縦横共通）：ページを開くたびに6.4remへ畳む。開くと`.sidebar-content`を18remで絶対配置し本文に重ねる。`.admin-sidebar`の確保幅は6.4remのまま、開いた状態は保存しない
- Esc・本文側クリック・開閉ボタンで重ね表示を閉じ、開閉ボタンへフォーカスを戻す。ナビの境界は1px線、影なし。畳んでも文字は読み上げに残し、各リンクのtitle・現在地・白いフォーカス枠を維持する
- body・`.admin-main`の`min-width: min-content`とサイドバーの固定幅を保つ。縦横ともページ全体でスクロールし、一覧内部にスクロールを作らない。ヘッダー・サイドバーの面をページの右端・下端まで続ける
- 共通Sassと`common.js`で制御し、両PHPのheadに同一の初期化スクリプトをCSSより前に置く。依存class・保存キー・読み込み順は[申し送り§9](HANDOFF_CLIENT_ORDER_LIST.md#9-jsが依存しているclass属性id)を参照

## 画面ごとに確認する項目

- ページタイトルと説明
- 主要操作の位置と優先順位
- 検索、絞り込み、並び替え
- 一覧・詳細・編集の関係
- 保存、キャンセル、削除の配置
- 空状態、読み込み中、エラー状態
- モーダル、ドロワー、確認ダイアログ
- 狭い画面でのサイドバーとテーブルの扱い

## 注意

- 共通領域の構造とスタイルを揃え、共通Sassを再利用する
- ブラウザで画面幅を変え、レイアウトの挙動を確認する
- 固定配置は必要な箇所だけに限定する
- 画面固有の仕様は`docs/SPEC.md`へ記載する
- デザイントークンは`DESIGN_TOKENS.md`を参照する
- class名は実装開始時に既存コードへ合わせて調整してよい

