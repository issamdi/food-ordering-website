// Secure Checkout with Stripe Integration
// IMPORTANT: Replace this with YOUR actual Stripe publishable key to receive payments
// Get your key from: https://dashboard.stripe.com/apikeys
const STRIPE_PUBLISHABLE_KEY = "pk_test_TGlpdmVfOW03WjQ3OG1"; // Temporary test key

// Initialize Stripe with error handling
let stripe, elements, cardElement;

function initializeStripeComponents() {
  try {
    console.log("Initializing Stripe...");
    stripe = Stripe(STRIPE_PUBLISHABLE_KEY);
    elements = stripe.elements();

    // Create card element
    cardElement = elements.create("card", {
      style: {
        base: {
          fontSize: "16px",
          color: "#424770",
          "::placeholder": {
            color: "#aab7c4",
          },
        },
        invalid: {
          color: "#9e2146",
        },
      },
    });

    // Mount card element
    const cardContainer = document.getElementById("card-element");
    if (cardContainer) {
      cardElement.mount("#card-element");
      console.log("Stripe card element mounted");
    }

    // Handle errors
    cardElement.on("change", ({ error }) => {
      const displayError = document.getElementById("card-errors");
      if (error) {
        displayError.textContent = error.message;
      } else {
        displayError.textContent = "";
      }
    });

    return true;
  } catch (error) {
    console.error("Stripe initialization failed:", error);
    const cardContainer = document.getElementById("card-element");
    if (cardContainer) {
      cardContainer.innerHTML =
        '<p style="color: red;">Payment system temporarily unavailable. Please try again later.</p>';
    }
    return false;
  }
}

// Load checkout data when page loads
document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded, initializing checkout...");

  if (window.location.pathname.includes("checkout.html")) {
    // Test if basic DOM elements are accessible
    console.log("Testing DOM elements...");
    const testButton = document.querySelector("button");
    const testInput = document.querySelector("input");
    const testForm = document.getElementById("payment-form");

    console.log("Button found:", !!testButton);
    console.log("Input found:", !!testInput);
    console.log("Form found:", !!testForm);

    // Add click test to all buttons
    const buttons = document.querySelectorAll("button, .btn");
    buttons.forEach((btn, index) => {
      btn.addEventListener("click", function (e) {
        console.log(`Button ${index} clicked:`, btn);
      });
    });

    // Initialize Stripe first
    const stripeInitialized = initializeStripeComponents();

    // Load checkout data
    loadCheckoutData();
    setupFormValidation();

    if (stripeInitialized) {
      setupFormSubmission();
    }

    // Initialize cart counter
    if (typeof updateCartDisplay === "function") {
      updateCartDisplay();
    }
  }
});

function loadCheckoutData() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  if (cart.length === 0) {
    showNotification(getCurrentTranslation("orderEmptyRedirect"));
    setTimeout(() => {
      window.location.href = "cart.html";
    }, 2000);
    return;
  }

  displayCheckoutItems(cart);
  calculateCheckoutTotals(cart);
}

function displayCheckoutItems(cart) {
  const checkoutItems = document.getElementById("checkout-items");
  checkoutItems.innerHTML = "";

  cart.forEach((item) => {
    const itemElement = document.createElement("div");
    itemElement.className = "checkout-item";
    itemElement.innerHTML = `
      <div class="checkout-item-info">
        <span class="item-name">${item.name}</span>
        <span class="item-quantity">× ${item.quantity}</span>
      </div>
      <span class="item-price">$${(item.price * item.quantity).toFixed(
        2
      )}</span>
    `;
    checkoutItems.appendChild(itemElement);
  });
}

function calculateCheckoutTotals(cart) {
  const subtotal = cart.reduce(
    (total, item) => total + item.price * item.quantity,
    0
  );
  const deliveryFee = 3.99;
  const taxRate = 0.08;
  const tax = subtotal * taxRate;
  const total = subtotal + deliveryFee + tax;

  document.getElementById(
    "checkout-subtotal"
  ).textContent = `$${subtotal.toFixed(2)}`;
  document.getElementById(
    "checkout-delivery"
  ).textContent = `$${deliveryFee.toFixed(2)}`;
  document.getElementById("checkout-tax").textContent = `$${tax.toFixed(2)}`;
  document.getElementById("checkout-total").textContent = `$${total.toFixed(
    2
  )}`;

  return { subtotal, deliveryFee, tax, total };
}

function setupFormValidation() {
  const form = document.getElementById("payment-form");

  // Real-time validation
  const inputs = form.querySelectorAll("input[required], textarea[required]");
  inputs.forEach((input) => {
    input.addEventListener("blur", validateField);
    input.addEventListener("input", clearFieldError);
  });
}

function validateField(event) {
  const field = event.target;
  const value = field.value.trim();

  // Remove existing error styling
  field.classList.remove("error");

  if (!value) {
    showFieldError(field, "This field is required");
    return false;
  }

  // Specific validations
  if (field.type === "email" && !isValidEmail(value)) {
    showFieldError(field, "Please enter a valid email address");
    return false;
  }

  if (field.type === "tel" && !isValidPhone(value)) {
    showFieldError(field, "Please enter a valid phone number");
    return false;
  }

  return true;
}

