// Simple test script to verify checkout page functionality
console.log("Checkout test script loaded");

// Test basic DOM interaction
document.addEventListener("DOMContentLoaded", function () {
  console.log("Testing checkout page interactions...");

  // Test 1: Check if elements exist
  const form = document.getElementById("payment-form");
  const submitBtn = document.getElementById("submit-payment");
  const inputs = document.querySelectorAll("input");

  console.log("Form found:", !!form);
  console.log("Submit button found:", !!submitBtn);
  console.log("Inputs found:", inputs.length);

  // Test 2: Add simple click handlers
  if (submitBtn) {
    submitBtn.addEventListener("click", function (e) {
      console.log("Submit button clicked!");
      // Don't prevent default for now to test if click works
    });
  }

  // Test 3: Add input focus handlers
  inputs.forEach((input, index) => {
    input.addEventListener("focus", function () {
      console.log(`Input ${index} focused:`, input.id);
    });

    input.addEventListener("click", function () {
      console.log(`Input ${index} clicked:`, input.id);
    });
  });

  // Test 4: Check for overlaying elements
  const modals = document.querySelectorAll(".modal");
  modals.forEach((modal, index) => {
    const isVisible = getComputedStyle(modal).display !== "none";
    console.log(`Modal ${index} visible:`, isVisible);
    if (isVisible) {
      console.warn("Modal is visible and might be blocking interactions!");
    }
  });

  // Test 5: Simple interaction test
  document.addEventListener("click", function (e) {
    console.log(
      "Click detected on:",
      e.target.tagName,
      e.target.className,
      e.target.id
    );
  });

  console.log("Checkout test setup complete");
});
