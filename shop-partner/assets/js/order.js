/* =======================================
 * 熊日モール Client 受注一覧 操作
 * URL: /shop-partner/assets/js/order.js
 * Referenced in: /shop-partner/orders.php
 * Created: 2026-10-17
 * Last updated: 2026-10-17
 * ======================================= */

"use strict";

// ===== ステータス（radio・表示値・hiddenの同期） =====
let openStatusBox = null;

function getStatusParts(box) {
  return {
    head: box.querySelector(".selectbox__head"),
    value: box.querySelector("[data-selectbox-value]"),
    hidden: box.querySelector("[data-selectbox-hidden]"),
    panel: box.querySelector(".list-wrapper"),
  };
}

function syncStatus(box) {
  const { head, value, hidden } = getStatusParts(box);
  const checked = box.querySelector('input[type="radio"]:checked');
  // 初期値も選択済みとして扱い、既存mixinの矢印表示と同期する。
  box.classList.toggle("is-selected", Boolean(checked));
  if (!checked) return;

  const label = checked.labels[0];
  value.textContent = label.textContent.trim();
  hidden.value = checked.value;
  [...head.classList].filter((name) => name.startsWith("status-")).forEach((name) => {
    head.classList.remove(name);
  });
  head.classList.add(...[...label.classList].filter((name) => name.startsWith("status-")));

  const number = box.closest(".order-card").querySelector("h3").textContent.trim();
  const name = `注文${number}のステータスを変更（現在：${value.textContent}）`;
  head.setAttribute("aria-label", name);
  head.title = name;
  // 保存方法が未確定のため、値と表示だけを同期し、通信・更新日時の変更はしない。
}

function closeStatusBox(box, restoreFocus = false) {
  const { head, panel } = getStatusParts(box);
  box.classList.remove("is-open");
  head.setAttribute("aria-expanded", "false");
  panel.hidden = true;
  if (openStatusBox === box) openStatusBox = null;
  if (restoreFocus) head.focus();
}

function toggleStatusBox(box) {
  const { head, panel } = getStatusParts(box);
  if (openStatusBox === box) {
    closeStatusBox(box);
    return;
  }
  if (openStatusBox) closeStatusBox(openStatusBox);

  panel.hidden = false;
  box.classList.add("is-open");
  head.setAttribute("aria-expanded", "true");
  openStatusBox = box;
  box.querySelector('input[type="radio"]:checked')?.focus();
}

// scriptはbody末尾で読み込む。検索パネルや汎用ツールチップの処理は持ち込まない。
document.querySelectorAll(".select-customer-status[data-selectbox]").forEach((box) => {
  const { head } = getStatusParts(box);
  syncStatus(box);
  closeStatusBox(box);
  head.addEventListener("click", () => toggleStatusBox(box));
  box.querySelectorAll('input[type="radio"]').forEach((radio) => {
    radio.addEventListener("click", () => {
      // 選択済み項目の再クリックではchangeが出ないため、ここで閉じる。
      if (radio.value === getStatusParts(box).hidden.value) closeStatusBox(box, true);
    });
    radio.addEventListener("change", () => {
      syncStatus(box);
      closeStatusBox(box, true);
    });
  });
});

// ===== 外側クリック・メモ／入稿画像の展開 =====
document.addEventListener("click", (event) => {
  if (!(event.target instanceof Element)) return;
  if (openStatusBox && !openStatusBox.contains(event.target)) closeStatusBox(openStatusBox);

  const button = event.target.closest(".order-toggle");
  if (!button) return;
  const panel = document.getElementById(button.getAttribute("aria-controls"));
  if (!panel) return;

  const expanded = button.getAttribute("aria-expanded") === "true";
  const number = button.closest(".order-card").querySelector("h3").textContent.trim();
  const contentNote = button.classList.contains("has-content") ? "（メモ・入稿画像あり）" : "";
  const name = `注文${number}のメモ・入稿画像を${expanded ? "開く" : "閉じる"}${contentNote}`;
  button.setAttribute("aria-expanded", String(!expanded));
  button.setAttribute("aria-label", name);
  button.title = name;
  panel.hidden = expanded;
  // 行クリックによる開閉とパネルの中身はステップ5で追加する。
});

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape" || !openStatusBox) return;
  event.preventDefault();
  closeStatusBox(openStatusBox, true);
});
