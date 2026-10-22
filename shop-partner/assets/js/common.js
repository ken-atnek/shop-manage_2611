/* =======================================
 * 熊日モール Client 共通操作
 * URL: /shop-partner/assets/js/common.js
 * Referenced in: /shop-partner/orders.php・/shop-partner/products.php・/shop-partner/sales.php ほか
 * Created: 2026-10-19
 * Last updated: 2026-10-22
 * ======================================= */

"use strict";

// ===== 共通サイドバー（1194px以下は保存しないオーバーレイ） =====
{
  const sidebar = document.querySelector(".admin-sidebar");
  const button = sidebar?.querySelector(".sidebar-toggle");
  if (button) {
    const root = document.documentElement;
    const tablet = window.matchMedia("(max-width: 1194px)");
    const storageKey = "kumanichi-client-sidebar";
    let desktopCollapsed = false;
    try {
      desktopCollapsed = localStorage.getItem(storageKey) === "collapsed";
    } catch {
      // 保存できない環境では、このページ内だけで開閉する。
    }

    const setCollapsed = (collapsed, restoreFocus = false) => {
      root.classList.toggle("sidebar-collapsed", collapsed);
      button.setAttribute("aria-expanded", String(!collapsed));
      const label = collapsed ? "メニューを開く" : "メニューを畳む";
      button.setAttribute("aria-label", label);
      button.title = label;
      if (restoreFocus) button.focus();
    };
    const closeOverlay = () => {
      if (!tablet.matches || root.classList.contains("sidebar-collapsed")) return;
      setCollapsed(true, true);
    };
    const resetMode = () => {
      root.classList.remove("sidebar-ready");
      setCollapsed(tablet.matches || desktopCollapsed);
    };

    resetMode();
    button.addEventListener("click", () => {
      root.classList.add("sidebar-ready");
      const collapsed = !root.classList.contains("sidebar-collapsed");
      setCollapsed(collapsed, tablet.matches && collapsed);
      if (!tablet.matches) {
        desktopCollapsed = collapsed;
        try {
          localStorage.setItem(storageKey, collapsed ? "collapsed" : "expanded");
        } catch {
          // storage拒否時も開閉と他の共通操作を継続する。
        }
      }
    });
    tablet.addEventListener("change", resetMode);
    // 戻る／進むで復元された場合も、タブレットのオーバーレイは閉じる。
    window.addEventListener("pageshow", resetMode);
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && tablet.matches && !root.classList.contains("sidebar-collapsed")) {
        event.preventDefault();
        closeOverlay();
      }
    });
    document.addEventListener("click", (event) => {
      if (!sidebar.contains(event.target)) closeOverlay();
    });
  }
}

// ===== セレクトの自動送信（data-auto-submitを付けた欄だけ） =====
document.querySelectorAll("select[data-auto-submit]").forEach((select) => {
  select.addEventListener("change", () => {
    select.form?.requestSubmit();
  });
});

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
