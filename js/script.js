// Dark Mode Toggle Functionality
function toggleTheme() {
  const html = document.documentElement;
  const themeToggle = document.querySelector(".theme-toggle");
  const icon = themeToggle?.querySelector(".icon");

  if (!themeToggle || !icon) {
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
      const targetId = this.getAttribute("href");
      if (!targetId || targetId === "#") return;

      e.preventDefault();
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

// Scroll-reveal animations via IntersectionObserver
function initializeScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
      }
    });
  }, observerOptions);

  // Observe sections, cards, and glass elements
  const elementsToAnimate = document.querySelectorAll(
    ".section, .food-card, .category-card, .glass-card, .cart-section, .checkout-section, .order-section",
  );
  elementsToAnimate.forEach((el) => observer.observe(el));
}

// Food catalog — all available food items
const foodCatalog = [
  {
    name: "Margherita Pizza",
    price: 11.75,
    category: "pizza",
    image: "images/pizza.jpg",
    detail:
      "Classic Italian pizza with fresh mozzarella, basil, and rich tomato sauce on a crispy thin crust.",
    name_fr: "Pizza Margherita",
    detail_fr:
      "Pizza italienne classique avec mozzarella fraîche, basilic et sauce tomate sur une croûte fine et croustillante.",
    name_ar: "بيتزا مارغريتا",
    detail_ar:
      "بيتزا إيطالية كلاسيكية بجبنة الموزاريلا الطازجة والريحان وصلصة الطماطم على عجينة رقيقة ومقرمشة.",
  },
  {
    name: "Smoky BBQ Burger",
    price: 9.5,
    category: "burger",
    image: "images/burger.jpg",
    detail:
      "Juicy beef patty with smoky BBQ sauce, caramelized onions, cheddar cheese, and crisp lettuce.",
    name_fr: "Burger BBQ Fumé",
    detail_fr:
      "Steak de bœuf juteux avec sauce BBQ fumée, oignons caramélisés, cheddar et laitue croquante.",
    name_ar: "برجر باربكيو مدخّن",
    detail_ar:
      "قطعة لحم بقري طرية مع صلصة الباربكيو المدخّنة والبصل المكرمل وجبنة الشيدر والخس المقرمش.",
  },
  {
    name: "Chicken Steam Momo",
    price: 7.25,
    category: "momo",
    image: "images/momo.jpg",
    detail:
      "Traditional steamed dumplings filled with seasoned chicken, ginger, garlic, and fresh herbs.",
    name_fr: "Momo au Poulet Vapeur",
    detail_fr:
      "Raviolis vapeur traditionnels farcis au poulet assaisonné, gingembre, ail et herbes fraîches.",
    name_ar: "مومو دجاج على البخار",
    detail_ar:
      "فطائر مبخّرة تقليدية محشوة بالدجاج المتبّل والزنجبيل والثوم والأعشاب الطازجة.",
  },
  {
    name: "Grilled Chicken Plate",
    price: 13.99,
    category: "grilled-chicken",
    image:
      "images/crilled-chicken-bread-piece-greens-pepper-sauce-spices-side-view.jpg",
    detail:
      "Tender grilled chicken breast served with warm bread, fresh greens, peppers, and a zesty spice sauce.",
    name_fr: "Assiette de Poulet Grillé",
    detail_fr:
      "Blanc de poulet grillé tendre servi avec du pain chaud, des légumes verts, des poivrons et une sauce épicée.",
    name_ar: "طبق دجاج مشوي",
    detail_ar:
      "صدر دجاج مشوي طري يُقدّم مع خبز دافئ وخضار طازجة وفلفل وصلصة بهارات شهية.",
  },
  {
    name: "Lavash Wraps",
    price: 8.99,
    category: "wraps",
    image: "images/lavash-rolls-top-view-table.jpg",
    detail:
      "Soft lavash bread rolled with seasoned meat, fresh vegetables, herbs, and a creamy garlic sauce.",
    name_fr: "Wraps Lavash",
    detail_fr:
      "Pain lavash moelleux roulé avec de la viande assaisonnée, des légumes frais, des herbes et une sauce à l'ail crémeuse.",
    name_ar: "لفائف لافاش",
    detail_ar:
      "خبز لافاش طري ملفوف بلحم متبّل وخضار طازجة وأعشاب وصلصة ثوم كريمية.",
  },
  {
    name: "Colorful Power Bowl",
    price: 10.5,
    category: "bowls",
    image:
      "images/colorful-bowl-of-deliciousness-with-fried-egg-RaPUELLHgqFnS9zI85BpU.webp",
    detail:
      "A vibrant bowl loaded with fresh veggies, grains, a perfectly fried egg, and a tangy dressing.",
    name_fr: "Bowl Coloré Énergétique",
    detail_fr:
      "Un bol vibrant garni de légumes frais, de céréales, d'un œuf parfaitement frit et d'une vinaigrette acidulée.",
    name_ar: "وعاء الطاقة الملوّن",
    detail_ar:
      "وعاء نابض بالحياة مليء بالخضار الطازجة والحبوب وبيضة مقلية بإتقان وصلصة منعشة.",
  },
  {
    name: "Beef & Potato Patties",
    price: 10.25,
    category: "patties",
    image: "images/potato-patties-topped-with-beef-patties-shoe-strings.jpg",
    detail:
      "Golden potato patties topped with seasoned beef, crispy shoestring fries, and a savory drizzle.",
    name_fr: "Galettes Bœuf & Pommes de Terre",
    detail_fr:
      "Galettes de pommes de terre dorées garnies de bœuf assaisonné, de frites croustillantes et d'un filet de sauce savoureuse.",
    name_ar: "كفتة لحم وبطاطس",
    detail_ar:
      "أقراص بطاطس ذهبية مغطاة بلحم البقر المتبّل وبطاطس مقرمشة رفيعة وصلصة لذيذة.",
  },
];

