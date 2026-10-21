# backend

バックエンド（Laravel）のコードを置くフォルダ。フロント（デザイン実装）と同じリポジトリで作業するため、Laravel等のバックエンドのファイルはこのフォルダの中だけに置き、リポジトリのルートには広げない。

- 資料は `../docs/backend/` に置く（目次は `../docs/README.md`）
- フロントから引き渡すもの：制作用PHP（`../shop-partner/` 等のページと `includes/` の共通部品）・Sass・生成CSS。書き方と引き渡し方は `../docs/coding/IMPLEMENTATION_REFERENCE.md`
- 業務仕様は `../docs/SPEC.md` と画面資料（`../docs/design/`）が正
- Laravel 13（2026-10-21導入）。Laravel付属のREADMEは `README.laravel.md`

## バックエンド担当者と決めること

- DDEVの構成：決定済み（2026-10-20）。バックエンドはXAMPPのVirtualHostで`backend/public`を表示し、フロントのDDEVは制作確認用のまま変更しない。詳細は`../docs/backend/SERVER_DIRECTORY_LAYOUT.md`「バックエンドのローカル開発環境」
- 生成CSS・アセットの取り込み：決定済み（2026-10-20）。バックエンド担当者がLaravelの`public/`配下へコピーする。具体的な配置パス・手順は未確定。詳細は`../docs/backend/SERVER_DIRECTORY_LAYOUT.md`「CSS・JS・画面用画像の取り込み」
- フロントのAI作業（Codex等）はこのフォルダを依頼なく編集しない
