# docs の目次

このリポジトリは、フロント（デザイン実装：Ken・Codex・Claude）とバックエンド担当者が一緒に使う。資料を追加・更新するときは、下の担当と置き場所に従う。

## 仕様として正の資料

資料どうしで内容が食い違う場合は、この順で優先する。迷ったら更新前にフロント・バックエンドで確認する。

1. `SPEC.md`：画面一覧と業務の仕様（確定事項・未確定事項）。両担当で参照し、本文はフロントが書く
2. `design/` の画面資料（例：`design/CLIENT_ORDER_LIST.md`）：画面ごとの構成・項目・操作・状態
3. `../DESIGN_TOKENS.md`：色・文字・余白などのデザイントークン

`MEMORY.md` は判断の履歴で、仕様ではない。古い判断も残っているため、現在の仕様は上の資料で確認する。

## 資料の一覧と担当

| 場所 | 内容 | 担当 |
|---|---|---|
| `../AGENTS.md` | AIエージェント（Codex等）向けの作業ルール | フロント |
| `SPEC.md` | 画面一覧・業務仕様・制作手順 | フロント |
| [QA_BACKEND.md](QA_BACKEND.md) | 質問・フロントの案・バックエンド回答・フロントの対応の経緯 | 両方（欄ごとに担当を分ける） |
| `MEMORY.md` | フロントの作業・判断の履歴 | フロント |
| `design/DESIGN.md` | デザイン方針 | フロント |
| `design/CLIENT_ORDER_LIST.md` | Client受注一覧の画面資料 | フロント（業務仕様の変更はQAで確認） |
| `design/CLIENT_PRODUCT_LIST.md` | Client商品一覧の画面資料（確定事項・案・未確定事項・制作予定） | フロント（業務仕様の変更はQAで確認） |
| `design/CLIENT_PRODUCT_FORM.md` | Client商品詳細（登録・編集）の画面資料（確定事項・案・未確定事項・制作予定） | フロント（業務仕様の変更はQAで確認） |
| [design/CLIENT_STORE_LIST.md](design/CLIENT_STORE_LIST.md) | Client店舗一覧の画面資料（完成（引き渡し前）。表示・状態・制作記録・申し送り） | フロント（業務仕様の変更はQAで確認） |
| [design/CLIENT_STORE_FORM.md](design/CLIENT_STORE_FORM.md) | Client店舗の登録・編集と送料設定の画面資料（完成（引き渡し前）。表示・操作・制作記録・申し送り） | フロント（業務仕様の変更はQAで確認） |
| [design/CLIENT_CUSTOMER_LIST.md](design/CLIENT_CUSTOMER_LIST.md) | Client顧客一覧の画面資料（確定事項・案・未確定事項・制作予定） | フロント（業務仕様の変更はQAで確認） |
| [design/CLIENT_CUSTOMER_DETAIL.md](design/CLIENT_CUSTOMER_DETAIL.md) | Client顧客詳細の画面資料（確定事項・案・未確定事項・制作予定） | フロント（業務仕様の変更はQAで確認） |
| [design/CLIENT_SALES.md](design/CLIENT_SALES.md) | Client集計の画面資料（確定事項・案・未確定事項・制作予定） | フロント（業務仕様の変更はQAで確認） |
| [design/MASTER_ORDER_LIST.md](design/MASTER_ORDER_LIST.md) | Master受注一覧の画面資料（Clientとの違い・確定事項・未確定事項。制作前） | フロント（業務仕様の変更はQAで確認） |
| [design/COMMON_LOGIN.md](design/COMMON_LOGIN.md) | Master・Client共通のログイン・パスワード再設定・確認コード（Masterのみ）の画面資料（確定事項・状態・文言・制作記録） | フロント（業務仕様の変更はQAで確認） |
| `design/reference/` | 提供ロゴ等の参照資料 | フロント |
| `design/proposals/` | 画像モック・生成記録（Git管理外のローカル資料） | フロント |
| `coding/LAYOUT_TEMPLATE.md` | 共通レイアウトの構造 | フロント |
| `coding/IMPLEMENTATION_REFERENCE.md` | 制作用PHP・Sassの書き方と、バックエンドへの引き渡し方 | フロント |
| `coding/HANDOFF_CLIENT_COMMON.md` | Client共通のBlade組み込み申し送り（レイアウト・head・サイドバー・検索・空状態・ページ送り・読み込み順） | フロント |
| `coding/HANDOFF_CLIENT_ORDER_LIST.md` | Client受注一覧固有の申し送り（KPI・ステータス保存・メモ・入稿画像・order.jsの依存先） | フロント |
| `coding/HANDOFF_CLIENT_PRODUCT_LIST.md` | Client商品一覧固有の申し送り（テーブル・状態・画像・編集リンク・検索細則・未確定事項） | フロント |
| `coding/HANDOFF_CLIENT_PRODUCT_FORM.md` | Client商品フォーム固有の申し送り（新規／編集・送信項目・画像・エラー・店舗JSON・JS依存先・未確定事項） | フロント |
| [coding/HANDOFF_CLIENT_STORE_LIST.md](coding/HANDOFF_CLIENT_STORE_LIST.md) | Client店舗一覧固有の申し送り（7列・集計・状態・追加／編集導線・現在地・未確定事項） | フロント |
| [coding/HANDOFF_CLIENT_STORE_FORM.md](coding/HANDOFF_CLIENT_STORE_FORM.md) | Client店舗フォーム固有の申し送り（送信・料金保持・DB対応・エラー・規格JSON・JS依存先・検証範囲） | フロント |
| [coding/HANDOFF_CLIENT_SALES.md](coding/HANDOFF_CLIENT_SALES.md) | Client集計固有の申し送り（集計条件・期間・操作・状態・CSV・ページ送り・バックエンドで決めてほしいこと・未確定事項） | フロント |
| [coding/HANDOFF_CLIENT_CUSTOMER_LIST.md](coding/HANDOFF_CLIENT_CUSTOMER_LIST.md) | Client顧客一覧固有の申し送り（7列・検索・集計条件・状態・ページ送り・バックエンドで決めてほしいこと・未確定事項） | フロント |
| [coding/HANDOFF_CLIENT_CUSTOMER_DETAIL.md](coding/HANDOFF_CLIENT_CUSTOMER_DETAIL.md) | Client顧客詳細固有の申し送り（基本情報・購入状況・注文履歴・届け先判定・状態・バックエンドで決めてほしいこと・未確定事項） | フロント |
| [coding/HANDOFF_COMMON_LOGIN.md](coding/HANDOFF_COMMON_LOGIN.md) | Master・Client共通のログイン系5画面の申し送り（状態・フォーム属性・JS依存先・文言・ヘッダー・バックエンドで決めてほしいこと・未確定／未確認事項） | フロント |
| [coding/DDEV_PHP_UPDATE_GUIDE.md](coding/DDEV_PHP_UPDATE_GUIDE.md) | フロントのDDEVのPHPを修正版へ更新する手順（追加Dockerfile `.ddev/web-build/Dockerfile`） | フロント |
| `backend/` | API・DB・ルーティング・Blade組み込み等の資料 | バックエンド |
| `../backend/`（リポジトリのルート） | Laravel等のバックエンドのコード | バックエンド |