function showFieldError(field, message) {
  field.classList.add("error");

  // Remove existing error message
  const existingError = field.parentNode.querySelector(".field-error");
  if (existingError) {
    existingError.remove();
  }

  // Add new error message
  const errorDiv = document.createElement("div");
  errorDiv.className = "field-error";
  errorDiv.textContent = message;
  field.parentNode.appendChild(errorDiv);
}

function clearFieldError(event) {
  const field = event.target;
  field.classList.remove("error");

  const errorMessage = field.parentNode.querySelector(".field-error");
  if (errorMessage) {
    errorMessage.remove();
  }
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function isValidPhone(phone) {
  const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
  const cleanPhone = phone.replace(/[\s\-\(\)]/g, "");
  return cleanPhone.length >= 10 && phoneRegex.test(cleanPhone);
}

// Handle form submission
function setupFormSubmission() {
  const paymentForm = document.getElementById("payment-form");
  if (paymentForm) {
    paymentForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      if (!validateForm()) {
        return;
      }

      if (!stripe || !cardElement) {
        showCardError(
          "Payment system not initialized. Please refresh the page."
        );
        return;
      }

      setLoading(true);

      try {
        // Create payment method
        const { token, error } = await stripe.createToken(cardElement);

        if (error) {
          showCardError(error.message);
          setLoading(false);
          return;
        }

        // Process payment
        await processPayment(token);
      } catch (error) {
        console.error("Payment error:", error);
        showCardError("Payment failed. Please try again.");
        setLoading(false);
      }
    });
  }
}

function validateForm() {
  const form = document.getElementById("payment-form");
  const requiredFields = form.querySelectorAll(
    "input[required], textarea[required]"
  );
  let isValid = true;

  requiredFields.forEach((field) => {
    if (!validateField({ target: field })) {
      isValid = false;
    }
  });

  return isValid;
}

async function processPayment(token) {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const totals = calculateCheckoutTotals(cart);

  const customerData = {
    name: document.getElementById("customer-name").value,
    email: document.getElementById("customer-email").value,
    phone: document.getElementById("customer-phone").value,
    address: document.getElementById("customer-address").value,
  };

  try {
    // Process payment through secure backend
    const response = await fetch("api/process-payment.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({
        token: token.id,
        amount: totals.total,
        customer: customerData,
        items: cart,
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    if (result.success) {
      const orderData = {
        orderId: result.order_id,
        paymentIntentId: result.payment_intent_id,
        customerId: result.customer_id,
        items: cart,
        customer: customerData,
        totals: totals,
        timestamp: new Date().toISOString(),
      };

      processPaymentSuccess(orderData);
    } else {
      throw new Error(result.message || "Payment failed");
    }
  } catch (error) {
    console.error("Payment processing error:", error);
    showCardError(
      error.message || "Payment processing failed. Please try again."
    );
    setLoading(false);
  }
}

function processPaymentSuccess(orderData) {
  // Clear the cart
  localStorage.removeItem("cart");
  updateCartDisplay();

  // Store order for reference
  const orders = JSON.parse(localStorage.getItem("orders")) || [];
  orders.push(orderData);
  localStorage.setItem("orders", JSON.stringify(orders));

  // Show success modal
  document.getElementById("order-id").textContent = orderData.orderId;
  showSuccessModal();

  setLoading(false);
}

function generateOrderId() {
  const timestamp = Date.now();
  const random = Math.floor(Math.random() * 1000);
  return `ORD-${timestamp}-${random}`;
}

function showCardError(message) {
  const errorElement = document.getElementById("card-errors");
  errorElement.textContent = message;
  errorElement.style.display = "block";
}

function setLoading(isLoading) {
  const button = document.getElementById("submit-payment");
  const buttonText = document.getElementById("button-text");
  const spinner = document.getElementById("spinner");

  if (isLoading) {
    button.disabled = true;
    buttonText.classList.add("hidden");
    spinner.classList.remove("hidden");
  } else {
    button.disabled = false;
    buttonText.classList.remove("hidden");
    spinner.classList.add("hidden");
  }
}

function showSuccessModal() {
  const modal = document.getElementById("payment-success-modal");
  modal.classList.remove("hidden");

  // Add animation
  setTimeout(() => {
    modal.classList.add("show");
  }, 100);
}

function goToHome() {
  window.location.href = "index.html";
}

function trackOrder() {
  // For demo purposes, just show a message
  showNotification("Order tracking feature coming soon!");
  setTimeout(() => {
    goToHome();
  }, 2000);
}

// Security enhancements
function sanitizeInput(input) {
  const div = document.createElement("div");
  div.textContent = input;
  return div.innerHTML;
}

// Prevent form resubmission
window.addEventListener("beforeunload", function () {
  const form = document.getElementById("payment-form");
  if (form) {
    form.reset();
  }
});

// Handle page visibility change (security)
document.addEventListener("visibilitychange", function () {
  if (document.hidden) {
    // Clear sensitive data when page is hidden
    const cardErrors = document.getElementById("card-errors");
    if (cardErrors) {
      cardErrors.textContent = "";
      cardErrors.style.display = "none";
    }
  }
});
