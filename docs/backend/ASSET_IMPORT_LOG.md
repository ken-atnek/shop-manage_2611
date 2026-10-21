# 画面アセットの取り込み記録

- 目的：フロントで生成したCSS・JS・画面用画像をLaravelの public へ取り込んだ記録。取り込み元のコミットを残す（QA No.9）
- 配置先：Client用は backend/public/assets/partner/（css/・js/・images/）、Master用は backend/public/assets/master/（2026-10-21、担当者決定）。images/ は最初に画像を取り込むときに作る
- 取り込まないもの：.map ファイル、shop-partner/assets/images/sample/ などの表示確認用画像
- コピーするときはファイルの中身を変えない。CSS末尾の sourceMappingURL コメントは残す（.map は取り込まないため、開発者ツールでだけ読み込みエラーが出る）
- Laravel連携のためにJSを直したときは、「Laravel連携で直したJS」の列に、ファイル名と変更点の要点を書く
- 新しい取り込みは表の一番下に1行追加する

| 日付 | 取り込み元（ブランチ・コミット） | 対象（Master／Client） | 取り込んだファイル | Laravel連携で直したJS | 備考 |
| --- | --- | --- | --- | --- | --- |
| 2026-10-21 | ken-work d86a27e | Client | shop-partner/assets/css/common.css、products.css、shop-partner/assets/js/common.js | なし | 商品一覧のBlade組み込み用。初回 |
