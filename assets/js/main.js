const CART_KEY = "fourtech_cart";
const money = (n) => new Intl.NumberFormat("vi-VN").format(n) + " đ";
const getCart = () => JSON.parse(localStorage.getItem(CART_KEY) || "[]");
const saveCart = (cart) => localStorage.setItem(CART_KEY, JSON.stringify(cart));
function updateCartCount() {
  const el = document.querySelectorAll("[data-cart-count]");
  const count = getCart().reduce((s, p) => s + p.qty, 0);
  el.forEach((x) => (x.textContent = count));
}
function addToCart(id) {
  const cart = getCart();
  const item = cart.find((p) => p.id === Number(id));
  if (item) item.qty++;
  else cart.push({ id: Number(id), qty: 1 });
  saveCart(cart);
  updateCartCount();
  alert("Đã thêm sản phẩm vào giỏ hàng!");
}
function productCard(p) {
  const detailPage = location.pathname.endsWith(".php")
    ? "product-detail.php"
    : "product-detail.html";

  const detailUrl = `${detailPage}?id=${p.id}`;

  return `
    <article class="product-card rounded-2xl border border-slate-200 bg-white p-4">
      <a href="${detailUrl}">
        <img class="h-48 w-full object-contain" src="${p.image}" alt="${p.name}">
      </a>

      <h3 class="mt-3 font-bold text-slate-900">${p.name}</h3>
      <p class="mt-1 text-sm text-slate-500">${p.spec}</p>
      <p class="my-3 text-xl font-black text-red-700">${money(p.price)}</p>

      <div class="flex gap-2">
        <a href="${detailUrl}"
           class="flex-1 rounded-lg border border-blue-600 px-2 py-2 text-center text-sm font-bold text-blue-700">
          Xem chi tiết
        </a>
        <button onclick="addToCart(${p.id})"
                class="rounded-lg bg-blue-600 px-4 text-white"
                aria-label="Thêm vào giỏ">🛒</button>
      </div>
    </article>
  `;
}function renderProductList(target, items) {
  const el = document.querySelector(target);
  if (el) el.innerHTML = items.map(productCard).join("");
}
document.addEventListener("DOMContentLoaded", () => {
  updateCartCount();
  if (document.querySelector("#featured-products"))
    renderProductList("#featured-products", FOURTECH_PRODUCTS.slice(0, 4));
  if (document.querySelector("#all-products")) {
    const params = new URLSearchParams(location.search);
    // Lấy từ khóa từ địa chỉ trang và hiển thị lại trong ô tìm kiếm
    const keyword = (params.get("q") || "").trim();
    document.querySelector('input[name="q"]').value = keyword;
    const initial = params.get("brand");
    const checks = [...document.querySelectorAll('[name="brand"]')];
    if (initial) checks.forEach((c) => (c.checked = c.value === initial));
    const filter = () => {
      const brands = checks.filter((c) => c.checked).map((c) => c.value);
      const range = document.querySelector("#price-filter")?.value || "all";
      const list = FOURTECH_PRODUCTS.filter(
        (p) =>
          // toLowerCase() giúp khách gõ a55 hay A55 đều tìm được cùng sản phẩm.
          p.name.toLowerCase().includes(keyword.toLowerCase()) &&
          (!brands.length || brands.includes(p.brand)) &&
          (range === "all" ||
            (range === "under5" && p.price < 5e6) ||
            (range === "5to10" && p.price >= 5e6 && p.price <= 1e7) ||
            (range === "over10" && p.price > 1e7)),
      );
      // list là danh sách điện thoại còn lại sau khi lọc theo tên, hãng và giá. list.length === 0 nghĩa là danh sách có 0 sản phẩm.
      if (list.length === 0) {
        document.querySelector("#all-products").textContent =
          "Không tìm thấy điện thoại phù hợp. Bạn hãy thử từ khóa khác hoặc đổi bộ lọc.";
      } else {
        renderProductList("#all-products", list);
      }
    };
    checks.forEach((c) => c.addEventListener("change", filter));
    document.querySelector("#price-filter")?.addEventListener("change", filter);
    filter();
  }
  const detail = document.querySelector("#product-detail");
  if (detail) {
    const id = Number(new URLSearchParams(location.search).get("id") || 1);
    const p =
      FOURTECH_PRODUCTS.find((x) => x.id === id) || FOURTECH_PRODUCTS[0];
    detail.innerHTML = `<div class="rounded-2xl bg-slate-50 p-6"><img src="${p.image}" alt="${p.name}" class="h-96 w-full object-contain"></div><div><p class="font-bold text-blue-700">${p.brand}</p><h1 class="mt-2 text-3xl font-black">${p.name}</h1><p class="mt-3 text-slate-500">${p.spec}</p><p class="my-6 text-3xl font-black text-red-700">${money(p.price)}</p><div class="rounded-xl bg-blue-50 p-4 text-sm leading-7">✓ Hàng chính hãng<br>✓ Hỗ trợ tư vấn trước và sau khi mua<br>✓ Chính sách bảo hành theo cửa hàng</div><button onclick="addToCart(${p.id})" class="mt-6 rounded-xl bg-blue-600 px-7 py-3 font-bold text-white">Thêm vào giỏ hàng</button></div>`;
  }
  if (document.querySelector("#cart-items")) renderCart();
});
function renderCart() {
  const host = document.querySelector("#cart-items");
  if (!host) return;
  const cart = getCart();
  let total = 0;
  if (!cart.length) {
    host.innerHTML =
      '<p class="py-8 text-center text-slate-500">Giỏ hàng của bạn đang trống.</p>';
  } else {
    host.innerHTML = cart
      .map((item) => {
        const p = FOURTECH_PRODUCTS.find((x) => x.id === item.id);
        if (!p) return "";
        total += p.price * item.qty;
        return `<div class="flex flex-wrap items-center gap-4 border-b py-4"><img src="${p.image}" class="h-20 w-20 object-contain" alt=""><div class="min-w-0 flex-1"><b>${p.name}</b><p class="text-sm text-slate-500">${p.spec}</p><b class="text-red-700">${money(p.price)}</b></div><div class="flex items-center gap-2"><button class="rounded border px-3 py-1" onclick="changeQty(${p.id},-1)">−</button>${item.qty}<button class="rounded border px-3 py-1" onclick="changeQty(${p.id},1)">+</button><button class="ml-2 text-red-600" onclick="removeItem(${p.id})">Xóa</button></div></div>`;
      })
      .join("");
  }
  document
    .querySelectorAll("[data-cart-total]")
    .forEach((x) => (x.textContent = money(total)));
  updateCartCount();
}
function changeQty(id, step) {
  const cart = getCart();
  const p = cart.find((x) => x.id === id);
  if (p) p.qty += step;
  saveCart(cart.filter((x) => x.qty > 0));
  renderCart();
}
function removeItem(id) {
  saveCart(getCart().filter((x) => x.id !== id));
  renderCart();
}
