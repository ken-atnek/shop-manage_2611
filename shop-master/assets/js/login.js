// shop-partner/assets/js/login.js と同じ内容。変えるときは両方を直す。
/* =======================================
 * 熊日モール Master・Client共通 ログインまわりの操作
 * URL: /shop-partner/assets/js/login.js
 * Referenced in: /shop-partner/login.php・/shop-partner/password-forgot.php・/shop-partner/password-reset.php
 * Created: 2026-10-23
 * Last updated: 2026-10-23
 * ======================================= */

"use strict";

(() => {
  // ===== パスワードの表示切り替え =====
  // 名前は「パスワードを表示」に固定し、状態はaria-pressedで伝える。JSが使えないときはボタンを出さない。
  const toggles = document.querySelectorAll(".password-toggle");
  toggles.forEach((button) => {
    const input = document.getElementById(button.getAttribute("aria-controls"));
    if (!input) return;
    button.hidden = false;
    button.addEventListener("click", () => {
      const show = button.getAttribute("aria-pressed") !== "true";
      input.type = show ? "text" : "password";
      button.setAttribute("aria-pressed", String(show));
    });
  });

  const hidePasswords = () => {
    toggles.forEach((button) => {
      const input = document.getElementById(button.getAttribute("aria-controls"));
      if (input) input.type = "password";
      button.setAttribute("aria-pressed", "false");
    });
  };

  // ===== 送信中の二重送信防止 =====
  const busyButtons = document.querySelectorAll("form button[data-busy-label]");
  busyButtons.forEach((button) => {
    button.dataset.label = button.textContent;
    button.form.addEventListener("submit", (event) => {
      if (event.submitter && event.submitter !== button) return;
      // 送る前にパスワードを隠し、表示中の値をブラウザに残さない。
      hidePasswords();
      // 押した直後に無効にすると送信が止まるため、送信を始めてから切り替える。
      setTimeout(() => {
        button.disabled = true;
        button.textContent = button.dataset.busyLabel;
      });
    });
  });

  // ブラウザの「戻る」で戻ったときは送信前の状態に戻す。
  window.addEventListener("pageshow", (event) => {
    if (!event.persisted) return;
    busyButtons.forEach((button) => {
      button.textContent = button.dataset.label;
      // 再送ボタンの押せる・押せないはlogin-code.jsが決める。
      if (!button.classList.contains("resend-button")) button.disabled = false;
    });
  });
})();
