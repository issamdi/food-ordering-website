// Cart-specific functionality
function loadCartItems() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const cartItemsContainer = document.getElementById("cart-items");
  const emptyCartDiv = document.getElementById("empty-cart");
  const cartContainer = document.querySelector(".cart-container");

  if (cart.length === 0) {
    cartContainer.style.display = "none";
    emptyCartDiv.style.display = "block";
    return;
  }

  cartContainer.style.display = "block";
  emptyCartDiv.style.display = "none";

  cartItemsContainer.innerHTML = "";

  cart.forEach((item, index) => {
    const cartItem = document.createElement("div");
    cartItem.className = "cart-item";
    cartItem.innerHTML = `
      <div class="cart-item-image">
        <img src="${item.image}" alt="${
      item.name
    }" class="img-responsive img-curve">
      </div>
      <div class="cart-item-details">
        <h4>${item.name}</h4>
        <p class="item-price">$${item.price.toFixed(2)}</p>
      </div>
      <div class="cart-item-quantity">
        <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
        <span class="quantity">${item.quantity}</span>
        <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
      </div>
      <div class="cart-item-total">
        <span class="item-total">$${(item.price * item.quantity).toFixed(
          2
        )}</span>
      </div>
      <div class="cart-item-remove">
        <button class="remove-btn" onclick="removeFromCart(${index})" title="Remove item">
          <span>×</span>
        </button>
      </div>
    `;
    cartItemsContainer.appendChild(cartItem);
  });

  updateCartSummary();
}

function updateQuantity(index, change) {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  if (cart[index]) {
    cart[index].quantity += change;

    if (cart[index].quantity <= 0) {
      cart.splice(index, 1);
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    loadCartItems();
    updateCartDisplay(); // Update the navbar counter

    if (change > 0) {
      showNotification(`Quantity updated!`);
    } else {
      showNotification(`Item ${cart[index] ? "updated" : "removed"}!`);
    }
  }
}

function removeFromCart(index) {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  if (cart[index]) {
    const itemName = cart[index].name;
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    loadCartItems();
    updateCartDisplay(); // Update the navbar counter
    showNotification(
      `${itemName} ${getCurrentTranslation("removedFromOrder")}`
    );
  }
}

function updateCartSummary() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  const subtotal = cart.reduce(
    (total, item) => total + item.price * item.quantity,
    0
  );
  const deliveryFee = subtotal > 0 ? 3.99 : 0;
  const taxRate = 0.08;
  const tax = subtotal * taxRate;
  const total = subtotal + deliveryFee + tax;

  document.getElementById("cart-subtotal").textContent = `$${subtotal.toFixed(
    2
  )}`;
  document.getElementById("delivery-fee").textContent =
    subtotal > 0 ? `$${deliveryFee.toFixed(2)}` : "$0.00";
  document.getElementById("cart-tax").textContent = `$${tax.toFixed(2)}`;
  document.getElementById("cart-total").textContent = `$${total.toFixed(2)}`;
}

function clearCart() {
  if (confirm(getCurrentTranslation("clearOrders") + "?")) {
    localStorage.removeItem("cart");
    loadCartItems();
    updateCartDisplay(); // Update the navbar counter
    showNotification(getCurrentTranslation("ordersCleared"));
  }
}

function proceedToCheckout() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  if (cart.length === 0) {
    showNotification(getCurrentTranslation("ordersEmpty"));
    return;
  }

  // Redirect to secure checkout page
  showNotification("Redirecting to secure checkout...");
  setTimeout(() => {
    window.location.href = "checkout.html";
  }, 1000);
}

// Load cart items when the page loads (only on cart page)
document.addEventListener("DOMContentLoaded", function () {
  if (window.location.pathname.includes("cart.html")) {
    loadCartItems();
  }
});

// Quick add to cart function for use on other pages
function quickAddToCart(button) {
  const foodBox = button.closest(".food-menu-box");
  if (foodBox) {
    const foodName = foodBox.querySelector("h4").textContent;
    const priceText = foodBox.querySelector(".food-price").textContent;
    const price = parseFloat(priceText.replace("$", ""));
    const imageSrc = foodBox.querySelector("img").src;

    addToCart(foodName, price, imageSrc);
  }
}
