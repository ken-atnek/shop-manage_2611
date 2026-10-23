/* =======================================
 * 熊日モール Master 確認コードの再送までの残り秒数
 * URL: /shop-master/assets/js/login-code.js
 * Referenced in: /shop-master/login-code.php
 * Created: 2026-10-23
 * Last updated: 2026-10-23
 * ======================================= */

"use strict";

(() => {
  const button = document.querySelector(".resend-button");
  const timer = document.getElementById("resend-timer");
  const notice = document.getElementById("resend-notice");
  if (!button || !timer) return;

  const seconds = timer.querySelector(".resend-seconds");
  // 残り秒数はサーバーが出した値から数える。時刻で計算し、タブが裏にあってもずれないようにする。
  const endTime = Date.now() + (Number(button.dataset.resendSeconds) || 0) * 1000;
  let intervalId = null;

  const update = () => {
    const remaining = Math.max(0, Math.ceil((endTime - Date.now()) / 1000));
    if (remaining > 0) {
      seconds.textContent = remaining;
      return false;
    }
    const wasDisabled = button.disabled;
    button.disabled = false;
    button.removeAttribute("aria-describedby");
    timer.hidden = true;
    // 残り秒数は毎秒読み上げず、押せるようになったときに1回だけ知らせる。
    if (wasDisabled && notice) notice.textContent = "コードを再送できます";
    return true;
  };

  const start = () => {
    if (update()) return;
    intervalId = setInterval(() => {
      if (update()) clearInterval(intervalId);
    }, 1000);
  };

  start();

  // ブラウザの「戻る」で戻ったときは、残り秒数から押せるかを決め直す。
  window.addEventListener("pageshow", (event) => {
    if (!event.persisted) return;
    clearInterval(intervalId);
    if (Date.now() < endTime) button.disabled = true;
    start();
  });
})();
