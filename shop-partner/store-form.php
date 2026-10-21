<?php
// 制作確認用。保存・検証・DB参照などの業務処理は持たない。
$currentPage = 'stores';
$pageExtension = ($staticExport ?? false) ? 'html' : 'php';
// preview・架空の値・金額は表示確認専用。Bladeでは本番のデータ・状態に置き換える。
// idクエリでは検索しない。通常URLは編集例、新規はpreview=newで確認する。
$previewState = ($staticExport ?? false) ? '' : ($_GET['preview'] ?? '');
$isNewState = $previewState === 'new';
$isNoSizeState = $previewState === 'no-size';
$isSagawaState = $previewState === 'sagawa';
$pageTitle = $isNewState ? '店舗登録' : '店舗編集';
$storeId = $isNoSizeState ? 2 : ($isSagawaState ? 3 : 1);
$storeName = $isNewState ? '' : ($isNoSizeState ? '熊本ギフト サンプル店舗' : ($isSagawaState ? '熊本の旬の味覚と地域の特産品を全国へお届けする 阿蘇・天草ふるさとギフト サンプル店舗' : '熊日モール サンプル店舗'));
$carriers = require __DIR__ . '/includes/data_carriers.php';
$prefectureRegions = require __DIR__ . '/includes/data_prefectures.php';
$carrierCode = $isNewState ? '' : ($isNoSizeState ? 'yupack' : ($isSagawaState ? 'sagawa' : 'yamato'));
$carrier = $carriers[$carrierCode] ?? null;
$sizeBased = !$isNewState && !$isNoSizeState;
$usedSizes = $isNewState ? [] : ($isNoSizeState ? array_column($carrier['sizes'], 'size') : ($isSagawaState ? [60, 80, 100, 140, 160] : [60, 80, 100, 120, 140, 160]));
$freeShippingEnabled = !$isNewState && !$isNoSizeState && !$isSagawaState;
$temperatureLabels = ['refrigerated' => '冷蔵', 'frozen' => '冷凍'];
// 全社のサイズ表を先に持つ。非表示・非選択でもDOMの金額は破棄しない。
$storeCarriers = array_intersect_key($carriers, array_flip(['yamato', 'sagawa', 'yupack']));
$allSizes = [];
foreach ($storeCarriers as $definition) {
    $allSizes = array_merge($allSizes, array_column($definition['sizes'], 'size'));
}
$allSizes = array_values(array_unique($allSizes));
sort($allSizes);
// 初期HTML用の表示判定。JSのcanUseCoolと同じ条件（SPECの決定4）。業務上の配送可否の検証はサーバー側で行う。
$canUseCool = static function (?array $definition, ?int $size, string $temperature): bool {
    $rule = $definition['cool'][$temperature] ?? null;
    if ($rule === null) return false;
    if ($size === null) return true;
    $index = array_search($size, array_column($definition['sizes'], 'size'), true);
    if ($index === false) return false;
    if ($index === 0) return true;
    $current = $definition['sizes'][$index];
    $previous = $definition['sizes'][$index - 1];
    return min($size, $rule['maxTotalCm']) > $previous['size']
        || min($current['maxWeightKg'], $rule['maxWeightKg']) > $previous['maxWeightKg'];
};
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 店舗の登録・編集（2回目：配送・送料の操作）
 * URL: /shop-partner/store-form.<?= $pageExtension . PHP_EOL ?>
 * Referenced in: /shop-partner/stores.php・/index.html・/docs/design/CLIENT_STORE_FORM.md
 * 編集元: /shop-partner/store-form.php（.htmlは必要時のみの書き出し物。Git管理外）
 * Created: 2026-10-21
 * Last updated: 2026-10-21
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
    <link rel="stylesheet" href="assets/css/store-form.css" />
  </head>

  <body class="admin-page">
    <?php require __DIR__ . '/includes/inc_header.php'; ?>
    <div class="admin-layout">
      <?php require __DIR__ . '/includes/inc_sidebar.php'; ?>
      <main class="admin-main store-form-page">
        <div class="page-heading"><h1><?= $pageTitle ?></h1></div>
        <?php if (!$isNewState): ?>
          <dl class="store-meta">
            <div><dt>ID</dt><dd><?= $storeId ?></dd></div>
            <div><dt>更新日時</dt><dd><time datetime="2026-10-20T14:30:00+09:00">2026/10/20 14:30</time></dd></div>
          </dl>
        <?php endif; ?>
        <!-- name・idは仮。送信・保存・検証は3回目に制作する。 -->
        <form class="store-form" method="post" action="#" novalidate data-sizes-initialized="<?= $isNewState ? 'false' : 'true' ?>">
          <section class="form-box" aria-labelledby="basic-heading">
            <h2 id="basic-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" />
  <path d="M12 16v-4" />
  <path d="M12 8h.01" /></svg></span>基本情報</h2>
            <div class="form-field form-row">
              <label class="form-label" for="store-name">店舗名 <span class="required-label">必須</span></label>
              <div class="field-control field-wide"><input type="text" id="store-name" name="name" maxlength="50" value="<?= htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') ?>" required aria-required="true" /></div>
            </div>
            <p class="form-help contact-help" id="store-contact-help">以下の発送元情報は任意です。社内の連絡用として入力してください。</p>
            <div class="form-field form-row">
              <label class="form-label" for="store-postal-code">発送元の郵便番号 <span class="optional-label">任意</span></label>
              <div class="field-control field-medium"><input type="text" id="store-postal-code" name="postal_code" value="<?= $isNewState ? '' : '860-0000' ?>" aria-describedby="store-contact-help" /></div>
            </div>
            <div class="form-field form-row">
              <label class="form-label" for="store-address">発送元の住所 <span class="optional-label">任意</span></label>
              <div class="field-control field-wide"><input type="text" id="store-address" name="address" value="<?= $isNewState ? '' : '熊本県熊本市 サンプル住所' ?>" aria-describedby="store-contact-help" /></div>
            </div>
            <div class="form-field form-row">
              <label class="form-label" for="store-contact-name">発送元の担当者 <span class="optional-label">任意</span></label>
              <div class="field-control field-medium"><input type="text" id="store-contact-name" name="contact_name" maxlength="50" value="<?= $isNewState ? '' : 'サンプル担当者' ?>" aria-describedby="store-contact-help" /></div>
            </div>
            <div class="form-field form-row">
              <label class="form-label" for="store-phone">発送元の電話番号 <span class="optional-label">任意</span></label>
              <div class="field-control field-medium">
                <input type="tel" id="store-phone" name="phone" value="<?= $isNewState ? '' : '096-000-0000' ?>" aria-describedby="store-contact-help store-phone-help" />
                <p class="form-help" id="store-phone-help">ハイフンの有無はどちらでも入力できます。</p>
              </div>
            </div>
          </section>

          <section class="form-box" aria-labelledby="delivery-heading">
            <h2 id="delivery-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
  <path d="M15 18H9" />
  <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
  <circle cx="17" cy="18" r="2" />
  <circle cx="7" cy="18" r="2" /></svg></span>配送</h2>
            <div class="form-field form-row">
              <label class="form-label" for="store-carrier">運送会社 <span class="required-label">必須</span></label>
              <div class="field-control field-medium">
                <select id="store-carrier" name="carrier_code" required aria-required="true" aria-describedby="carrier-rules-heading">
                  <option value=""<?= $carrierCode === '' ? ' selected' : '' ?>>選択してください</option>
                  <?php foreach (['yamato', 'sagawa', 'yupack'] as $code): ?>
                    <option value="<?= $code ?>"<?= $carrierCode === $code ? ' selected' : '' ?>><?= $carriers[$code]['name'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <section class="carrier-rules" id="store-carrier-rules" aria-labelledby="carrier-rules-heading">
              <h3 id="carrier-rules-heading">運送会社の規格 <span>参照情報</span></h3>
              <?php if ($carrier === null): ?>
                <p class="form-help">運送会社を選ぶと表示します</p>
              <?php else: ?>
                <p class="form-help">サイズは3辺の合計（cm）、重さは1個あたりの上限です。</p>
                <dl class="carrier-sizes">
                  <?php foreach ($carrier['sizes'] as $size): ?>
                    <div><dt><?= $size['size'] ?>サイズ</dt><dd><?= $size['maxWeightKg'] ?>kgまで</dd></div>
                  <?php endforeach; ?>
                </dl>
                <dl class="carrier-cool">
                  <?php foreach ($temperatureLabels as $temperature => $label): $rule = $carrier['cool'][$temperature] ?? null; ?>
                    <div><dt><?= $label ?></dt><dd><?php if ($rule === null): ?>非対応<?php else: ?>3辺合計<?= $rule['maxTotalCm'] ?>cm・<?= $rule['maxWeightKg'] ?>kgまで<?= isset($rule['maxLongestCm']) ? '（最長辺' . $rule['maxLongestCm'] . 'cmまで）' : '' ?><?php endif; ?></dd></div>
                  <?php endforeach; ?>
                </dl>
              <?php endif; ?>
            </section>
            <div class="form-field">
              <label class="form-check" for="store-size-based"><input type="checkbox" id="store-size-based" name="size_based" value="1"<?= $sizeBased ? ' checked' : '' ?><?= $carrier === null ? ' disabled' : '' ?> aria-describedby="store-size-based-help" /><span>サイズ別に送料を設定する</span></label>
              <p class="form-help" id="store-size-based-help"<?= $carrier === null ? '' : ' hidden' ?>>運送会社を選ぶと設定できます</p>
            </div>
            <p class="form-help" id="store-size-notice" aria-live="polite" aria-atomic="true"></p>
            <fieldset class="form-field used-sizes" id="store-used-sizes"<?= $sizeBased ? '' : ' disabled hidden' ?>>
              <legend class="form-label">使うサイズ</legend>
              <div class="check-group">
                <?php foreach ($carrier['sizes'] ?? [] as $size): ?>
                  <label class="form-check" for="store-size-<?= $size['size'] ?>"><input type="checkbox" id="store-size-<?= $size['size'] ?>" name="used_sizes[]" value="<?= $size['size'] ?>"<?= in_array($size['size'], $usedSizes, true) ? ' checked' : '' ?> /><span><?= $size['size'] ?>サイズ</span></label>
                <?php endforeach; ?>
              </div>
            </fieldset>
          </section>

          <section class="form-box" aria-labelledby="shipping-heading">
            <h2 id="shipping-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M12 9.5V21m0-11.5L6 3m6 6.5L18 3" />
  <path d="M6 15h12" />
  <path d="M6 11h12" /></svg></span>送料</h2>
            <p class="form-help shipping-help" id="shipping-amount-help">通常送料はすべての都道府県に入力してください。金額は0〜99,999円の整数で入力し、無料の場合は0円を入力してください。</p>
            <?php foreach (['common', 'by_size'] as $mode):
                $isSizeMode = $mode === 'by_size';
                $activeMode = $isSizeMode === $sizeBased;
                $tableSizes = $isSizeMode ? $allSizes : [null];
            ?>
              <!-- 非選択方式はdisabled＋hidden。選択方式内の非表示タブはhiddenだけにする。 -->
              <fieldset class="shipping-mode" id="shipping-mode-<?= $mode ?>"<?= $activeMode ? '' : ' disabled hidden' ?>>
                <legend class="sr-only"><?= $isSizeMode ? 'サイズ別送料' : 'サイズ共通送料' ?></legend>
                <?php if ($isSizeMode && $tableSizes !== []): ?>
                  <div class="size-tabs" id="store-size-tabs" role="tablist" aria-label="送料を設定するサイズ">
                    <?php foreach ($usedSizes as $index => $size): ?>
                      <button type="button" role="tab" id="shipping-tab-<?= $size ?>" aria-controls="shipping-panel-<?= $size ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= $size ?>サイズ</button>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <?php foreach ($tableSizes as $index => $size):
                    $prefix = $isSizeMode ? 'shipping-size-' . $size : 'shipping-common';
                    $namePrefix = $isSizeMode ? 'shipping_tables[by_size][' . $size . ']' : 'shipping_tables[common]';
                    $isUsedSize = !$isSizeMode || in_array($size, $usedSizes, true);
                    $hasExampleAmounts = !$isNewState && $activeMode && $isUsedSize;
                ?>
                  <fieldset class="shipping-table" data-shipping-size="<?= $size ?? 'common' ?>"<?= $isSizeMode ? ' role="tabpanel" id="shipping-panel-' . $size . '"' . ($isUsedSize ? ' aria-labelledby="shipping-tab-' . $size . '"' : ' aria-label="' . $size . 'サイズ"') : ' id="shipping-common-table"' ?><?= $isUsedSize ? '' : ' disabled' ?><?= $isSizeMode && $size !== ($usedSizes[0] ?? null) ? ' hidden' : '' ?>>
                    <div class="bulk-entry national-entry form-field">
                      <label class="form-label" for="<?= $prefix ?>-all">全国一律</label>
                      <div class="bulk-controls">
                        <div class="price-input"><input type="text" inputmode="numeric" id="<?= $prefix ?>-all" pattern="[0-9]{1,5}" /><span>円（税込）</span></div>
                        <button class="bulk-button" type="button" data-bulk-scope="all" data-bulk-input="<?= $prefix ?>-all">全都道府県に入力</button>
                      </div>
                    </div>
                    <p class="form-help bulk-notice" id="<?= $prefix ?>-notice" aria-live="polite" aria-atomic="true"></p>
                    <?php foreach ($prefectureRegions as $regionKey => $region): ?>
                      <section class="shipping-region" aria-labelledby="<?= $prefix ?>-<?= $regionKey ?>-heading">
                        <div class="region-heading">
                          <h3 id="<?= $prefix ?>-<?= $regionKey ?>-heading"><?= $region['name'] ?></h3>
                          <div class="bulk-controls form-field">
                            <label class="sr-only" for="<?= $prefix ?>-<?= $regionKey ?>-bulk"><?= $region['name'] ?>の一括入力金額</label>
                            <div class="price-input"><input type="text" inputmode="numeric" id="<?= $prefix ?>-<?= $regionKey ?>-bulk" pattern="[0-9]{1,5}" /><span>円（税込）</span></div>
                            <button class="bulk-button" type="button" data-bulk-scope="region" data-bulk-input="<?= $prefix ?>-<?= $regionKey ?>-bulk">この地方に入力</button>
                          </div>
                        </div>
                        <div class="prefecture-grid">
                          <?php foreach ($region['prefectures'] as $prefecture): ?>
                            <div class="form-field">
                              <label class="form-label" for="<?= $prefix ?>-pref-<?= $prefecture['code'] ?>"><?= $prefecture['name'] ?></label>
                              <div class="price-input">
                                <input type="text" inputmode="numeric" data-prefecture-fee id="<?= $prefix ?>-pref-<?= $prefecture['code'] ?>" name="<?= $namePrefix ?>[rates][<?= $prefecture['code'] ?>][base_fee_yen]" value="<?= $hasExampleAmounts ? 800 + $index * 200 : '' ?>" maxlength="5" pattern="[0-9]{1,5}" required aria-required="true" aria-describedby="shipping-amount-help" /><span>円（税込）</span>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      </section>
                    <?php endforeach; ?>
                    <section class="cool-surcharges" aria-labelledby="<?= $prefix ?>-cool-heading">
                      <h3 id="<?= $prefix ?>-cool-heading">クール便の追加料金</h3>
                      <?php $hasCoolFields = $canUseCool($carrier, $size, 'refrigerated') || $canUseCool($carrier, $size, 'frozen'); ?>
                      <p class="form-help" data-cool-unselected<?= $carrier === null ? '' : ' hidden' ?>>運送会社を選ぶと表示します</p>
                      <p class="form-help" data-cool-unavailable<?= $carrier !== null && !$hasCoolFields ? '' : ' hidden' ?>>このサイズはクール便の規格外です</p>
                      <p class="form-help" id="<?= $prefix ?>-cool-help" data-cool-help<?= $hasCoolFields ? '' : ' hidden' ?>>空欄＝非対応、0円＝追加料金なしで対応。通常送料に加算する金額を入力してください。</p>
                      <div class="prefecture-grid cool-grid">
                        <?php foreach ($temperatureLabels as $temperature => $label):
                            $showCool = $canUseCool($carrier, $size, $temperature);
                            // 制作用の保存値例。規格外・非対応でも要素は残し、属性で送信対象から外す。
                            $coolValue = $hasExampleAmounts && $showCool ? '330' : '';
                            if ($isSagawaState && $temperature === 'refrigerated') $coolValue = '';
                            if ($carrierCode === 'yamato' && $size === 100 && $temperature === 'frozen') $coolValue = '';
                            $showYupack160Help = $carrierCode === 'yupack' && $size === 160 && $temperature === 'refrigerated';
                        ?>
                          <fieldset class="form-field" data-cool-temperature="<?= $temperature ?>"<?= $showCool ? '' : ' disabled hidden' ?>>
                            <label class="form-label" for="<?= $prefix ?>-<?= $temperature ?>"><?= $label ?></label>
                            <div class="price-input"><input type="text" inputmode="numeric" id="<?= $prefix ?>-<?= $temperature ?>" name="<?= $namePrefix ?>[cool_<?= $temperature ?>_surcharge_yen]" value="<?= $coolValue ?>" maxlength="5" pattern="[0-9]{1,5}" aria-describedby="<?= $prefix ?>-cool-help<?= $showYupack160Help ? ' ' . $prefix . '-refrigerated-limit' : '' ?>" /><span>円（税込）</span></div>
                            <?php if ($size === 160 && $temperature === 'refrigerated'): ?><p class="form-help" id="<?= $prefix ?>-refrigerated-limit" data-yupack-limit<?= $showYupack160Help ? '' : ' hidden' ?>>3辺合計150cmまで</p><?php endif; ?>
                          </fieldset>
                        <?php endforeach; ?>
                      </div>
                    </section>
                  </fieldset>
                <?php endforeach; ?>
              </fieldset>
            <?php endforeach; ?>
          </section>

          <section class="form-box" aria-labelledby="free-shipping-heading">
            <h2 id="free-shipping-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
  <path d="m15 9-6 6" />
  <path d="M9 9h.01" />
  <path d="M15 15h.01" /></svg></span>送料無料の条件</h2>
            <div class="form-field">
              <label class="form-check" for="store-free-shipping"><input type="checkbox" id="store-free-shipping" name="free_shipping_enabled" value="1"<?= $freeShippingEnabled ? ' checked' : '' ?> /><span>送料無料の条件を設定する</span></label>
            </div>
            <div class="form-field form-row">
              <label class="form-label" for="store-free-shipping-threshold">送料無料にする金額</label>
              <div class="field-control">
                <div class="price-input"><input type="text" inputmode="numeric" id="store-free-shipping-threshold" name="free_shipping_threshold_yen" value="<?= $freeShippingEnabled ? '10000' : '' ?>" maxlength="5" pattern="(?=.*[1-9])[0-9]{1,5}" aria-describedby="free-shipping-help"<?= $freeShippingEnabled ? '' : ' disabled' ?> /><span>円（税込）以上</span></div>
                <p class="form-help" id="free-shipping-help">1〜99,999円の整数で入力してください。この金額以上で送料無料になります。</p>
              </div>
            </div>
          </section>
          <div class="save-bar">
            <a href="stores.<?= $pageExtension ?>"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m12 19-7-7 7-7" /><path d="M19 12H5" /></svg>一覧へ戻る</a>
            <!-- 保存はまだ行わない。送信ボタン化・保存中表示は3回目に制作する。 -->
            <button class="button-primary" id="store-save" type="button">保存する</button>
          </div>
        </form>
      </main>
    </div>
    <script type="application/json" id="store-carrier-data"><?= json_encode($storeCarriers, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/store-form.js"></script>
  </body>
</html>
