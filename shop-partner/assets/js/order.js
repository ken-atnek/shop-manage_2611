/* =======================================
 * 熊日モール Client 受注一覧 操作
 * URL: /shop-partner/assets/js/order.js
 * Referenced in: /shop-partner/orders.php
 * Created: 2026-10-17
 * Last updated: 2026-10-19
 * ======================================= */

"use strict";

function getOrderNumber(element) {
  return element.closest(".order-card").querySelector("h3").textContent.trim();
}

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

  head.setAttribute("aria-label", `注文${getOrderNumber(box)}のステータスを変更（現在：${value.textContent}）`);
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

// scriptはbody末尾で読み込む。初期の閉じた状態（hidden・aria-expanded）はHTML側で持つ。
document.querySelectorAll(".select-customer-status[data-selectbox]").forEach((box) => {
  const { head, panel } = getStatusParts(box);
  syncStatus(box);
  head.addEventListener("click", () => toggleStatusBox(box));
  // マウス・タッチで選んだときだけ、選択と同時に閉じる。
  // 矢印キーでは選択が移るたびにchangeが起きるため、表示だけ同期して開いたままにし、Enterで閉じる。
  let pointerSelecting = false;
  panel.addEventListener("pointerdown", () => {
    pointerSelecting = true;
  });
  panel.addEventListener("keydown", (event) => {
    pointerSelecting = false;
    if (event.key === "Enter") {
      event.preventDefault();
      closeStatusBox(box, true);
    } else if (event.key === "Tab") {
      // Tabで一覧の次の操作へ移るときは、開いたメニューを残さない。
      closeStatusBox(box);
    }
  });
  box.querySelectorAll('input[type="radio"]').forEach((radio) => {
    radio.addEventListener("click", () => {
      // 選択済み項目の再クリックではchangeが出ないため、ここで閉じる。
      if (pointerSelecting && radio.value === getStatusParts(box).hidden.value) {
        pointerSelecting = false;
        closeStatusBox(box, true);
      }
    });
    radio.addEventListener("change", () => {
      syncStatus(box);
      if (pointerSelecting) closeStatusBox(box, true);
      pointerSelecting = false;
    });
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape" || !openStatusBox) return;
  event.preventDefault();
  closeStatusBox(openStatusBox, true);
});

// ===== メモ／入稿画像の展開 =====
function toggleOrderDetail(button) {
  const panel = document.getElementById(button.getAttribute("aria-controls"));
  if (!panel) return;
  const open = toggleAccordion(button, panel);
  const contentNote = button.classList.contains("has-content") ? "（メモ・入稿画像あり）" : "";
  const name = `注文${getOrderNumber(button)}のメモ・入稿画像を${open ? "閉じる" : "開く"}${contentNote}`;
  button.setAttribute("aria-label", name);
  button.title = name;
}

// ===== 入稿画像の拡大表示（全注文で1つのdialogを共用） =====
const imageDialog = document.getElementById("submission-preview");
const previewImage = imageDialog.querySelector(".preview-image");
const previewFileName = imageDialog.querySelector("#preview-file-name");
let imagePreviewOpener = null;

function openImagePreview(button) {
  imagePreviewOpener = button;
  previewImage.src = button.dataset.imageSrc;
  previewImage.alt = button.dataset.imageAlt;
  previewFileName.textContent = button.dataset.fileName;
  imageDialog.showModal();
}

imageDialog.querySelector(".dialog-close").addEventListener("click", () => {
  imageDialog.close();
});

imageDialog.addEventListener("click", (event) => {
  if (event.target !== imageDialog) return;
  const bounds = imageDialog.getBoundingClientRect();
  // dialogの余白を押しただけでは閉じず、外側の背景クリックだけを扱う。
  if (event.clientX < bounds.left || event.clientX > bounds.right ||
      event.clientY < bounds.top || event.clientY > bounds.bottom) {
    imageDialog.close();
  }
});

// Escはdialog標準の動作で閉じる。どの閉じ方でも元の拡大ボタンへ戻す。
imageDialog.addEventListener("close", () => {
  imagePreviewOpener?.focus();
  imagePreviewOpener = null;
});

// ===== 一覧のクリック（注文が増えても1つのリスナーで扱う） =====
document.addEventListener("click", (event) => {
  if (!(event.target instanceof Element)) return;
  if (openStatusBox && !openStatusBox.contains(event.target)) closeStatusBox(openStatusBox);

  const orderToggle = event.target.closest(".order-toggle");
  if (orderToggle) {
    toggleOrderDetail(orderToggle);
    return;
  }
  const imagePreview = event.target.closest(".image-preview");
  if (imagePreview) openImagePreview(imagePreview);
});