// Search functionality
function initializeSearchEnhancements() {
  const searchInput = document.querySelector('input[type="search"]');

  if (searchInput) {
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        const form = searchInput.closest("form");
        if (form) return; // let the form submit naturally
        e.preventDefault();
        const query = searchInput.value.trim();
        if (query) {
          window.location.href =
            "food-search.html?search=" + encodeURIComponent(query);
        }
      }
    });
  }

  // If we're on the search results page, run the search
  if (document.getElementById("search-results")) {
    runFoodSearch();
  }
}

// Get translated food name and detail based on current language
function getTranslatedFood(food) {
  var lang = localStorage.getItem("selectedLanguage") || "en";
  var name = food.name;
  var detail = food.detail;
  if (lang === "fr" && food.name_fr) {
    name = food.name_fr;
    detail = food.detail_fr || detail;
  }
  if (lang === "ar" && food.name_ar) {
    name = food.name_ar;
    detail = food.detail_ar || detail;
  }
  return { name: name, detail: detail };
}

function runFoodSearch() {
  const params = new URLSearchParams(window.location.search);
  const query = params.get("search") || "";
  const resultsContainer = document.getElementById("search-results");
  const noResults = document.getElementById("no-results");
  const queryDisplay = document.getElementById("search-query-display");
  const searchInput = document.getElementById("search-input");

  // Show the query in the header and input
  if (queryDisplay) {
    queryDisplay.textContent = '"' + query + '"';
  }
  if (searchInput) {
    searchInput.value = query;
  }

  if (!query.trim()) {
    resultsContainer.innerHTML = "";
    if (noResults) noResults.style.display = "block";
    return;
  }

  const searchTerm = query.toLowerCase();
  const matches = foodCatalog.filter(function (food) {
    var t = getTranslatedFood(food);
    var categoryName = food.category.replace(/-/g, " ");
    return (
      food.name.toLowerCase().includes(searchTerm) ||
      food.detail.toLowerCase().includes(searchTerm) ||
      categoryName.includes(searchTerm) ||
      t.name.toLowerCase().includes(searchTerm) ||
      t.detail.toLowerCase().includes(searchTerm)
    );
  });

  if (matches.length === 0) {
    resultsContainer.innerHTML = "";
    if (noResults) noResults.style.display = "block";
    return;
  }

  if (noResults) noResults.style.display = "none";

  resultsContainer.innerHTML = matches
    .map(function (food) {
      var t = getTranslatedFood(food);
      var safeName = t.name
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
      var safeDetail = t.detail
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
      return (
        '<div class="food-card food-menu-box">' +
        '<div class="food-card-img-wrapper">' +
        '<img src="' +
        food.image +
        '" alt="' +
        safeName +
        '" class="food-card-img" />' +
        "</div>" +
        '<div class="food-card-body">' +
        "<h4>" +
        safeName +
        "</h4>" +
        '<p class="food-price">$' +
        food.price.toFixed(2) +
        "</p>" +
        '<p class="food-detail">' +
        safeDetail +
        "</p>" +
        '<div class="food-card-actions">' +
        '<a href="#" class="btn btn-primary" data-translate="orderNow">Order Now</a>' +
        "</div>" +
        "</div>" +
        "</div>"
      );
    })
    .join("");

  // Make dynamically added cards and their parent section visible
  resultsContainer.closest(".section")?.classList.add("visible");
  resultsContainer.querySelectorAll(".food-card").forEach(function (card) {
    card.classList.add("visible");
  });
}

