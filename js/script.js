// Dark Mode Toggle Functionality
function toggleTheme() {
  console.log("toggleTheme called"); // Debug log
  const html = document.documentElement;
  const themeToggle = document.querySelector(".theme-toggle");
  const icon = themeToggle?.querySelector(".icon");

  if (!themeToggle || !icon) {
    console.error("Theme toggle elements not found");
    return;
  }

  // Toggle theme
  const currentTheme = html.getAttribute("data-theme");
  const newTheme = currentTheme === "dark" ? "light" : "dark";

  html.setAttribute("data-theme", newTheme);

  // Update icon only
  if (newTheme === "dark") {
    icon.textContent = "☀️";
  } else {
    icon.textContent = "🌙";
  }

  // Save preference to localStorage
  localStorage.setItem("theme", newTheme);
  console.log("Theme changed to:", newTheme); // Debug log
}

// Make toggleTheme globally accessible
window.toggleTheme = toggleTheme;

// Initialize theme on page load
function initializeTheme() {
  const savedTheme = localStorage.getItem("theme") || "light";
  const html = document.documentElement;
  const themeToggle = document.querySelector(".theme-toggle");

  if (themeToggle) {
    const icon = themeToggle.querySelector(".icon");

    html.setAttribute("data-theme", savedTheme);

    if (savedTheme === "dark") {
      icon.textContent = "☀️";
    } else {
      icon.textContent = "🌙";
    }
  }
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
  const links = document.querySelectorAll('a[href^="#"]');

  links.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      const targetId = this.getAttribute("href");
      const targetSection = document.querySelector(targetId);

      if (targetSection) {
        targetSection.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });
}

// Add fade-in animation on scroll
function initializeScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in-up");
      }
    });
  }, observerOptions);

  // Observe all sections and food menu boxes
  const elementsToAnimate = document.querySelectorAll(
    ".categories, .food-menu-box, .box-3"
  );
  elementsToAnimate.forEach((el) => observer.observe(el));
}

// Enhanced search functionality
function initializeSearchEnhancements() {
  const searchInput = document.querySelector('input[type="search"]');

  if (searchInput) {
    // Add search suggestions (placeholder for future enhancement)
    searchInput.addEventListener("input", function (e) {
      const query = e.target.value.toLowerCase();
      // This can be enhanced with actual search functionality
      console.log("Searching for:", query);
    });

    // Add search on Enter key
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        const query = e.target.value;
        if (query.trim()) {
          // For now, just show an alert. Can be enhanced with actual search
          alert(`Searching for: ${query}`);
        }
      }
    });
  }
}

// Shopping cart functionality (basic implementation)
let cart = JSON.parse(localStorage.getItem("cart")) || [];

function addToCart(foodName, price, imageSrc) {
  const item = {
    id: Date.now(),
    name: foodName,
    price: price,
    image: imageSrc,
    quantity: 1,
  };

  // Check if item already exists
  const existingItem = cart.find((cartItem) => cartItem.name === foodName);

  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push(item);
  }

  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartDisplay();
  showNotification(`${foodName} ${getCurrentTranslation("addedToOrder")}`);
}

function updateCartDisplay() {
  const cartCount = cart.reduce((total, item) => total + item.quantity, 0);

  // Update cart counter if it exists
  const cartCounter = document.querySelector(".cart-counter");
  if (cartCounter) {
    cartCounter.textContent = cartCount;
  }
}

