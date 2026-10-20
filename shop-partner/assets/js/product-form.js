/* =======================================
 * 熊日モール Client 商品フォーム 状態・画像・送信操作（制作2-A／2-B）
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
    // データがない場合も食品・在庫は動かし、配送は店舗の選択案内に戻す。
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
    let result = "店舗を選ぶと判定します";
    let notice = "";

    if (store) {
      const carrier = store.carrier;
      const temperature = form.querySelector('input[name="temperature"]:checked')?.value;
      const coolKey = { cold: "refrigerated", frozen: "frozen" }[temperature];
      const cool = coolKey ? carrier.cool?.[coolKey] : null;
      // 温度帯そのものの非対応は寸法未入力でも分かる。上限・追加料金より先に伝える。
      if (coolKey && !cool) {
        notice = "この店舗の運送会社（" + carrier.name + "）は" +
          (temperature === "cold" ? "冷蔵" : "冷凍") + "に対応していません";
      }
      result = "縦・横・高さ・重さを入力すると判定します";
      if (values.every(Boolean)) {
        const total = addDimensions(values.slice(0, 3));
        const weight = values[3];
        const detail = "（3辺合計" + formatMeasurement(total) + "cm・" + formatMeasurement(weight) + "kg）";
        // 規格の重さ上限は運送会社から取得。その他はPHPで店舗の独自規格を結合する。
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
              "この店舗はサイズ別の送料を使いません" + detail
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
              "この店舗はクール便に対応していません";
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

  // ===== 変更あり・送信・戻る（保存処理そのものはバックエンドが組み込む） =====
  const saveButton = form.querySelector("#product-save");
  const saveLabel = saveButton.textContent;
  let hasChanges = false;
  let submitting = false;
  const warnBeforeLeaving = (event) => {
    if (!hasChanges || submitting) return;
    event.preventDefault();
    event.returnValue = true;
  };
  const markChanged = () => {
    hasChanges = true;
    if (!submitting) window.addEventListener("beforeunload", warnBeforeLeaving);
  };
  form.addEventListener("input", markChanged);
  form.addEventListener("change", markChanged);
  form.addEventListener("submit", (event) => {
    if (submitting) {
      event.preventDefault();
      return;
    }
    submitting = true;
    window.removeEventListener("beforeunload", warnBeforeLeaving);
    saveButton.disabled = true;
    saveButton.textContent = "保存中…";
    form.setAttribute("aria-busy", "true");
  });
  window.addEventListener("pageshow", (event) => {
    if (!event.persisted) return;
    submitting = false;
    saveButton.disabled = false;
    saveButton.textContent = saveLabel;
    form.removeAttribute("aria-busy");
    // 復元された入力・画像は未保存のまま。再度離れる場合も警告する。
    if (hasChanges) window.addEventListener("beforeunload", warnBeforeLeaving);
  });

  // ===== 商品画像（順番・削除は保存時に反映。名前・IDは制作用の仮契約） =====
  const imageList = form.querySelector("#product-image-list");
  const imageRow = form.querySelector("#product-images-row");
  const fileInput = form.querySelector("#product-images");
  const dropzone = form.querySelector("#product-image-dropzone");
  const imageTemplate = form.querySelector("#product-image-template");
  const imageDeleteFields = form.querySelector("#image-delete-fields");
  const imageStatus = form.querySelector("#image-status");
  // 要素にFileとObject URLを対応させ、newの番号はfilesの現在の順番に合わせる。
  const newImages = new Map();
  const imageItems = () => [...imageList.children];
  const announceImages = (message) => { imageStatus.textContent = message; };

  const syncImages = () => {
    const items = imageItems();
    const transfer = new DataTransfer();
    let newIndex = 0;
    items.forEach((item, index) => {
      const newImage = newImages.get(item);
      if (newImage) {
        transfer.items.add(newImage.file);
        // new:1はimages[0]。削除・並べ替えのたびに欠番なく採番する。
        item.dataset.imageKey = "new:" + (++newIndex);
      }
      item.querySelector('input[name="image_order[]"]').value = item.dataset.imageKey;
      item.querySelector("img").alt = "商品画像" + (index + 1);
      let badge = item.querySelector(".main-image-label");
      if (!badge) {
        badge = document.createElement("span");
        badge.className = "main-image-label";
        badge.textContent = "メイン";
        item.querySelector(".image-preview").append(badge);
      }
      badge.hidden = index !== 0;
      const labels = { previous: "前へ", next: "後ろへ", delete: "削除" };
      item.querySelectorAll("[data-image-action]").forEach((button) => {
        const action = button.dataset.imageAction;
        const label = "商品画像" + (index + 1) + "を" + labels[action];
        button.setAttribute("aria-label", label);
        button.title = label;
        button.disabled = (action === "previous" && index === 0) || (action === "next" && index === items.length - 1);
      });
    });
    // ファイル選択で置き換わったfilesも、追加済みの新規画像全体から作り直す。
    fileInput.files = transfer.files;
    imageRow.hidden = items.length === 0;
  };

  imageList.addEventListener("click", (event) => {
    const button = event.target.closest("button[data-image-action]");
    if (!button || button.disabled) return;
    const item = button.closest("li");
    const items = imageItems();
    const index = items.indexOf(item);
    const action = button.dataset.imageAction;
    if (action === "delete") {
      const nextItem = items[index + 1];
      const newImage = newImages.get(item);
      if (newImage) {
        URL.revokeObjectURL(newImage.url);
        newImages.delete(item);
      } else {
        const deleted = document.createElement("input");
        deleted.type = "hidden";
        deleted.name = "image_delete[]";
        deleted.value = item.dataset.imageKey.slice("existing:".length);
        imageDeleteFields.append(deleted);
      }
      item.remove();
      syncImages();
      const nextButton = nextItem && [...nextItem.querySelectorAll("button")].find((control) => !control.disabled);
      (nextButton || fileInput).focus();
      announceImages("商品画像" + (index + 1) + "を削除しました。保存すると反映されます");
    } else {
      const targetIndex = index + (action === "previous" ? -1 : 1);
      if (action === "previous") imageList.insertBefore(item, items[targetIndex]);
      else imageList.insertBefore(items[targetIndex], item);
      syncImages();
      const focusButton = button.disabled
        ? item.querySelector('[data-image-action="' + (action === "previous" ? "next" : "previous") + '"]')
        : button;
      focusButton.focus();
      announceImages("商品画像" + (index + 1) + "を" + (targetIndex + 1) + "番目に移動しました");
    }
    markChanged();
  });

  const addImages = (files) => {
    let added = 0;
    let rejected = false;
    files.forEach((file) => {
      // 形式・容量・枚数の上限は未確定。画像かどうかの確認だけを行う。
      if (!file.type.startsWith("image/")) {
        rejected = true;
        return;
      }
      const item = imageTemplate.content.firstElementChild.cloneNode(true);
      const url = URL.createObjectURL(file);
      item.querySelector("img").src = url;
      newImages.set(item, { file, url });
      imageList.append(item);
      added += 1;
    });
    syncImages();
    if (added) markChanged();
    if (added || rejected) {
      announceImages((added ? "画像を" + added + "枚追加しました。保存すると反映されます" : "") +
        (rejected ? (added ? "。" : "") + "画像ファイルを選択してください" : ""));
    }
  };
  fileInput.addEventListener("change", () => addImages([...fileInput.files]));
  dropzone.addEventListener("click", (event) => {
    if (!event.target.closest("label, input")) fileInput.click();
  });
  let dragDepth = 0;
  const isFileDrag = (event) => [...(event.dataTransfer?.types || [])].includes("Files");
  dropzone.addEventListener("dragenter", (event) => {
    if (!isFileDrag(event)) return;
    event.preventDefault();
    dragDepth += 1;
    dropzone.classList.add("is-dragover");
  });
  dropzone.addEventListener("dragover", (event) => {
    if (!isFileDrag(event)) return;
    event.preventDefault();
    event.dataTransfer.dropEffect = "copy";
  });
  dropzone.addEventListener("dragleave", () => {
    dragDepth = Math.max(0, dragDepth - 1);
    if (dragDepth === 0) dropzone.classList.remove("is-dragover");
  });
  dropzone.addEventListener("drop", (event) => {
    event.preventDefault();
    dragDepth = 0;
    dropzone.classList.remove("is-dragover");
    const files = [...(event.dataTransfer?.files || [])];
    if (files.length) addImages(files);
    else announceImages("画像ファイルを選択してください");
  });
  window.addEventListener("pagehide", (event) => {
    // bfcacheに入る場合はサムネイルも保持し、戻った画面で操作を続けられるようにする。
    if (!event.persisted) newImages.forEach((image) => URL.revokeObjectURL(image.url));
  });
  syncImages();

  // サーバーからの入力エラー表示を最初に伝える。保存完了メッセージには移動しない。
  form.closest("main").querySelector("#product-errors")?.focus();
})();