// Category page filtering
function initializeCategoryPage() {
  var container = document.getElementById("category-results");
  if (!container) return;

  var params = new URLSearchParams(window.location.search);
  var category = params.get("category") || "";
  var nameDisplay = document.getElementById("category-name-display");
  var noResults = document.getElementById("no-category-results");

  // Display translated category name in header
  if (nameDisplay && category) {
    var lang = localStorage.getItem("selectedLanguage") || "en";
    var categoryKeyMap = {
      pizza: "pizza",
      burger: "burger",
      momo: "momo",
      "grilled-chicken": "grilledChicken",
      wraps: "wraps",
      bowls: "bowls",
      patties: "patties",
    };
    var translationKey = categoryKeyMap[category];
    if (
      translationKey &&
      translations[lang] &&
      translations[lang][translationKey]
    ) {
      nameDisplay.textContent = translations[lang][translationKey];
    } else {
      nameDisplay.textContent = category
        .split("-")
        .map(function (w) {
          return w.charAt(0).toUpperCase() + w.slice(1);
        })
        .join(" ");
    }
  }

  var matches = category
    ? foodCatalog.filter(function (food) {
        return food.category === category;
      })
    : foodCatalog;

  if (matches.length === 0) {
    container.innerHTML = "";
    if (noResults) noResults.style.display = "block";
    return;
  }

  if (noResults) noResults.style.display = "none";

  container.innerHTML = matches
    .map(function (food) {
      var t = getTranslatedFood(food);
      var safeName = t.name
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
      var safeDetail = t.detail
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
      return (
        '<div class="food-card food-menu-box">' +
        '<div class="food-card-img-wrapper">' +
        '<img src="' +
        food.image +
        '" alt="' +
        safeName +
        '" class="food-card-img" />' +
        "</div>" +
        '<div class="food-card-body">' +
        "<h4>" +
        safeName +
        "</h4>" +
        '<p class="food-price">$' +
        food.price.toFixed(2) +
        "</p>" +
        '<p class="food-detail">' +
        safeDetail +
        "</p>" +
        '<div class="food-card-actions">' +
        '<a href="order.html" class="btn btn-primary" data-translate="orderNow">Order Now</a>' +
        "</div>" +
        "</div>" +
        "</div>"
      );
    })
    .join("");

  // Make dynamically added cards and their parent section visible
  container.closest(".section")?.classList.add("visible");
  container.querySelectorAll(".food-card").forEach(function (card) {
    card.classList.add("visible");
  });
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
  initializeCategoryPage();
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
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
        }
    `;
  document.head.appendChild(style);
}

// Initialize smooth scrolling when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
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
    searchBtn: "Search",
    orderNow: "Order Now",
    viewAll: "See All Food",
    addToOrder: "Add to Order",

    // Status Pills
    openNow: "Open Now",
    freeDelivery: "Free Delivery",
    rating: "4.9 Rating",

    // Category Badges
    badgeNew: "New",
    badgePopular: "Popular",
    badgeHot: "Hot",
    badgeChefsPick: "Chef's Pick",
    badgeTrending: "Trending",
    badgeHealthy: "Healthy",
    badgeClassic: "Classic",

    // Food Categories
    pizza: "Pizza",
    burger: "Burger",
    momo: "Momo",
    grilledChicken: "Grilled Chicken",
    wraps: "Wraps",
    bowls: "Bowls",
    patties: "Patties",

    // Home page
    homeTitle: "Best Food In The City",
    homeSubtitle:
      "Discover our delicious menu crafted with fresh ingredients and love.",

    // Categories
    categoriesTitle: "Explore Food",

    // Food page
    foodTitle: "Food Menu",

    // Food card names & descriptions
    foodMargherita: "Margherita Pizza",
    foodMargheritaDesc:
      "Classic Italian pizza with fresh mozzarella, basil, and rich tomato sauce on a crispy thin crust.",
    foodBurger: "Smoky BBQ Burger",
    foodBurgerDesc:
      "Juicy beef patty with smoky BBQ sauce, caramelized onions, cheddar cheese, and crisp lettuce.",
    foodMomo: "Chicken Steam Momo",
    foodMomoDesc:
      "Traditional steamed dumplings filled with seasoned chicken, ginger, garlic, and fresh herbs.",
    foodGrilledChicken: "Grilled Chicken Plate",
    foodGrilledChickenDesc:
      "Tender grilled chicken breast served with warm bread, fresh greens, peppers, and a zesty spice sauce.",
    foodWraps: "Lavash Wraps",
    foodWrapsDesc:
      "Soft lavash bread rolled with seasoned meat, fresh vegetables, herbs, and a creamy garlic sauce.",
    foodBowl: "Colorful Power Bowl",
    foodBowlDesc:
      "A vibrant bowl loaded with fresh veggies, grains, a perfectly fried egg, and a tangy dressing.",
    foodPatties: "Beef & Potato Patties",
    foodPattiesDesc:
      "Golden potato patties topped with seasoned beef, crispy shoestring fries, and a savory drizzle.",

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
    browseFood: "Browse Food",

    // Order page
    confirmYourOrder: "Confirm Your Order",
    selectedFood: "Selected Food",
    quantity: "Quantity",
    deliveryDetails: "Delivery Details",
    confirmOrder: "Confirm Order",
    orderPlaced: "Order Placed!",

    // Search page
    searchResultsFor: "Search Results for",
    noResults: "No food items found. Try a different search term.",

    // Category foods page
    foodIn: "Food in",
    noCategoryResults: "No food items found in this category.",

    // Checkout
    checkoutTitle: "Secure Checkout",
    yourOrder: "Your Order",
    deliveryInfo: "Delivery Information",
    paymentInfo: "Payment Information",
    cardNumber: "Card Number",
    cardInfo: "Card Information",
    expiryDate: "MM/YY",
    cvv: "CVV",
    billingInfo: "Billing Information",
    fullName: "Full Name",
    email: "Email Address",
    phone: "Phone Number",
    address: "Address",
    deliveryAddress: "Delivery Address",
    city: "City",
    zipCode: "ZIP Code",
    placeOrder: "Place Order",
    securePayment: "Secure Payment",
    completePayment: "Complete Secure Payment",
    weAccept: "We accept:",
    paymentSecurityNote:
      "Your payment information is encrypted and secure. We never store your card details on our servers.",
    paymentSuccessful: "Payment Successful!",
    thankYouOrder:
      "Thank you for your order! We'll start preparing your delicious food right away.",
    orderId: "Order ID:",
    estimatedDelivery: "Estimated Delivery:",
    estimatedTime: "30-45 minutes",
    continueShopping: "Continue Shopping",
    trackOrder: "Track Order",

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
    search: "Rechercher un plat..",
    searchBtn: "Rechercher",
    orderNow: "Commander",
    viewAll: "Voir Tous les Plats",
    addToOrder: "Ajouter à la Commande",

    // Status Pills
    openNow: "Ouvert",
    freeDelivery: "Livraison Gratuite",
    rating: "Note 4.9",

    // Category Badges
    badgeNew: "Nouveau",
    badgePopular: "Populaire",
    badgeHot: "Épicé",
    badgeChefsPick: "Choix du Chef",
    badgeTrending: "Tendance",
    badgeHealthy: "Sain",
    badgeClassic: "Classique",

    // Food Categories
    pizza: "Pizza",
    burger: "Burger",
    momo: "Momo",
    grilledChicken: "Poulet Grillé",
    wraps: "Wraps",
    bowls: "Bowls",
    patties: "Galettes",

    // Home page
    homeTitle: "La Meilleure Cuisine en Ville",
    homeSubtitle:
      "Découvrez notre délicieux menu préparé avec des ingrédients frais et beaucoup d'amour.",

    // Categories
    categoriesTitle: "Explorez nos Plats",

    // Food page
    foodTitle: "Notre Menu",

    // Food card names & descriptions
    foodMargherita: "Pizza Margherita",
    foodMargheritaDesc:
      "Pizza italienne classique avec mozzarella fraîche, basilic et sauce tomate sur une croûte fine et croustillante.",
    foodBurger: "Burger BBQ Fumé",
    foodBurgerDesc:
      "Steak de bœuf juteux avec sauce BBQ fumée, oignons caramélisés, cheddar et laitue croquante.",
    foodMomo: "Momo au Poulet Vapeur",
    foodMomoDesc:
      "Raviolis vapeur traditionnels farcis au poulet assaisonné, gingembre, ail et herbes fraîches.",
    foodGrilledChicken: "Assiette de Poulet Grillé",
    foodGrilledChickenDesc:
      "Blanc de poulet grillé tendre servi avec du pain chaud, des légumes verts, des poivrons et une sauce épicée.",
    foodWraps: "Wraps Lavash",
    foodWrapsDesc:
      "Pain lavash moelleux roulé avec de la viande assaisonnée, des légumes frais, des herbes et une sauce à l'ail crémeuse.",
    foodBowl: "Bowl Coloré Énergétique",
    foodBowlDesc:
      "Un bol vibrant garni de légumes frais, de céréales, d'un œuf parfaitement frit et d'une vinaigrette acidulée.",
    foodPatties: "Galettes Bœuf & Pommes de Terre",
    foodPattiesDesc:
      "Galettes de pommes de terre dorées garnies de bœuf assaisonné, de frites croustillantes et d'un filet de sauce savoureuse.",

    // Cart/Orders
    myOrdersTitle: "Mes Commandes",
    orderSummary: "Résumé de la Commande",
    subtotal: "Sous-total :",
    deliveryFee: "Frais de Livraison :",
    tax: "Taxe :",
    total: "Total :",
    clearOrders: "Vider les Commandes",
    proceedToCheckout: "Procéder au Paiement",
    emptyOrderTitle: "Votre commande est vide",
    emptyOrderText:
      "Ajoutez de délicieux plats à votre commande pour commencer !",
    browseFood: "Parcourir le Menu",

    // Order page
    confirmYourOrder: "Confirmez Votre Commande",
    selectedFood: "Plat Sélectionné",
    quantity: "Quantité",
    deliveryDetails: "Détails de Livraison",
    confirmOrder: "Confirmer la Commande",
    orderPlaced: "Commande passée !",

    // Search page
    searchResultsFor: "Résultats de recherche pour",
    noResults: "Aucun plat trouvé. Essayez un autre terme de recherche.",

    // Category foods page
    foodIn: "Plats dans",
    noCategoryResults: "Aucun plat trouvé dans cette catégorie.",

    // Checkout
    checkoutTitle: "Paiement Sécurisé",
    yourOrder: "Votre Commande",
    deliveryInfo: "Informations de Livraison",
    paymentInfo: "Informations de Paiement",
    cardNumber: "Numéro de Carte",
    cardInfo: "Informations de Carte",
    expiryDate: "MM/AA",
    cvv: "CVV",
    billingInfo: "Informations de Facturation",
    fullName: "Nom Complet",
    email: "Adresse Email",
    phone: "Numéro de Téléphone",
    address: "Adresse",
    deliveryAddress: "Adresse de Livraison",
    city: "Ville",
    zipCode: "Code Postal",
    placeOrder: "Passer la Commande",
    securePayment: "Paiement Sécurisé",
    completePayment: "Finaliser le Paiement Sécurisé",
    weAccept: "Nous acceptons :",
    paymentSecurityNote:
      "Vos informations de paiement sont chiffrées et sécurisées. Nous ne stockons jamais les détails de votre carte sur nos serveurs.",
    paymentSuccessful: "Paiement Réussi !",
    thankYouOrder:
      "Merci pour votre commande ! Nous commençons à préparer votre délicieux repas immédiatement.",
    orderId: "N° de Commande :",
    estimatedDelivery: "Livraison Estimée :",
    estimatedTime: "30-45 minutes",
    continueShopping: "Continuer vos Achats",
    trackOrder: "Suivre la Commande",

    // Footer
    followUs: "Suivez-Nous",

    // Messages
    languageChanged: "Langue changée en français",
    addedToOrder: "ajouté à la commande !",
    viewMyOrders: "Voir Mes Commandes",
    removedFromOrder: "retiré de la commande !",
    ordersCleared: "Commandes vidées !",
    ordersEmpty: "Vos commandes sont vides !",
    orderEmptyRedirect:
      "Vos commandes sont vides ! Redirection vers la page de commande...",
  },

  ar: {
    // Navigation
    home: "الرئيسية",
    categories: "الأصناف",
    food: "الطعام",
    myOrders: "طلباتي",
    contact: "تواصل معنا",

    // Common
    search: "ابحث عن طعام..",
    searchBtn: "بحث",
    orderNow: "اطلب الآن",
    viewAll: "عرض جميع الأطباق",
    addToOrder: "أضف للطلب",

    // Status Pills
    openNow: "مفتوح الآن",
    freeDelivery: "توصيل مجاني",
    rating: "تقييم 4.9",

    // Category Badges
    badgeNew: "جديد",
    badgePopular: "رائج",
    badgeHot: "حار",
    badgeChefsPick: "اختيار الشيف",
    badgeTrending: "شائع",
    badgeHealthy: "صحي",
    badgeClassic: "كلاسيكي",

    // Food Categories
    pizza: "بيتزا",
    burger: "برجر",
    momo: "مومو",
    grilledChicken: "دجاج مشوي",
    wraps: "لفائف",
    bowls: "أطباق",
    patties: "كفتة",

    // Home page
    homeTitle: "أفضل طعام في المدينة",
    homeSubtitle: "اكتشف قائمتنا الشهية المحضّرة بمكونات طازجة وبكل حب.",

    // Categories
    categoriesTitle: "استكشف أطباقنا",

    // Food page
    foodTitle: "قائمة الطعام",

    // Food card names & descriptions
    foodMargherita: "بيتزا مارغريتا",
    foodMargheritaDesc:
      "بيتزا إيطالية كلاسيكية بجبنة الموزاريلا الطازجة والريحان وصلصة الطماطم على عجينة رقيقة ومقرمشة.",
    foodBurger: "برجر باربكيو مدخّن",
    foodBurgerDesc:
      "قطعة لحم بقري طرية مع صلصة الباربكيو المدخّنة والبصل المكرمل وجبنة الشيدر والخس المقرمش.",
    foodMomo: "مومو دجاج على البخار",
    foodMomoDesc:
      "فطائر مبخّرة تقليدية محشوة بالدجاج المتبّل والزنجبيل والثوم والأعشاب الطازجة.",
    foodGrilledChicken: "طبق دجاج مشوي",
    foodGrilledChickenDesc:
      "صدر دجاج مشوي طري يُقدّم مع خبز دافئ وخضار طازجة وفلفل وصلصة بهارات شهية.",
    foodWraps: "لفائف لافاش",
    foodWrapsDesc:
      "خبز لافاش طري ملفوف بلحم متبّل وخضار طازجة وأعشاب وصلصة ثوم كريمية.",
    foodBowl: "وعاء الطاقة الملوّن",
    foodBowlDesc:
      "وعاء نابض بالحياة مليء بالخضار الطازجة والحبوب وبيضة مقلية بإتقان وصلصة منعشة.",
    foodPatties: "كفتة لحم وبطاطس",
    foodPattiesDesc:
      "أقراص بطاطس ذهبية مغطاة بلحم البقر المتبّل وبطاطس مقرمشة رفيعة وصلصة لذيذة.",

    // Cart/Orders
    myOrdersTitle: "طلباتي",
    orderSummary: "ملخص الطلب",
    subtotal: "المجموع الفرعي:",
    deliveryFee: "رسوم التوصيل:",
    tax: "الضريبة:",
    total: "الإجمالي:",
    clearOrders: "مسح الطلبات",
    proceedToCheckout: "متابعة الدفع",
    emptyOrderTitle: "طلبك فارغ",
    emptyOrderText: "أضف بعض الأطباق الشهية إلى طلبك للبدء!",
    browseFood: "تصفح القائمة",

    // Order page
    confirmYourOrder: "أكّد طلبك",
    selectedFood: "الطبق المختار",
    quantity: "الكمية",
    deliveryDetails: "تفاصيل التوصيل",
    confirmOrder: "تأكيد الطلب",
    orderPlaced: "تم تقديم الطلب!",

    // Search page
    searchResultsFor: "نتائج البحث عن",
    noResults: "لم يتم العثور على أطباق. جرّب كلمة بحث أخرى.",

    // Category foods page
    foodIn: "أطباق في",
    noCategoryResults: "لم يتم العثور على أطباق في هذا الصنف.",

    // Checkout
    checkoutTitle: "الدفع الآمن",
    yourOrder: "طلبك",
    deliveryInfo: "معلومات التوصيل",
    paymentInfo: "معلومات الدفع",
    cardNumber: "رقم البطاقة",
    cardInfo: "معلومات البطاقة",
    expiryDate: "شش/سس",
    cvv: "CVV",
    billingInfo: "معلومات الفوترة",
    fullName: "الاسم الكامل",
    email: "البريد الإلكتروني",
    phone: "رقم الهاتف",
    address: "العنوان",
    deliveryAddress: "عنوان التوصيل",
    city: "المدينة",
    zipCode: "الرمز البريدي",
    placeOrder: "تأكيد الطلب",
    securePayment: "دفع آمن",
    completePayment: "إتمام الدفع الآمن",
    weAccept: "نقبل:",
    paymentSecurityNote:
      "معلومات الدفع الخاصة بك مشفّرة وآمنة. لا نقوم أبداً بتخزين تفاصيل بطاقتك على خوادمنا.",
    paymentSuccessful: "تم الدفع بنجاح!",
    thankYouOrder: "شكراً لطلبك! سنبدأ بتحضير طعامك الشهي فوراً.",
    orderId: "رقم الطلب:",
    estimatedDelivery: "التوصيل المتوقع:",
    estimatedTime: "30-45 دقيقة",
    continueShopping: "متابعة التسوق",
    trackOrder: "تتبع الطلب",

    // Footer
    followUs: "تابعونا",

    // Messages
    languageChanged: "تم تغيير اللغة إلى العربية",
    addedToOrder: "تمت الإضافة للطلب!",
    viewMyOrders: "عرض طلباتي",
    removedFromOrder: "تمت الإزالة من الطلب!",
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
    "[data-translate-placeholder]",
  );
  inputElements.forEach((element) => {
    const key = element.getAttribute("data-translate-placeholder");
    if (translations[language] && translations[language][key]) {
      element.placeholder = translations[language][key];
    }
  });

  // Re-render dynamic food content if on those pages
  if (document.getElementById("search-results")) {
    runFoodSearch();
  }
  if (document.getElementById("category-results")) {
    initializeCategoryPage();
  }

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
  if (!menu || !toggleButton) return;

  const willOpen = !menu.classList.contains("active");

  menu.classList.toggle("active", willOpen);
  toggleButton.classList.toggle("active", willOpen);
  if (controls) controls.classList.toggle("active", willOpen);

  // Accessibility
  toggleButton.setAttribute("aria-expanded", willOpen ? "true" : "false");
  menu.setAttribute("aria-hidden", willOpen ? "false" : "true");

  // Prevent body scroll when menu open
  document.body.style.overflow = willOpen ? "hidden" : "";
  // Move navbar-controls into the menu on mobile so they appear below the links
  try {
    if (willOpen && controls && menu) {
      // Save original parent and nextSibling so we can restore later
      controls._originalParent = controls.parentNode;
      controls._originalNext = controls.nextSibling;
      menu.appendChild(controls);
    } else if (!willOpen && controls) {
      // restore to the original location if available
      if (controls._originalParent) {
        if (
          controls._originalNext &&
          controls._originalNext.parentNode === controls._originalParent
        ) {
          controls._originalParent.insertBefore(
            controls,
            controls._originalNext,
          );
        } else {
          controls._originalParent.appendChild(controls);
        }
      }
    }
  } catch (e) {
    // silently ignore DOM errors
  }
}

// Close mobile menu when clicking on menu links
function closeMobileMenu() {
  const menu = document.getElementById("navbar-menu");
  const controls = document.getElementById("navbar-controls");
  const toggleButton = document.querySelector(".mobile-menu-toggle");
  if (!menu || !toggleButton) return;

  menu.classList.remove("active");
  toggleButton.classList.remove("active");
  if (controls) controls.classList.remove("active");
  document.body.style.overflow = "";

  // Accessibility
  toggleButton.setAttribute("aria-expanded", "false");
  menu.setAttribute("aria-hidden", "true");

  // Ensure controls are restored back to original parent when menu is closed
  try {
    if (controls && controls._originalParent) {
      if (
        controls._originalNext &&
        controls._originalNext.parentNode === controls._originalParent
      ) {
        controls._originalParent.insertBefore(controls, controls._originalNext);
      } else {
        controls._originalParent.appendChild(controls);
      }
      // clean up saved refs
      delete controls._originalParent;
      delete controls._originalNext;
    }
  } catch (e) {}
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

// Scroll detection for glassmorphism navbar & scroll-to-top button
document.addEventListener("DOMContentLoaded", function () {
  const navbar = document.querySelector(".navbar");
  const scrollTopBtn = document.querySelector(".scroll-top-btn");

  window.addEventListener("scroll", () => {
    // Navbar scrolled state
    if (navbar) {
      if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
      } else {
        navbar.classList.remove("scrolled");
      }
    }
    // Scroll-to-top button visibility
    if (scrollTopBtn) {
      if (window.scrollY > 400) {
        scrollTopBtn.classList.add("visible");
      } else {
        scrollTopBtn.classList.remove("visible");
      }
    }
  });

  // Scroll-to-top click handler
  if (scrollTopBtn) {
    scrollTopBtn.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // Cursor glow tracking
  const cursorGlow = document.querySelector(".cursor-glow");
  if (cursorGlow) {
    let mouseX = 0,
      mouseY = 0;
    let glowX = 0,
      glowY = 0;

    document.addEventListener("mousemove", (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;
    });

    function animateGlow() {
      glowX += (mouseX - glowX) * 0.15;
      glowY += (mouseY - glowY) * 0.15;
      cursorGlow.style.left = glowX + "px";
      cursorGlow.style.top = glowY + "px";
      requestAnimationFrame(animateGlow);
    }
    animateGlow();
  }
});

// Order form submission (order.html)
document.addEventListener("DOMContentLoaded", function () {
  const orderForm = document.querySelector(".order-form");
  if (!orderForm || !window.location.pathname.includes("order.html")) return;

  // Populate order items from cart
  const orderItemsList = document.getElementById("order-items-list");
  if (orderItemsList) {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length === 0) {
      orderItemsList.innerHTML =
        '<p style="color:rgba(255,255,255,0.5);">Your cart is empty. <a href="foods.html" style="color:#667eea;">Browse food</a></p>';
    } else {
      let subtotal = 0;
      let html = "";
      cart.forEach(function (item) {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        html +=
          '<div style="display:flex;gap:1rem;align-items:center;padding:0.8rem 0;border-bottom:1px solid rgba(255,255,255,0.08);">' +
          '<img src="' +
          item.image +
          '" alt="' +
          item.name +
          '" style="width:60px;height:60px;object-fit:cover;border-radius:10px;">' +
          '<div style="flex:1;"><strong>' +
          item.name +
          "</strong><br>" +
          '<small style="color:rgba(255,255,255,0.5);">x' +
          item.quantity +
          "</small></div>" +
          '<span class="food-price">$' +
          itemTotal.toFixed(2) +
          "</span></div>";
      });
      const tax = subtotal * 0.08;
      const delivery = 3.99;
      const total = subtotal + tax + delivery;
      html +=
        '<div style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.15);font-size:0.9rem;">' +
        '<div style="display:flex;justify-content:space-between;margin-bottom:0.3rem;"><span>Subtotal</span><span>$' +
        subtotal.toFixed(2) +
        "</span></div>" +
        '<div style="display:flex;justify-content:space-between;margin-bottom:0.3rem;"><span>Tax (8%)</span><span>$' +
        tax.toFixed(2) +
        "</span></div>" +
        '<div style="display:flex;justify-content:space-between;margin-bottom:0.5rem;"><span>Delivery</span><span>$' +
        delivery.toFixed(2) +
        "</span></div>" +
        '<div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;"><span>Total</span><span>$' +
        total.toFixed(2) +
        "</span></div>" +
        "</div>";
      orderItemsList.innerHTML = html;
    }
  }

  orderForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length === 0) {
      showNotification("Your cart is empty. Add items first!");
      return;
    }

    const name = document.getElementById("full-name").value.trim();
    const phone = document.getElementById("contact").value.trim();
    const email = document.getElementById("email").value.trim();
    const address = document.getElementById("address").value.trim();

    if (!name || !phone || !email || !address) {
      showNotification("Please fill in all required fields.");
      return;
    }

    const submitBtn = orderForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = "Processing...";

    try {
      const response = await fetch("api/orders.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          name: name,
          email: email,
          phone: phone,
          address: address,
          items: cart.map(function (item) {
            return {
              title: item.name,
              price: item.price,
              quantity: item.quantity,
            };
          }),
        }),
      });

      const result = await response.json();

      if (result.success) {
        localStorage.removeItem("cart");
        cart.length = 0;
        updateCartDisplay();

        // Show success message
        orderForm.innerHTML =
          '<div style="text-align:center;padding:3rem;">' +
          '<i class="fas fa-check-circle" style="font-size:3rem;color:#48c78e;margin-bottom:1rem;display:block;"></i>' +
          "<h3>" +
          (getCurrentTranslation("orderPlaced") || "Order Placed!") +
          "</h3>" +
          '<p style="margin:1rem 0;color:rgba(255,255,255,0.6);">Order #' +
          result.data.order_number +
          "</p>" +
          '<p style="color:rgba(255,255,255,0.6);">Total: $' +
          parseFloat(result.data.total).toFixed(2) +
          "</p>" +
          '<a href="index.html" class="btn btn-primary" style="margin-top:1.5rem;display:inline-block;">Back to Home</a>' +
          "</div>";
      } else {
        showNotification(result.error || "Failed to place order.");
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    } catch (err) {
      showNotification("Connection error. Make sure XAMPP is running.");
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  });
});