function showNotification(message) {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = "notification";
  notification.innerHTML = `
    <div>${message}</div>
    <small style="display: block; margin-top: 5px; opacity: 0.8;">
      <a href="cart.html" style="color: white; text-decoration: underline;" data-translate="viewMyOrders">View My Orders</a>
    </small>
  `;
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--accent-color);
        color: white;
        padding: 15px 20px;
        border-radius: var(--border-radius-sm);
        box-shadow: var(--shadow-medium);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

  document.body.appendChild(notification);

  // Animate in
  setTimeout(() => {
    notification.style.transform = "translateX(0)";
  }, 100);

  // Remove after 3 seconds
  setTimeout(() => {
    notification.style.transform = "translateX(100%)";
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Initialize all functionality when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  initializeTheme();
  initializeSmoothScrolling();
  initializeScrollAnimations();
  initializeSearchEnhancements();
  updateCartDisplay();

  // Add click handlers to order buttons
  const orderButtons = document.querySelectorAll(".btn-primary");
  orderButtons.forEach((button) => {
    if (button.textContent.includes("Order Now")) {
      button.addEventListener("click", function (e) {
        e.preventDefault();

        // Get food details from the parent container
        const foodBox = this.closest(".food-menu-box");
        if (foodBox) {
          const foodName = foodBox.querySelector("h4").textContent;
          const priceText = foodBox.querySelector(".food-price").textContent;
          const price = parseFloat(priceText.replace("$", ""));
          const imageSrc = foodBox.querySelector("img").src;

          addToCart(foodName, price, imageSrc);
        } else {
          // If not in a food box, just show the order page
          window.location.href = "order.html";
        }
      });
    }
  });
});

// Add CSS for notifications if not already present
if (!document.querySelector("#notification-styles")) {
  const style = document.createElement("style");
  style.id = "notification-styles";
  style.textContent = `
        .notification {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
        }
    `;
  document.head.appendChild(style);
}

// Smooth Scrolling for Anchor Links
function initializeSmoothScrolling() {
  // Add smooth scrolling behavior to all anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();

      const targetId = this.getAttribute("href").substring(1);
      const targetElement = document.getElementById(targetId);

      if (targetElement) {
        // Calculate offset for fixed navbar
        const navbarHeight =
          document.querySelector(".navbar")?.offsetHeight || 0;
        const targetPosition = targetElement.offsetTop - navbarHeight - 20; // 20px extra padding

        window.scrollTo({
          top: targetPosition,
          behavior: "smooth",
        });

        // Optional: Add a subtle highlight effect to the target section
        targetElement.style.transition = "background-color 0.3s ease";
        const originalBg = getComputedStyle(targetElement).backgroundColor;
        targetElement.style.backgroundColor = "rgba(231, 76, 60, 0.1)";

        setTimeout(() => {
          targetElement.style.backgroundColor = originalBg;
        }, 1000);
      }
    });
  });
}

// Initialize smooth scrolling when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  initializeSmoothScrolling();
  loadSavedLanguage();
});

