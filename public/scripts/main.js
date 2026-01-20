export {};
document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("menuBtn");
  const menu = document.getElementById("mobileMenu");

  if (btn && menu) {
    btn.addEventListener("click", () => {
      // toggle dropdown smooth
      if (menu.classList.contains("max-h-0")) {
        menu.classList.remove("max-h-0");
        menu.classList.add("max-h-96"); // ganti sesuai tinggi konten
      } else {
        menu.classList.remove("max-h-96");
        menu.classList.add("max-h-0");
      }
    });
  }
});
