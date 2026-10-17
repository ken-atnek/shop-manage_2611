# backend

バックエンド（Laravel）のコードを置くフォルダ。フロント（デザイン実装）と同じリポジトリで作業するため、Laravel等のバックエンドのファイルはこのフォルダの中だけに置き、リポジトリのルートには広げない。

- 資料は `../docs/backend/` に置く（目次は `../docs/README.md`）
- フロントから引き渡すもの：制作用PHP（`../shop-partner/` 等のページと `includes/` の共通部品）・Sass・生成CSS。書き方と引き渡し方は `../docs/coding/IMPLEMENTATION_REFERENCE.md`
- 業務仕様は `../docs/SPEC.md` と画面資料（`../docs/design/`）が正

## バックエンド担当者と決めること

- DDEVの構成：現在のDDEVはリポジトリのルートをドキュメントルートにしている（フロントの制作確認用）。Laravelの `backend/public` をどう表示するか（別のDDEVプロジェクト、設定の切り替え等）
- 生成CSS・アセットをLaravelの `public/` へどう取り込むか（コピー、Sassの出力先の変更等）
- フロントのAI作業（Codex等）はこのフォルダを依頼なく編集しない