// Language functionality with full translation
const translations = {
  en: {
    // Navigation
    home: "Home",
    categories: "Categories",
    food: "Food",
    myOrders: "My Orders",
    contact: "Contact",

    // Common
    search: "Search for Food..",
    orderNow: "Order Now",
    viewAll: "View All",
    addToOrder: "Add to Order",

    // Food Categories
    pizza: "Pizza",
    burger: "Burger",
    momo: "Momo",

    // Home page
    homeTitle: "Best Food In The City",
    homeSubtitle:
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi, asperiores!",

    // Categories
    categoriesTitle: "Explore Foods",

    // Food page
    foodTitle: "Food Menu",

    // Cart/Orders
    myOrdersTitle: "My Orders",
    orderSummary: "Order Summary",
    subtotal: "Subtotal:",
    deliveryFee: "Delivery Fee:",
    tax: "Tax:",
    total: "Total:",
    clearOrders: "Clear Orders",
    proceedToCheckout: "Proceed to Checkout",
    emptyOrderTitle: "Your order is empty",
    emptyOrderText:
      "Add some delicious food items to your order to get started!",
    browseFood: "Browse Foods",

    // Checkout
    checkoutTitle: "Secure Checkout",
    yourOrder: "Your Order",
    paymentInfo: "Payment Information",
    cardNumber: "Card Number",
    expiryDate: "MM/YY",
    cvv: "CVV",
    billingInfo: "Billing Information",
    fullName: "Full Name",
    email: "Email Address",
    phone: "Phone Number",
    address: "Address",
    city: "City",
    zipCode: "ZIP Code",
    placeOrder: "Place Order",

    // Footer
    followUs: "Follow Us",

    // Messages
    languageChanged: "Language changed to English",
    addedToOrder: "added to order!",
    viewMyOrders: "View My Orders",
    removedFromOrder: "removed from order!",
    ordersCleared: "Orders cleared!",
    ordersEmpty: "Your orders are empty!",
    orderEmptyRedirect: "Your orders are empty! Redirecting to order page...",
  },

  fr: {
    // Navigation
    home: "Accueil",
    categories: "Catégories",
    food: "Nourriture",
    myOrders: "Mes Commandes",
    contact: "Contact",

    // Common
    search: "Rechercher de la nourriture..",
    orderNow: "Commander Maintenant",
    viewAll: "Voir Tout",
    addToOrder: "Ajouter à la Commande",

    // Food Categories
    pizza: "Pizza",
    burger: "Burger",
    momo: "Momo",

    // Home page
    homeTitle: "Meilleure Nourriture de la Ville",
    homeSubtitle:
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi, asperiores!",

    // Categories
    categoriesTitle: "Explorer la Nourriture",

    // Food page
    foodTitle: "Menu de Nourriture",

    // Cart/Orders
    myOrdersTitle: "Mes Commandes",
    orderSummary: "Résumé de la Commande",
    subtotal: "Sous-total:",
    deliveryFee: "Frais de Livraison:",
    tax: "Taxe:",
    total: "Total:",
    clearOrders: "Vider les Commandes",
    proceedToCheckout: "Procéder au Checkout",
    emptyOrderTitle: "Vos commandes sont vides",
    emptyOrderText:
      "Ajoutez de délicieux plats à votre commande pour commencer!",
    browseFood: "Parcourir les Aliments",

    // Checkout
    checkoutTitle: "Checkout Sécurisé",
    yourOrder: "Votre Commande",
    paymentInfo: "Informations de Paiement",
    cardNumber: "Numéro de Carte",
    expiryDate: "MM/AA",
    cvv: "CVV",
    billingInfo: "Informations de Facturation",
    fullName: "Nom Complet",
    email: "Adresse Email",
    phone: "Numéro de Téléphone",
    address: "Adresse",
    city: "Ville",
    zipCode: "Code Postal",
    placeOrder: "Passer la Commande",

    // Footer
    followUs: "Suivez-Nous",

    // Messages
    languageChanged: "Langue changée en français",
    addedToOrder: "ajouté à la commande!",
    viewMyOrders: "Voir Mes Commandes",
    removedFromOrder: "retiré de la commande!",
    ordersCleared: "Commandes vidées!",
    ordersEmpty: "Vos commandes sont vides!",
    orderEmptyRedirect:
      "Vos commandes sont vides! Redirection vers la page de commande...",
  },

  ar: {
    // Navigation
    home: "الرئيسية",
    categories: "الفئات",
    food: "الطعام",
    myOrders: "طلباتي",
    contact: "اتصل بنا",

    // Common
    search: "البحث عن الطعام..",
    orderNow: "اطلب الآن",
    viewAll: "عرض الكل",
    addToOrder: "أضف للطلب",

    // Food Categories
    pizza: "بيتزا",
    burger: "برغر",
    momo: "مومو",

    // Home page
    homeTitle: "أفضل طعام في المدينة",
    homeSubtitle:
      "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi, asperiores!",

    // Categories
    categoriesTitle: "استكشف الطعام",

    // Food page
    foodTitle: "قائمة الطعام",

    // Cart/Orders
    myOrdersTitle: "طلباتي",
    orderSummary: "ملخص الطلب",
    subtotal: "المجموع الفرعي:",
    deliveryFee: "رسوم التوصيل:",
    tax: "الضريبة:",
    total: "الإجمالي:",
    clearOrders: "مسح الطلبات",
    proceedToCheckout: "المتابعة للدفع",
    emptyOrderTitle: "طلبك فارغ",
    emptyOrderText: "أضف بعض العناصر الغذائية اللذيذة إلى طلبك للبدء!",
    browseFood: "تصفح الأطعمة",

    // Checkout
    checkoutTitle: "الدفع الآمن",
    yourOrder: "طلبك",
    paymentInfo: "معلومات الدفع",
    cardNumber: "رقم البطاقة",
    expiryDate: "ش ش/س س",
    cvv: "CVV",
    billingInfo: "معلومات الفوترة",
    fullName: "الاسم الكامل",
    email: "البريد الإلكتروني",
    phone: "رقم الهاتف",
    address: "العنوان",
    city: "المدينة",
    zipCode: "الرمز البريدي",
    placeOrder: "تأكيد الطلب",

    // Footer
    followUs: "تابعونا",

    // Messages
    languageChanged: "تم تغيير اللغة إلى العربية",
    addedToOrder: "تمت الإضافة للطلب!",
    viewMyOrders: "عرض طلباتي",
    removedFromOrder: "تم الحذف من الطلب!",
    ordersCleared: "تم مسح الطلبات!",
    ordersEmpty: "طلباتك فارغة!",
    orderEmptyRedirect: "طلباتك فارغة! جاري التوجيه لصفحة الطلب...",
  },
};