## 追加するときのルール

- `docs/backend/`・`backend/`はバックエンドだけが書く。中の構成はバックエンド担当者が決める
- フロントの資料は `design/`（見た目・画面）と `coding/`（実装・引き渡し）に置く
- 画面資料は `design/` 直下に `CLIENT_画面名.md`・`MASTER_画面名.md` で置く。Master・Client共通の画面は `COMMON_画面名.md` とする（2026-10-23、ログイン画面で追加）
- `SPEC.md`の本文はフロントだけが書く。バックエンドが仕様を変えたい・確認したいときは[QA_BACKEND.md](QA_BACKEND.md)へ書く。決まった業務仕様（保存方式・ステータスの選択肢・集計条件など）はフロントがSPEC本文の確定事項と該当する画面資料へ反映する。QA・`docs/backend/`・`MEMORY.md`だけに書かない
- `QA_BACKEND.md`は両方が書く。各項目の「回答（バックエンド）」はバックエンドだけ、それ以外はフロントが書く。ただし新しい質問はどちらも末尾へ新しい番号で追加でき、バックエンドからは「質問（バックエンド）」としてその質問欄を書く
- フロントからの確認事項のうち、バックエンドの判断で決めて実装できるもの（保存値・データの持ち方・実装の時期など）は`QA_BACKEND.md`に追加しない。`SPEC.md`の該当する節と画面の申し送り（`coding/HANDOFF_*.md`）に「バックエンドで決めてほしいこと」として、フロントの前提と並べて書く。決めた内容はバックエンドからフロントへ知らせてもらい、フロントがSPEC・画面資料へ反映する。回答がないと画面の仕様を決められないものだけを`QA_BACKEND.md`へ書く（2026-10-22、Kenの決定）
- `docs/design/`・`docs/coding/`・`shop-partner/`・`shop-master/`・`scss/`・`index.html`・`AGENTS.md`はフロントだけが書く
- 相手の担当の資料を変える必要がある場合は、直接書き換えずに相手へ確認する
- 作業を始める前に相手のブランチを取り込む。資料の変更とコードの変更はなるべく別のコミットにする

### フロント側の Git の運用（2026-10-22、フロント担当の決定。バックエンド担当者に通達済み）

- フロント側では、ローカルのコミットまで Claude Code が行う。Codex などほかの AI はコミットしない
- push はフロント担当（Ken）が中身を確認してから行う
- merge はフロント担当（Ken）が行う
- バックエンド側の AI の使い方は、[docs/backend/README.md の「役割分担」](backend/README.md#役割分担2026-10-21バックエンド担当者の決定)に従う
