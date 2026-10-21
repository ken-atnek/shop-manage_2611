/* =======================================
 * 熊日モール Client 店舗フォーム 配送・送料の切り替え（制作2回目）
 * URL: /shop-partner/assets/js/store-form.js
 * Referenced in: /shop-partner/store-form.php・/docs/design/CLIENT_STORE_FORM.md
 * Created: 2026-10-21
 * Last updated: 2026-10-21
 * ======================================= */

"use strict";

(() => {
  const form = document.querySelector(".store-form");
  const carrierData = document.getElementById("store-carrier-data");
  if (!form || !carrierData) return;
  // Bladeでは同じ形の本番規格データへ置き換える。値保持は既存の入力要素で行う。
  const carriers = JSON.parse(carrierData.textContent);
  const carrierSelect = form.querySelector("#store-carrier");
  const sizeBased = form.querySelector("#store-size-based");
  const sizeHelp = form.querySelector("#store-size-based-help");
  const sizeNotice = form.querySelector("#store-size-notice");
  const usedSizeFields = form.querySelector("#store-used-sizes");
  const sizeChoices = usedSizeFields.querySelector(".check-group");
  const tabs = form.querySelector("#store-size-tabs");
  const commonMode = form.querySelector("#shipping-mode-common");
  const sizeMode = form.querySelector("#shipping-mode-by_size");
  const tables = [...form.querySelectorAll(".shipping-table")];
  const rules = form.querySelector("#store-carrier-rules");
  const freeShipping = form.querySelector("#store-free-shipping");
  const freeThreshold = form.querySelector("#store-free-shipping-threshold");
  const temperatureLabels = { refrigerated: "冷蔵", frozen: "冷凍" };
  let selectedSizes = new Set([...sizeChoices.querySelectorAll("input:checked")].map((input) => Number(input.value)));
  let sizesInitialized = form.dataset.sizesInitialized === "true";
  let activeSize = Number(tabs.querySelector('[aria-selected="true"]')?.id.replace("shipping-tab-", "")) || null;
  const currentCarrier = () => carriers[carrierSelect.value] || null;

  // 同じ文言を続けて通知する場合も、常設のlive領域で読み上げられるよう一度空にする。
  const noticeTimers = new WeakMap();
  const announce = (region, text) => {
    clearTimeout(noticeTimers.get(region));
    region.textContent = "";
    noticeTimers.set(region, setTimeout(() => { region.textContent = text; }, 0));
  };
  const element = (tag, text, className) => {
    const node = document.createElement(tag);
    if (text !== undefined) node.textContent = text;
    if (className) node.className = className;
    return node;
  };

  // ===== 規格の読み取り専用表示 =====
  const renderRules = () => {
    const carrier = currentCarrier();
    const heading = rules.querySelector("#carrier-rules-heading");
    rules.replaceChildren(heading);
    if (!carrier) {
      rules.append(element("p", "運送会社を選ぶと表示します", "form-help"));
      return;
    }
    rules.append(element("p", "サイズは3辺の合計（cm）、重さは1個あたりの上限です。", "form-help"));
    const sizes = element("dl", undefined, "carrier-sizes");
    carrier.sizes.forEach(({ size, maxWeightKg }) => {
      const row = element("div");
      row.append(element("dt", `${size}サイズ`), element("dd", `${maxWeightKg}kgまで`));
      sizes.append(row);
    });
    const cool = element("dl", undefined, "carrier-cool");
    Object.entries(temperatureLabels).forEach(([temperature, label]) => {
      const rule = carrier.cool?.[temperature];
      const row = element("div");
      const text = rule
        ? `3辺合計${rule.maxTotalCm}cm・${rule.maxWeightKg}kgまで${rule.maxLongestCm ? `（最長辺${rule.maxLongestCm}cmまで）` : ""}`
        : "非対応";
      row.append(element("dt", label), element("dd", text));
      cool.append(row);
    });
    rules.append(sizes, cool);
  };

  // SPECの決定4。直前サイズは全標準サイズから求め、使用サイズの選択には左右されない。
  const canUseCool = (carrier, size, temperature) => {
    const rule = carrier?.cool?.[temperature];
    if (!rule) return false;
    if (size === null) return true;
    const index = carrier.sizes.findIndex((item) => item.size === size);
    if (index < 0) return false;
    if (index === 0) return true;
    const previous = carrier.sizes[index - 1];
    const current = carrier.sizes[index];
    return Math.min(size, rule.maxTotalCm) > previous.size
      || Math.min(current.maxWeightKg, rule.maxWeightKg) > previous.maxWeightKg;
  };

  // ===== 方式・サイズ・温度帯の表示と送信対象 =====
  const syncTables = () => {
    const carrier = currentCarrier();
    const bySize = Boolean(carrier && sizeBased.checked);
    sizeBased.disabled = !carrier;
    sizeHelp.hidden = Boolean(carrier);
    usedSizeFields.hidden = !bySize;
    usedSizeFields.disabled = !bySize;
    commonMode.hidden = bySize;
    commonMode.disabled = bySize;
    sizeMode.hidden = !bySize;
    sizeMode.disabled = !bySize;
    tables.forEach((table) => {
      const size = table.dataset.shippingSize === "common" ? null : Number(table.dataset.shippingSize);
      const used = size === null || selectedSizes.has(size);
      // 未使用サイズは無効。使用中の非表示タブはhiddenだけ（親の方式fieldsetが送信可否を制御）。
      table.disabled = !used;
      table.hidden = size !== null && (!bySize || !used || activeSize !== size);
      if (size !== null) {
        const specification = table.querySelector("[data-size-spec]");
        const definition = carrier?.sizes.find((item) => item.size === size);
        specification.textContent = definition ? `3辺合計${size}cm・${definition.maxWeightKg}kgまで` : "";
        specification.hidden = !definition;
        table.removeAttribute("aria-labelledby");
        table.removeAttribute("aria-label");
        table.setAttribute(used ? "aria-labelledby" : "aria-label", used ? `shipping-tab-${size}` : `${size}サイズ`);
      }
      let hasCool = false;
      table.querySelectorAll("[data-cool-temperature]").forEach((field) => {
        const temperature = field.dataset.coolTemperature;
        const supported = canUseCool(carrier, size, temperature);
        field.hidden = !supported;
        field.disabled = !supported;
        hasCool ||= supported;
        const limit = field.querySelector("[data-yupack-limit]");
        if (limit) {
          const showLimit = carrierSelect.value === "yupack" && supported;
          limit.hidden = !showLimit;
          const helpId = table.querySelector("[data-cool-help]").id;
          field.querySelector("input").setAttribute("aria-describedby", helpId + (showLimit ? ` ${limit.id}` : ""));
        }
      });
      table.querySelector("[data-cool-unselected]").hidden = Boolean(carrier);
      table.querySelector("[data-cool-unavailable]").hidden = !carrier || hasCool;
      table.querySelector("[data-cool-help]").hidden = !hasCool;
    });
  };
  // 横方向だけを移動し、送料入力中のページのスクロール位置は維持する。
  const revealActiveTab = () => {
    const tab = tabs.querySelector('[aria-selected="true"]');
    if (!tab || !tabs.clientWidth) return;
    const strip = tabs.getBoundingClientRect();
    const selected = tab.getBoundingClientRect();
    if (selected.left < strip.left) tabs.scrollLeft += selected.left - strip.left;
    else if (selected.right > strip.right) tabs.scrollLeft += selected.right - strip.right;
  };
  const activateTab = (size, moveFocus = false) => {
    activeSize = size;
    tabs.querySelectorAll('[role="tab"]').forEach((tab) => {
      const active = tab.id === `shipping-tab-${size}`;
      tab.setAttribute("aria-selected", String(active));
      tab.tabIndex = active ? 0 : -1;
    });
    syncTables();
    if (moveFocus) tabs.querySelector(`#shipping-tab-${size}`)?.focus();
    revealActiveTab();
  };
  const renderTabs = () => {
    const used = (currentCarrier()?.sizes || []).map((item) => item.size).filter((size) => selectedSizes.has(size));
    if (!used.includes(activeSize)) activeSize = used[0] ?? null;
    tabs.replaceChildren();
    used.forEach((size) => {
      const button = element("button", `${size}サイズ`);
      button.type = "button";
      button.id = `shipping-tab-${size}`;
      button.setAttribute("role", "tab");
      button.setAttribute("aria-controls", `shipping-panel-${size}`);
      tabs.append(button);
    });
    activateTab(activeSize);
  };
  const renderSizeChoices = () => {
    sizeChoices.replaceChildren();
    (currentCarrier()?.sizes || []).forEach(({ size }) => {
      const label = element("label", undefined, "form-check");
      label.htmlFor = `store-size-${size}`;
      const input = element("input");
      input.type = "checkbox";
      input.id = `store-size-${size}`;
      input.name = "used_sizes[]";
      input.value = String(size);
      input.checked = selectedSizes.has(size);
      label.append(input, element("span", `${size}サイズ`));
      sizeChoices.append(label);
    });
  };
  carrierSelect.addEventListener("change", () => {
    const allowed = new Set((currentCarrier()?.sizes || []).map((item) => item.size));
    const removed = [...selectedSizes].filter((size) => !allowed.has(size));
    selectedSizes = new Set([...selectedSizes].filter((size) => allowed.has(size)));
    announce(sizeNotice, removed.length ? `${removed.map((size) => `${size}サイズ`).join("・")}を外しました` : "");
    renderRules();
    renderSizeChoices();
    renderTabs();
  });
  sizeBased.addEventListener("change", () => {
    if (sizeBased.checked && !sizesInitialized && currentCarrier()) {
      selectedSizes = new Set(currentCarrier().sizes.map((item) => item.size));
      sizesInitialized = true;
      form.dataset.sizesInitialized = "true";
      renderSizeChoices();
    }
    renderTabs();
  });
  sizeChoices.addEventListener("change", (event) => {
    const input = event.target.closest('input[name="used_sizes[]"]');
    if (!input) return;
    const size = Number(input.value);
    if (input.checked) selectedSizes.add(size);
    else selectedSizes.delete(size);
    renderTabs();
  });
  tabs.addEventListener("click", (event) => {
    const tab = event.target.closest('[role="tab"]');
    if (tab) activateTab(Number(tab.id.replace("shipping-tab-", "")), true);
  });
  tabs.addEventListener("keydown", (event) => {
    const tab = event.target.closest('[role="tab"]');
    if (!tab || !["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return;
    event.preventDefault();
    const buttons = [...tabs.querySelectorAll('[role="tab"]')];
    const index = buttons.indexOf(tab);
    const next = event.key === "Home" ? 0 : event.key === "End" ? buttons.length - 1
      : (index + (event.key === "ArrowRight" ? 1 : -1) + buttons.length) % buttons.length;
    activateTab(Number(buttons[next].id.replace("shipping-tab-", "")), true);
  });

  // 折り返し・画面幅・文字サイズに合わせてフォーカス欄の上側余白を確保する。
  new ResizeObserver(() => {
    const height = tabs.getBoundingClientRect().height;
    if (height) sizeMode.style.setProperty("--store-tabs-height", `${height}px`);
    revealActiveTab();
  }).observe(tabs);
  sizeMode.addEventListener("focusin", (event) => {
    const field = event.target;
    if (!field.matches("input, button") || !field.closest('[role="tabpanel"]')) return;
    // Tab移動で既に画面内と判断された欄も、stickyの下に隠れる場合だけ補正する。
    requestAnimationFrame(() => {
      const strip = tabs.getBoundingClientRect();
      const position = field.getBoundingClientRect();
      if (strip.top <= 0 && position.top < strip.bottom && position.bottom > 0) {
        window.scrollBy({ top: position.top - parseFloat(getComputedStyle(field).scrollMarginTop), behavior: "instant" });
      }
    });
  });

  // ===== 一括入力（保存時の検証・エラー表示は3回目） =====
  form.addEventListener("click", (event) => {
    const button = event.target.closest("[data-bulk-scope]");
    if (!button || button.matches(":disabled")) return;
    const table = button.closest(".shipping-table");
    const input = document.getElementById(button.dataset.bulkInput);
    const notice = table.querySelector(".bulk-notice");
    // 全角数字だけを正規化し、補助入力欄そのものは書き換えない。
    const value = input.value.replace(/[０-９]/g, (digit) => String.fromCharCode(digit.charCodeAt(0) - 0xfee0));
    const amount = Number(value);
    if (!/^[0-9]+$/.test(value) || !Number.isInteger(amount) || amount < 0 || amount > 99999) {
      announce(notice, "0〜99,999の整数を入力してください");
      return;
    }
    const scope = button.dataset.bulkScope === "region" ? button.closest(".shipping-region") : table;
    const inputs = [...scope.querySelectorAll("[data-prefecture-fee]")];
    inputs.forEach((field) => { field.value = String(amount); });
    announce(notice, `${inputs.length}件に入力しました`);
  });

  // ===== 送料無料（金額は保持し、オフは送らない） =====
  const syncFreeShipping = () => { freeThreshold.disabled = !freeShipping.checked; };
  freeShipping.addEventListener("change", syncFreeShipping);
  renderRules();
  renderTabs();
  syncFreeShipping();
})();
