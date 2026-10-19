/* =======================================
 * 熊日モール Client 共通操作
 * URL: /shop-partner/assets/js/common.js
 * Referenced in: /shop-partner/orders.php
 * Created: 2026-10-19
 * Last updated: 2026-10-19
 * ======================================= */

"use strict";

// grid 0fr/1fr のアコーディオン。閉じたパネルはinertで操作・読み上げの対象から外す。
function toggleAccordion(button, panel) {
  const open = button.getAttribute("aria-expanded") !== "true";
  button.setAttribute("aria-expanded", String(open));
  panel.inert = !open;
  return open;
}

// ===== 詳細検索（パネル内だけをクリアし、キーワード・画面固有の絞り込みは維持） =====
{
  const detailSearchButton = document.querySelector(".detail-toggle");

  if (detailSearchButton) {
    const panelId = detailSearchButton.getAttribute("aria-controls");
    const detailSearchPanel = panelId ? document.getElementById(panelId) : null;

    if (detailSearchPanel) {
      // 開閉時にフォーカスは動かさず、詳細検索ボタンに残す。
      detailSearchButton.addEventListener("click", () => toggleAccordion(detailSearchButton, detailSearchPanel));

      detailSearchPanel.querySelector(".clear-conditions")?.addEventListener("click", () => {
        detailSearchPanel.querySelectorAll("input").forEach((input) => {
          if (input.type === "checkbox") {
            input.checked = false;
          } else {
            input.value = "";
          }
        });
        detailSearchPanel.querySelector("input")?.focus();
      });
    }
  }
}
