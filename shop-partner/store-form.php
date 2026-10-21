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
?>
<!doctype html>
<!-- =======================================
 * 熊日モール Client 店舗の登録・編集（1回目：表示）
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
        <!-- name・idは仮。方式とサイズの切り替え等は2回目、送信・保存・検証は3回目に制作する。 -->
        <form class="store-form" method="post" action="#" novalidate>
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
            <section class="carrier-rules" aria-labelledby="carrier-rules-heading">
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
              <label class="form-check" for="store-size-based"><input type="checkbox" id="store-size-based" name="size_based" value="1"<?= $sizeBased ? ' checked' : '' ?> /><span>サイズ別に送料を設定する</span></label>
            </div>
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
                $tableSizes = $isSizeMode ? $usedSizes : [null];
            ?>
              <!-- 非選択方式はdisabled＋hidden。選択方式内の非表示タブはhiddenだけにする。 -->
              <fieldset class="shipping-mode" id="shipping-mode-<?= $mode ?>"<?= $activeMode ? '' : ' disabled hidden' ?>>
                <legend class="sr-only"><?= $isSizeMode ? 'サイズ別送料' : 'サイズ共通送料' ?></legend>
                <?php if ($isSizeMode && $tableSizes !== []): ?>
                  <div class="size-tabs" role="tablist" aria-label="送料を設定するサイズ">
                    <?php foreach ($tableSizes as $index => $size): ?>
                      <button type="button" role="tab" id="shipping-tab-<?= $size ?>" aria-controls="shipping-panel-<?= $size ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>" tabindex="<?= $index === 0 ? '0' : '-1' ?>"><?= $size ?>サイズ</button>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <?php foreach ($tableSizes as $index => $size):
                    $prefix = $isSizeMode ? 'shipping-size-' . $size : 'shipping-common';
                    $namePrefix = $isSizeMode ? 'shipping_tables[by_size][' . $size . ']' : 'shipping_tables[common]';
                    $hasExampleAmounts = !$isNewState && $activeMode;
                ?>
                  <div class="shipping-table"<?= $isSizeMode ? ' role="tabpanel" id="shipping-panel-' . $size . '" aria-labelledby="shipping-tab-' . $size . '"' : ' id="shipping-common-table"' ?><?= $isSizeMode && $index !== 0 ? ' hidden' : '' ?>>
                    <div class="bulk-entry national-entry form-field">
                      <label class="form-label" for="<?= $prefix ?>-all">全国一律</label>
                      <div class="bulk-controls">
                        <div class="price-input"><input type="text" inputmode="numeric" id="<?= $prefix ?>-all" maxlength="5" pattern="[0-9]{1,5}" /><span>円（税込）</span></div>
                        <button class="bulk-button" type="button">全都道府県に入力</button>
                      </div>
                    </div>
                    <?php foreach ($prefectureRegions as $regionKey => $region): ?>
                      <section class="shipping-region" aria-labelledby="<?= $prefix ?>-<?= $regionKey ?>-heading">
                        <div class="region-heading">
                          <h3 id="<?= $prefix ?>-<?= $regionKey ?>-heading"><?= $region['name'] ?></h3>
                          <div class="bulk-controls form-field">
                            <label class="sr-only" for="<?= $prefix ?>-<?= $regionKey ?>-bulk"><?= $region['name'] ?>の一括入力金額</label>
                            <div class="price-input"><input type="text" inputmode="numeric" id="<?= $prefix ?>-<?= $regionKey ?>-bulk" maxlength="5" pattern="[0-9]{1,5}" /><span>円（税込）</span></div>
                            <button class="bulk-button" type="button">この地方に入力</button>
                          </div>
                        </div>
                        <div class="prefecture-grid">
                          <?php foreach ($region['prefectures'] as $prefecture): ?>
                            <div class="form-field">
                              <label class="form-label" for="<?= $prefix ?>-pref-<?= $prefecture['code'] ?>"><?= $prefecture['name'] ?></label>
                              <div class="price-input">
                                <input type="text" inputmode="numeric" id="<?= $prefix ?>-pref-<?= $prefecture['code'] ?>" name="<?= $namePrefix ?>[rates][<?= $prefecture['code'] ?>][base_fee_yen]" value="<?= $hasExampleAmounts ? 800 + $index * 200 : '' ?>" maxlength="5" pattern="[0-9]{1,5}" required aria-required="true" aria-describedby="shipping-amount-help" /><span>円（税込）</span>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      </section>
                    <?php endforeach; ?>
                    <section class="cool-surcharges" aria-labelledby="<?= $prefix ?>-cool-heading">
                      <h3 id="<?= $prefix ?>-cool-heading">クール便の追加料金</h3>
                      <?php if ($carrier === null): ?>
                        <p class="form-help">運送会社を選ぶと表示します</p>
                      <?php else: ?>
                        <p class="form-help" id="<?= $prefix ?>-cool-help">空欄＝非対応、0円＝追加料金なしで対応。通常送料に加算する金額を入力してください。</p>
                        <div class="prefecture-grid cool-grid">
                          <?php foreach ($temperatureLabels as $temperature => $label):
                              if (($carrier['cool'][$temperature] ?? null) === null) continue;
                              // 商品フォームの制作例に合わせ、100の冷凍・規格を超えるサイズは空欄。佐川の冷蔵も空欄の表示例。
                              $coolValue = $hasExampleAmounts ? '330' : '';
                              if ($isSagawaState && $temperature === 'refrigerated') $coolValue = '';
                              if ($isSizeMode && $size > $carrier['cool'][$temperature]['maxTotalCm']) $coolValue = '';
                              if ($carrierCode === 'yamato' && $size === 100 && $temperature === 'frozen') $coolValue = '';
                              $showYupack160Help = $carrierCode === 'yupack' && $size === 160 && $temperature === 'refrigerated';
                          ?>
                            <div class="form-field">
                              <label class="form-label" for="<?= $prefix ?>-<?= $temperature ?>"><?= $label ?></label>
                              <div class="price-input"><input type="text" inputmode="numeric" id="<?= $prefix ?>-<?= $temperature ?>" name="<?= $namePrefix ?>[cool_<?= $temperature ?>_surcharge_yen]" value="<?= $coolValue ?>" maxlength="5" pattern="[0-9]{1,5}" aria-describedby="<?= $prefix ?>-cool-help<?= $showYupack160Help ? ' ' . $prefix . '-refrigerated-limit' : '' ?>" /><span>円（税込）</span></div>
                              <?php if ($showYupack160Help): ?><p class="form-help" id="<?= $prefix ?>-refrigerated-limit">3辺合計150cmまで</p><?php endif; ?>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </section>
                  </div>
                <?php endforeach; ?>
              </fieldset>
            <?php endforeach; ?>
          </section>

          <section class="form-box" aria-labelledby="free-shipping-heading">
            <h2 id="free-shipping-heading"><span class="heading-icon"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M12 9.5V21m0-11.5L6 3m6 6.5L18 3" />
  <path d="M6 15h12" />
  <path d="M6 11h12" /></svg></span>送料無料の条件</h2>
            <div class="form-field">
              <label class="form-check" for="store-free-shipping"><input type="checkbox" id="store-free-shipping" name="free_shipping_enabled" value="1"<?= $freeShippingEnabled ? ' checked' : '' ?> /><span>送料無料の条件を設定する</span></label>
            </div>
            <div class="form-field form-row">
              <label class="form-label" for="store-free-shipping-threshold">送料無料にする金額</label>
              <div class="field-control">
                <div class="price-input"><input type="text" inputmode="numeric" id="store-free-shipping-threshold" name="free_shipping_threshold_yen" value="<?= $freeShippingEnabled ? '10000' : '' ?>" maxlength="5" pattern="[0-9]{1,5}" aria-describedby="free-shipping-help"<?= $freeShippingEnabled ? '' : ' disabled' ?> /><span>円（税込）以上</span></div>
                <p class="form-help" id="free-shipping-help">この金額以上で送料無料になります。</p>
              </div>
            </div>
          </section>
          <div class="save-bar">
            <a href="stores.<?= $pageExtension ?>"><svg width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="m12 19-7-7 7-7" /><path d="M19 12H5" /></svg>一覧へ戻る</a>
            <!-- 1回目は表示のみ。送信ボタン化・保存中表示は3回目に制作する。 -->
            <button class="button-primary" id="store-save" type="button">保存する</button>
          </div>
        </form>
      </main>
    </div>
    <script src="assets/js/common.js"></script>
  </body>
</html>
