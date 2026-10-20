<?php
// 制作確認用。保存・検証・DB参照などの業務処理は持たない。
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';

// previewと表示例は制作確認専用。Laravel・Bladeへ移さず、実データ・検証結果へ置き換える。
$previewState = $_GET['preview'] ?? '';
if ($staticExport ?? false) {
    $previewState = '';
}
$isErrorState = $previewState === 'error';
$isNewState = $previewState === 'new' || $isErrorState;
$isFoodState = $previewState === 'food' || $isErrorState;
$isSavedState = $previewState === 'saved';
$isRegisteredState = $previewState === 'registered';
$currentPage = $isNewState ? 'product-new' : 'products';
$pageTitle = $isNewState ? '商品登録' : '商品編集';

// 架空の保存済み商品1件。idクエリでは取得・切り替えをせず、編集状態の同じ表示例を出す。
// foodは食品入力欄の確認用に商品名等を差し替える。errorは新規入力途中の値の保持例。
$productName = $isNewState ? '' : ($isFoodState ? '熊本の焼き菓子詰め合わせ' : '写真入りマグカップ');
$productDescription = $previewState === 'new' ? '' : ($isFoodState ? '熊本の素材を使った焼き菓子を詰め合わせました。' : 'お気に入りの写真でつくるオリジナルマグカップです。贈り物にもおすすめです。');
$productPrice = $previewState === 'new' ? '' : '2800';
$productStock = $previewState === 'new' ? '' : '24';
$taxRate = $isNewState ? '' : ($isFoodState ? '8' : '10');
// 食品表示の保存値を想定した表示例。販売店からのひな形挿入は2回目に検討する。
$foodLabelExample = "名称：焼き菓子\n原材料名：小麦粉（国内製造）、砂糖、バター、卵\n内容量：12個\n賞味期限：製造日より30日\n保存方法：直射日光・高温多湿を避けて保存\n製造者：〇〇";
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 商品詳細（登録・編集）
 * URL: /shop-partner/product-form.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-partner/products.php・/index.html・/docs/design/CLIENT_PRODUCT_FORM.md
 * 編集元: /shop-partner/product-form.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-20
 * Last updated: 2026-10-20
 * ======================================= -->
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $pageTitle ?>｜熊日モール パートナー管理画面</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <!-- 初期描画前に共通サイドバーの状態を反映。1194pxはcommon.scssの$tbwとそろえる。 -->
    <script>
      (() => {
        let collapsed = window.matchMedia("(max-width: 1194px)").matches;
        try {
          collapsed = collapsed || localStorage.getItem("kumanichi-client-sidebar") === "collapsed";
        } catch {
          // localStorageが使えなくても初期表示を継続する。
        }
        document.documentElement.classList.toggle("sidebar-collapsed", collapsed);
      })();
    </script>
    <link rel="stylesheet" href="assets/css/common.css" />
    <link rel="stylesheet" href="assets/css/product-form.css" />
  </head>

  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main product-form-page">
        <div class="page-heading">
          <h1><?= $pageTitle ?></h1>
        </div>
        <?php if (!$isNewState): ?>
          <dl class="product-meta">
            <div><dt>ID</dt><dd>986</dd></div>
            <div><dt>登録日時</dt><dd><time datetime="2026-10-17T10:00:00+09:00">2026/10/17 10:00</time></dd></div>
            <div><dt>更新日時</dt><dd><time datetime="2026-10-19T14:30:00+09:00">2026/10/19 14:30</time></dd></div>
          </dl>
        <?php endif; ?>
        <?php if ($isSavedState || $isRegisteredState): ?>
          <p class="save-status" role="status"><?= $isRegisteredState ? '商品を登録しました' : '保存しました' ?></p>
        <?php endif; ?>
        <?php if ($isErrorState): ?>
          <!-- 自動フォーカスは2回目。ここではエラー結果の表示とページ内リンクだけを確認する。 -->
          <section class="error-summary" id="product-errors" aria-labelledby="product-errors-heading" tabindex="-1">
            <h2 id="product-errors-heading">入力内容を確認してください（3件）</h2>
            <ul>
              <li><a href="#product-name">商品名を入力してください</a></li>
              <li><a href="#product-tax-8">消費税率を選択してください</a></li>
              <li><a href="#food-label">食品表示を入力してください</a></li>
            </ul>
          </section>
        <?php endif; ?>

        <!-- action・name・valueは制作例。POST処理・保存・検証・画像転送は実装しない。 -->
        <form class="product-form" method="post" action="#" novalidate>
          <section class="form-box" aria-labelledby="basic-heading">
            <h2 id="basic-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" />
  <path d="M12 16v-4" />
  <path d="M12 8h.01" /></svg></span>基本情報</h2>
            <div class="form-field form-row">
              <label class="form-label" for="product-store">販売店 <span class="required-label">必須</span></label>
              <div class="field-control field-medium">
                <select id="product-store" name="store_id" aria-required="true">
                  <option value=""<?= $previewState === 'new' ? ' selected' : '' ?>>選択してください</option>
                  <option value="1"<?= $previewState !== 'new' ? ' selected' : '' ?>>熊日モール サンプル販売店</option>
                  <option value="2">熊本ギフト サンプル販売店</option>
                </select>
              </div>
            </div>
            <div class="form-field form-row">
              <label class="form-label" for="product-name">商品名 <span class="required-label">必須</span></label>
              <div class="field-control field-wide">
                <input type="text" id="product-name" name="product_name" value="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>" aria-required="true"<?= $isErrorState ? ' aria-invalid="true" aria-describedby="product-name-error"' : '' ?> />
                <?php if ($isErrorState): ?>
                  <p class="form-error" id="product-name-error">商品名を入力してください</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="form-field form-row">
              <label class="form-label" for="product-description">商品説明</label>
              <div class="field-control field-wide">
                <textarea id="product-description" name="description"><?= htmlspecialchars($productDescription, ENT_QUOTES, 'UTF-8') ?></textarea>
              </div>
            </div>
          </section>

          <section class="form-box" aria-labelledby="images-heading">
            <h2 id="images-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M18 22H4a2 2 0 0 1-2-2V6" />
  <path d="m22 13-1.296-1.296a2.41 2.41 0 0 0-3.408 0L11 18" />
  <circle cx="12" cy="8" r="2" />
  <rect width="16" height="16" x="6" y="2" rx="2" /></svg></span>商品画像</h2>
            <!-- 新規は画像なし、ほかの状態は3枚の表示例。画像操作は2回目。 -->
            <?php if ($previewState !== 'new'): ?>
              <div class="form-field form-row">
                <p class="form-label" id="registered-images-label">登録済みの画像</p>
                <div class="field-control field-wide">
                  <ol class="product-images" aria-labelledby="registered-images-label">
                    <?php foreach (['photo_01.jpg', 'photo_02.jpg', 'photo_01.jpg'] as $imageIndex => $imageFile): ?>
                      <li>
                        <div class="image-preview">
                          <img src="assets/images/sample/<?= $imageFile ?>" width="160" height="160" alt="商品画像<?= $imageIndex + 1 ?>" />
                          <?php if ($imageIndex === 0): ?><span class="main-image-label">メイン</span><?php endif; ?>
                        </div>
                        <div class="image-actions">
                          <button type="button" aria-label="商品画像<?= $imageIndex + 1 ?>を前へ" title="商品画像<?= $imageIndex + 1 ?>を前へ"<?= $imageIndex === 0 ? ' disabled' : '' ?>><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6" /></svg></button>
                          <button type="button" aria-label="商品画像<?= $imageIndex + 1 ?>を後ろへ" title="商品画像<?= $imageIndex + 1 ?>を後ろへ"<?= $imageIndex === 2 ? ' disabled' : '' ?>><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6" /></svg></button>
                          <button type="button" aria-label="商品画像<?= $imageIndex + 1 ?>を削除" title="商品画像<?= $imageIndex + 1 ?>を削除"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M3 6h18" /><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" /><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" /><line x1="10" x2="10" y1="11" y2="17" /><line x1="14" x2="14" y1="11" y2="17" /></svg></button>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  </ol>
                </div>
              </div>
            <?php endif; ?>
            <!-- ドロップ領域の外観だけを制作。ドラッグによる追加は2回目。 -->
            <div class="form-field form-row">
              <p class="form-label">画像を追加</p>
              <div class="field-control field-wide image-dropzone">
                <div class="image-drop-label" id="image-drop-help"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M10.3 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10l-3.1-3.1a2 2 0 0 0-2.814.014L6 21" />
    <path d="m14 19.5 3-3 3 3" />
    <path d="M17 22v-5.5" />
    <circle cx="9" cy="9" r="2" /></svg><span>ここに画像をドラッグ、またはクリックして選択</span></div>
                <input type="file" id="product-images" name="images[]" aria-describedby="image-drop-help" multiple />
                <label class="image-select-button" for="product-images">ファイルを選択</label>
              </div>
            </div>
          </section>

          <section class="form-box" aria-labelledby="price-heading">
            <h2 id="price-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M12 9.5V21m0-11.5L6 3m6 6.5L18 3" />
  <path d="M6 15h12" />
  <path d="M6 11h12" /></svg></span>価格・在庫</h2>
            <div class="form-field form-row">
              <label class="form-label" for="product-price">価格（税込） <span class="required-label">必須</span></label>
              <div class="field-control">
                <div class="price-input">
                  <input type="text" inputmode="numeric" id="product-price" name="price" value="<?= $productPrice ?>" aria-required="true" />
                  <span>円（税込）</span>
                </div>
              </div>
            </div>
            <fieldset class="form-field form-row">
              <legend class="form-label" id="tax-label">消費税率 <span class="required-label">必須</span></legend>
              <div class="field-control">
                <div class="check-group choice-cards">
                  <label class="form-check choice-card" for="product-tax-8"><input type="radio" id="product-tax-8" name="tax_rate" value="8"<?= $taxRate === '8' ? ' checked' : '' ?><?= $isErrorState ? ' aria-invalid="true" aria-describedby="tax-help product-tax-error"' : '' ?> /><span>8%（軽減税率）</span></label>
                  <label class="form-check choice-card" for="product-tax-10"><input type="radio" id="product-tax-10" name="tax_rate" value="10"<?= $taxRate === '10' ? ' checked' : '' ?><?= $isErrorState ? ' aria-invalid="true" aria-describedby="tax-help product-tax-error"' : '' ?> /><span>10%（標準税率）</span></label>
                </div>
                <p class="form-help" id="tax-help">食品は通常8%です</p>
                <?php if ($isErrorState): ?>
                  <p class="form-error" id="product-tax-error">消費税率を選択してください</p>
                <?php endif; ?>
              </div>
            </fieldset>
            <div class="form-field form-row">
              <label class="form-label" for="product-stock">在庫数</label>
              <div class="field-control">
                <input class="input-number" type="text" inputmode="numeric" id="product-stock" name="stock" value="<?= $productStock ?>" />
                <label class="form-check stock-check" for="product-no-stock"><input type="checkbox" id="product-no-stock" name="no_stock_management" value="1" /><span>在庫を管理しない</span></label>
              </div>
            </div>
          </section>

          <section class="form-box" aria-labelledby="shipping-heading">
            <h2 id="shipping-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
  <path d="M15 18H9" />
  <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
  <circle cx="17" cy="18" r="2" />
  <circle cx="7" cy="18" r="2" /></svg></span>配送</h2>
            <fieldset class="form-field form-row">
              <legend class="form-label" id="temperature-label">配送温度帯 <span class="required-label">必須</span></legend>
              <div class="field-control">
                <div class="check-group choice-cards">
                  <label class="form-check choice-card" for="temperature-normal"><input type="radio" id="temperature-normal" name="temperature" value="normal" checked /><span class="choice-content"><span class="choice-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z" />
    <path d="M12 22V12" />
    <polyline points="3.29 7 12 12 20.71 7" />
    <path d="m7.5 4.27 9 5.15" /></svg></span><span>常温</span></span></label>
                  <label class="form-check choice-card" for="temperature-cold"><input type="radio" id="temperature-cold" name="temperature" value="cold" /><span class="choice-content"><span class="choice-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z" /></svg></span><span>冷蔵</span></span></label>
                  <label class="form-check choice-card" for="temperature-frozen"><input type="radio" id="temperature-frozen" name="temperature" value="frozen" /><span class="choice-content"><span class="choice-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m10 20-1.25-2.5L6 18" />
    <path d="M10 4 8.75 6.5 6 6" />
    <path d="m14 20 1.25-2.5L18 18" />
    <path d="m14 4 1.25 2.5L18 6" />
    <path d="m17 21-3-6h-4" />
    <path d="m17 3-3 6 1.5 3" />
    <path d="M2 12h6.5L10 9" />
    <path d="m20 10-1.5 2 1.5 2" />
    <path d="M22 12h-6.5L14 15" />
    <path d="m4 10 1.5 2L4 14" />
    <path d="m7 21 3-6-1.5-3" />
    <path d="m7 3 3 6h4" /></svg></span><span>冷凍</span></span></label>
                </div>
              </div>
            </fieldset>
            <fieldset class="form-field form-row">
              <legend class="form-label">梱包時の大きさ・重さ</legend>
              <div class="field-control">
                <p class="form-help packing-help" id="packing-help">梱包したときの大きさと重さを入力してください</p>
                <div class="packing-fields">
                  <?php foreach (['length' => ['縦', 'cm', '20'], 'width' => ['横', 'cm', '20'], 'height' => ['高さ', 'cm', '15'], 'weight' => ['重さ', 'kg', '0.8']] as $packingKey => $packing): ?>
                    <div class="packing-field">
                      <label class="form-label" for="packing-<?= $packingKey ?>"><?= $packing[0] ?><span class="sr-only">（<?= $packing[1] ?>）</span></label>
                      <div class="unit-input">
                        <input type="text" inputmode="decimal" id="packing-<?= $packingKey ?>" name="packing[<?= $packingKey ?>]" value="<?= $previewState === 'new' ? '' : $packing[2] ?>" aria-describedby="packing-help" />
                        <span aria-hidden="true"><?= $packing[1] ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <!-- 計算しない固定の表示例。サイズ自動判定・販売店連動・クール便の注意は2回目。 -->
                <p class="size-result"><?= $previewState === 'new' ? '縦・横・高さ・重さを入力すると判定します' : '判定：60サイズ（3辺合計55cm・0.8kg）' ?></p>
              </div>
            </fieldset>
          </section>

          <section class="form-box" aria-labelledby="upload-heading">
            <h2 id="upload-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z" />
  <path d="M14 2v5a1 1 0 0 0 1 1h5" />
  <path d="M12 12v6" />
  <path d="m15 15-3-3-3 3" /></svg></span>注文者からのデータ入稿</h2>
            <div class="form-field form-row upload-field">
              <p class="form-label">入稿</p>
              <div class="field-control">
                <!-- 全previewで「入稿なし」の表示例。編集時の実データは保存済みの値に置き換える。 -->
                <label class="form-check" for="product-upload"><input type="checkbox" id="product-upload" name="upload" value="1" aria-describedby="upload-help" /><span>入稿あり</span></label>
                <p class="form-help" id="upload-help">注文者から画像を受け付ける場合はチェックしてください</p>
              </div>
            </div>
          </section>

          <section class="form-box food-box" aria-labelledby="food-heading">
            <h2 id="food-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M11 17h.01" />
  <path d="M11.496 2c.324-.016.558.292.529.615a4 4 0 004.235 4.368.713.713 0 01.758.757 4 4 0 004.366 4.237c.323-.03.63.204.614.527a10 10 0 01-2.915 6.566A1 1 0 114.93 4.918 10 10 0 0111.496 2" />
  <path d="M12 12h.01" />
  <path d="M16 16h.01" />
  <path d="M16 3h.01" />
  <path d="M21 4h.01" />
  <path d="M21 8h.01" />
  <path d="M7 14h.01" />
  <path d="M9 8h.01" /></svg></span>商品仕様（食品表示）</h2>
            <div class="form-field form-row">
              <p class="form-label">食品</p>
              <div class="field-control">
                <label class="form-check" for="product-food"><input type="checkbox" id="product-food" name="is_food" value="1" aria-controls="food-fields"<?= $isFoodState ? ' checked' : '' ?> /><span>この商品は食品です</span></label>
              </div>
            </div>
            <!-- 1回目はPHPで状態を出す。チェック操作による切り替えは2回目のproduct-form.js（仮名）。toggleAccordionは使用しない。 -->
            <fieldset class="food-fields" id="food-fields"<?= $isFoodState ? '' : ' disabled hidden' ?>>
              <legend class="sr-only">商品仕様（食品表示）</legend>
              <div class="form-field form-row">
                <label class="form-label" for="food-label">食品表示 <span class="required-label">必須</span></label>
                <div class="field-control field-wide">
                  <textarea id="food-label" name="food_label" aria-required="true" aria-describedby="food-label-help<?= $isErrorState ? ' food-label-error' : '' ?>"<?= $isErrorState ? ' aria-invalid="true"' : '' ?>><?= $isFoodState && !$isErrorState ? htmlspecialchars($foodLabelExample, ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                  <p class="form-help" id="food-label-help">パッケージの表示どおりに入力してください。詰め合わせの場合は商品ごとに記載してください。</p>
                  <details class="food-label-example">
                    <summary>記入例を見る</summary>
                    <p><?= nl2br(htmlspecialchars($foodLabelExample, ENT_QUOTES, 'UTF-8')) ?></p>
                  </details>
                  <?php if ($isErrorState): ?>
                    <p class="form-error" id="food-label-error">食品表示を入力してください</p>
                  <?php endif; ?>
                </div>
              </div>
              <fieldset class="form-field form-row" aria-describedby="allergen-help">
                <legend class="form-label">アレルゲン <span class="required-label">必須</span></legend>
                <div class="field-control">
                  <p class="form-help" id="allergen-help">1つ以上、または「該当なし」を選択してください。</p>
                  <!-- 品目・区分は一部の表示例。正式な一覧はバックエンドのマスタから出力する。 -->
                  <fieldset class="allergen-group">
                    <legend>特定原材料</legend>
                    <div class="check-group">
                      <label class="form-check" for="allergen-wheat"><input type="checkbox" id="allergen-wheat" name="allergens[]" value="wheat"<?= $isFoodState ? ' checked' : '' ?> /><span>小麦</span></label>
                      <label class="form-check" for="allergen-egg"><input type="checkbox" id="allergen-egg" name="allergens[]" value="egg"<?= $isFoodState ? ' checked' : '' ?> /><span>卵</span></label>
                      <label class="form-check" for="allergen-milk"><input type="checkbox" id="allergen-milk" name="allergens[]" value="milk"<?= $isFoodState ? ' checked' : '' ?> /><span>乳</span></label>
                    </div>
                  </fieldset>
                  <fieldset class="allergen-group">
                    <legend>推奨品目</legend>
                    <div class="check-group">
                      <label class="form-check" for="allergen-soy"><input type="checkbox" id="allergen-soy" name="allergens[]" value="soy" /><span>大豆</span></label>
                      <label class="form-check" for="allergen-apple"><input type="checkbox" id="allergen-apple" name="allergens[]" value="apple" /><span>りんご</span></label>
                      <label class="form-check" for="allergen-sesame"><input type="checkbox" id="allergen-sesame" name="allergens[]" value="sesame" /><span>ごま</span></label>
                    </div>
                  </fieldset>
                  <label class="form-check" for="allergen-none"><input type="checkbox" id="allergen-none" name="allergens[]" value="none" /><span>該当なし</span></label>
                  <div class="field-wide">
                    <label class="form-label" for="allergen-other">その他のアレルゲン</label>
                    <input type="text" id="allergen-other" name="allergens_other" value="" />
                  </div>
                </div>
              </fieldset>
            </fieldset>
          </section>

          <section class="form-box" aria-labelledby="publish-heading">
            <h2 id="publish-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
  <circle cx="12" cy="12" r="3" /></svg></span>公開設定</h2>
            <fieldset class="form-field form-row">
              <legend class="form-label">公開状態</legend>
              <div class="field-control">
                <div class="check-group choice-cards">
                  <label class="form-check choice-card" for="publish-public"><input type="radio" id="publish-public" name="publish" value="public"<?= !$isNewState ? ' checked' : '' ?> /><span class="choice-text"><span>公開</span><span class="choice-description">モールに表示されます</span></span></label>
                  <label class="form-check choice-card" for="publish-private"><input type="radio" id="publish-private" name="publish" value="private"<?= $isNewState ? ' checked' : '' ?> /><span class="choice-text"><span>非公開</span><span class="choice-description">モールに表示されません</span></span></label>
                </div>
              </div>
            </fieldset>
          </section>
          <div class="save-bar">
            <a href="products.<?= $pageExtension ?>"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m12 19-7-7 7-7" /><path d="M19 12H5" /></svg>一覧へ戻る</a>
            <button class="button-primary" type="submit">保存する</button>
          </div>
        </form>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>