function changeLanguage(language) {
  localStorage.setItem("selectedLanguage", language);

  // Show notification in selected language
  showNotification(translations[language].languageChanged);

  // Update all language selectors across the page
  updateLanguageSelectors(language);

  // Translate the page content
  translatePage(language);
}

function loadSavedLanguage() {
  const savedLanguage = localStorage.getItem("selectedLanguage") || "en";
  updateLanguageSelectors(savedLanguage);
  translatePage(savedLanguage);
}

function updateLanguageSelectors(language) {
  const selectors = document.querySelectorAll(".language-selector");
  selectors.forEach((selector) => {
    selector.value = language;
  });
}

function translatePage(language) {
  const elements = document.querySelectorAll("[data-translate]");

  elements.forEach((element) => {
    const key = element.getAttribute("data-translate");
    if (translations[language] && translations[language][key]) {
      element.textContent = translations[language][key];
    }
  });

  // Handle placeholders
  const inputElements = document.querySelectorAll(
    "[data-translate-placeholder]"
  );
  inputElements.forEach((element) => {
    const key = element.getAttribute("data-translate-placeholder");
    if (translations[language] && translations[language][key]) {
      element.placeholder = translations[language][key];
    }
  });

  // Set text direction for Arabic
  if (language === "ar") {
    document.documentElement.setAttribute("dir", "rtl");
    document.documentElement.setAttribute("lang", "ar");
  } else {
    document.documentElement.setAttribute("dir", "ltr");
    document.documentElement.setAttribute("lang", language);
  }
}

// Helper function to get current translation
function getCurrentTranslation(key) {
  const currentLanguage = localStorage.getItem("selectedLanguage") || "en";
  return translations[currentLanguage][key] || translations.en[key] || key;
}

// Mobile Menu Toggle Functionality
function toggleMobileMenu() {
  const menu = document.getElementById("navbar-menu");
  const controls = document.getElementById("navbar-controls");
  const toggleButton = document.querySelector(".mobile-menu-toggle");

  if (menu && controls && toggleButton) {
    // Toggle menu visibility
    menu.classList.toggle("active");
    controls.classList.toggle("active");
    toggleButton.classList.toggle("active");

    // Prevent body scroll when menu is open on mobile
    if (menu.classList.contains("active")) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "";
    }
  }
}

// Close mobile menu when clicking on menu links
function closeMobileMenu() {
  const menu = document.getElementById("navbar-menu");
  const controls = document.getElementById("navbar-controls");
  const toggleButton = document.querySelector(".mobile-menu-toggle");

  if (menu && controls && toggleButton) {
    menu.classList.remove("active");
    controls.classList.remove("active");
    toggleButton.classList.remove("active");
    document.body.style.overflow = "";
  }
}

// Close mobile menu when clicking outside
document.addEventListener("click", function (event) {
  const navbar = document.querySelector(".navbar");
  const menu = document.getElementById("navbar-menu");
  const toggleButton = document.querySelector(".mobile-menu-toggle");

  if (
    menu &&
    menu.classList.contains("active") &&
    !navbar.contains(event.target)
  ) {
    closeMobileMenu();
  }
});

// Close mobile menu when clicking on menu links
document.addEventListener("DOMContentLoaded", function () {
  const menuLinks = document.querySelectorAll("#navbar-menu a");
  menuLinks.forEach(function (link) {
    link.addEventListener("click", function () {
      closeMobileMenu();
    });
  });
});

// Close mobile menu on window resize if screen becomes larger
window.addEventListener("resize", function () {
  if (window.innerWidth > 768) {
    closeMobileMenu();
  }
});

// Make functions globally accessible
window.toggleMobileMenu = toggleMobileMenu;
window.closeMobileMenu = closeMobileMenu;
