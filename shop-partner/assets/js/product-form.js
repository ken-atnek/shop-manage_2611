/* =======================================
 * 熊日モール Client 商品フォーム 状態連動（制作2-A）
 * URL: /shop-partner/assets/js/product-form.js
 * Referenced in: /shop-partner/product-form.php
 * Created: 2026-10-20
 * Last updated: 2026-10-20
 * ======================================= */

"use strict";

(() => {
  const form = document.querySelector(".product-form");
  if (!form) return;

  // ===== 食品・在庫（値を保持し、フォーカスは操作元に残す） =====
  const foodCheck = form.querySelector("#product-food");
  const foodFields = form.querySelector("#food-fields");
  const noStockCheck = form.querySelector("#product-no-stock");
  const stockInput = form.querySelector("#product-stock");

  const syncFood = () => {
    foodFields.disabled = !foodCheck.checked;
    foodFields.hidden = !foodCheck.checked;
  };
  const syncStock = () => {
    stockInput.disabled = noStockCheck.checked;
  };
  // 食品でも税率は自動変更せず、食品オン＋10%の場合だけ確認を促す。
  const taxInputs = form.querySelectorAll('input[name="tax_rate"]');
  const taxWarning = form.querySelector("#tax-warning .tax-warning");
  const taxWarningText = taxWarning.querySelector("[data-tax-warning-text]");
  const syncTaxWarning = () => {
    const show = foodCheck.checked && form.querySelector('input[name="tax_rate"]:checked')?.value === "10";
    const text = show ? "食品は通常8%です。税率を確認してください" : "";
    taxWarning.hidden = !show;
    if (taxWarningText.textContent !== text) taxWarningText.textContent = text;
  };
  foodCheck.addEventListener("change", () => {
    syncFood();
    syncTaxWarning();
  });
  taxInputs.forEach((input) => input.addEventListener("change", syncTaxWarning));
  noStockCheck.addEventListener("change", syncStock);
  syncFood();
  syncStock();
  syncTaxWarning();

  // ===== 配送（JSONはBladeで同じ形の実データへ置き換える） =====
  const storeSelect = form.querySelector("#product-store");
  const packingInputs = ["length", "width", "height", "weight"].map((key) =>
    form.querySelector("#packing-" + key)
  );
  const temperatureInputs = form.querySelectorAll('input[name="temperature"]');
  const sizeResult = form.querySelector("#shipping-size-result");
  const warning = form.querySelector("#cool-warning .shipping-warning");
  const warningText = warning.querySelector("[data-cool-warning-text]");
  let shippingData = {};
  try {
    const data = JSON.parse(document.getElementById("store-shipping-data").textContent);
    if (data && typeof data === "object" && !Array.isArray(data)) shippingData = data;
  } catch {
    // データがない場合も食品・在庫は動かし、配送は販売店の選択案内に戻す。
  }

  // 正の十進数を扱う。全角数字・小数点を読み替え、入力値自体は書き換えない。
  // 0以下・符号・指数・桁区切りの扱いは未確定。制作では判定前の案内にする。
  const parseMeasurement = (input) => {
    const value = String(input).normalize("NFKC").trim();
    if (!/^(?:\d+(?:\.\d*)?|\.\d+)$/.test(value)) return null;
    const [whole, fraction = ""] = value.split(".");
    const units = BigInt((whole || "0") + fraction);
    return units > 0n ? { units, scale: fraction.length } : null;
  };
  // 小数の上限ちょうどで浮動小数点誤差を出さないよう、桁をそろえて比較する。
  const scaledUnits = (value, scale) => value.units * 10n ** BigInt(scale - value.scale);
  const addDimensions = (values) => {
    const scale = Math.max(...values.map((value) => value.scale));
    return { units: values.reduce((sum, value) => sum + scaledUnits(value, scale), 0n), scale };
  };
  const fits = (value, limit) => {
    const maximum = parseMeasurement(limit);
    if (!maximum) return false;
    const scale = Math.max(value.scale, maximum.scale);
    return scaledUnits(value, scale) <= scaledUnits(maximum, scale);
  };
  const formatMeasurement = (value) => {
    if (value.scale === 0) return String(value.units);
    const digits = String(value.units).padStart(value.scale + 1, "0");
    const fraction = digits.slice(-value.scale).replace(/0+$/, "");
    return digits.slice(0, -value.scale) + (fraction ? "." + fraction : "");
  };
  const setText = (element, value) => {
    // 同じ判定を繰り返しlive領域に書かない。
    if (element.textContent !== value) element.textContent = value;
  };

  const updateShipping = () => {
    const store = Object.hasOwn(shippingData, storeSelect.value) ? shippingData[storeSelect.value] : null;
    const values = packingInputs.map((input) => parseMeasurement(input.value));
    let result = "販売店を選ぶと判定します";
    let notice = "";

    if (store) {
      const carrier = store.carrier;
      const temperature = form.querySelector('input[name="temperature"]:checked')?.value;
      const coolKey = { cold: "refrigerated", frozen: "frozen" }[temperature];
      const cool = coolKey ? carrier.cool?.[coolKey] : null;
      // 温度帯そのものの非対応は寸法未入力でも分かる。上限・追加料金より先に伝える。
      if (coolKey && !cool) {
        notice = "この販売店の運送会社（" + carrier.name + "）は" +
          (temperature === "cold" ? "冷蔵" : "冷凍") + "に対応していません";
      }
      result = "縦・横・高さ・重さを入力すると判定します";
      if (values.every(Boolean)) {
        const total = addDimensions(values.slice(0, 3));
        const weight = values[3];
        const detail = "（3辺合計" + formatMeasurement(total) + "cm・" + formatMeasurement(weight) + "kg）";
        // 規格の重さ上限は運送会社から取得。その他はPHPで販売店の独自規格を結合する。
        const sizes = carrier.sizes.filter((entry) => !store.sizeBased || store.usedSizes.includes(entry.size));
        const size = [...sizes].sort((a, b) => a.size - b.size).find((entry) =>
          fits(total, entry.size) && fits(weight, entry.maxWeightKg)
        );
        if (!size && !store.sizeBased) {
          const maxTotalCm = Math.max(...carrier.sizes.map((entry) => entry.size));
          const maxWeightKg = Math.max(...carrier.sizes.map((entry) => entry.maxWeightKg));
          result = carrier.name + "の上限（3辺合計" + maxTotalCm + "cm・" + maxWeightKg + "kg）を超えています" + detail;
        } else {
          result = !size ? "対応するサイズがありません" + detail : (
            store.sizeBased ? "判定：" + size.size + "サイズ" + detail :
              "この販売店はサイズ別の送料を使いません" + detail
          );
        }
        // クール便は判定サイズの番号ではなく、実際の3辺合計・重さを別に照合する。
        if (cool && !notice) {
          if (!fits(total, cool.maxTotalCm) || !fits(weight, cool.maxWeightKg)) {
            notice = carrier.name + "のクール便は3辺合計" + cool.maxTotalCm + "cm・" + cool.maxWeightKg + "kgまでです";
          } else if (cool.maxLongestCm && values.slice(0, 3).some((value) => !fits(value, cool.maxLongestCm))) {
            notice = carrier.name + "のクール便は最長辺" + cool.maxLongestCm + "cmまでです";
          } else if (size && !(store.sizeBased ? store.coolSurchargeSetBySize[size.size] : store.coolSurchargeSet)) {
            notice = store.sizeBased ? size.size + "サイズのクール便に対応していません" :
              "この販売店はクール便に対応していません";
          }
        }
      }
    }
    setText(sizeResult, result);
    warning.hidden = notice === "";
    setText(warningText, notice);
  };

  let updateTimer;
  const scheduleShipping = () => {
    clearTimeout(updateTimer);
    updateTimer = setTimeout(updateShipping, 300);
  };
  const updateShippingNow = () => {
    clearTimeout(updateTimer);
    updateShipping();
  };
  packingInputs.forEach((input) => {
    input.addEventListener("compositionstart", () => clearTimeout(updateTimer));
    input.addEventListener("input", (event) => {
      if (!event.isComposing) scheduleShipping();
    });
    input.addEventListener("compositionend", scheduleShipping);
  });
  storeSelect.addEventListener("change", updateShippingNow);
  temperatureInputs.forEach((input) => input.addEventListener("change", updateShippingNow));
  updateShipping();

  // 画像操作・送信中の制御・エラーまとめへの自動フォーカス・離脱警告は2-B。
})();
