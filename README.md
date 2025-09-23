# 🍕 Food Order System - Secure Restaurant Ordering Platform

A modern, secure food ordering system with vibrant UI, dark mode, shopping cart functionality, and enterprise-grade payment processing using Stripe.

## ✨ Features

### 🎨 Modern UI/UX

- **Vibrant Color Scheme**: Beautiful, modern design with carefully selected colors
- **Dark Mode Toggle**: Seamless light/dark theme switching with persistence
- **Responsive Design**: Mobile-first approach, works on all devices
- **Smooth Animations**: Engaging user interface with CSS transitions

### 🛒 Shopping Cart System

- **Add to Cart**: One-click ordering from menu
- **Cart Management**: View, update quantities, remove items
- **Persistent Storage**: Cart preserved across browser sessions
- **Real-time Updates**: Live cart counter and totals

### 💳 Secure Payment Processing

- **Stripe Integration**: Industry-standard payment processing
- **PCI DSS Compliant**: Secure card data handling
- **256-bit SSL Encryption**: Enterprise-grade security
- **Real-time Validation**: Instant form validation and error handling
- **Transaction Logging**: Complete audit trail for all payments

### 🔒 Security Features

- **XSS Protection**: Input sanitization and validation
- **CSRF Protection**: Token-based request validation
- **Rate Limiting**: Prevents abuse and spam
- **Secure Headers**: Protection against common attacks
- **Error Logging**: Comprehensive logging system

### 📊 Admin Features

- **Order Management**: Track and manage customer orders
- **Transaction Logs**: View payment history and details
- **Menu Management**: Add, edit, and manage food items
- **Customer Database**: Secure customer information storage

## 🚀 Quick Start

### Prerequisites

- **XAMPP** (or any PHP 7.4+ server)
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Stripe Account** (for payment processing)

### Installation

1. **Clone/Download** the project to your XAMPP htdocs folder:

   ```
   c:\xampp\htdocs\food-order\
   ```

2. **Import Database**:

   - Start XAMPP (Apache + MySQL)
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import `database/setup.sql`

3. **Configure Stripe for Your Account**:

   **🔥 IMPORTANT: This is where YOU get paid!**

   a) **Create Your Stripe Account**:

   - Go to [stripe.com](https://stripe.com) and sign up
   - Complete account verification (add your bank account details)
   - This is where your customers' payments will be deposited!

   b) **Get Your API Keys**:

   - In Stripe Dashboard: Go to Developers > API keys
   - Copy your Publishable key (starts with `pk_test_`)
   - Copy your Secret key (starts with `sk_test_`)

   c) **Update Your Website**:

   - In `js/checkout.js` (line 3), replace:
     ```javascript
     const STRIPE_PUBLISHABLE_KEY = "pk_test_YOUR_ACTUAL_PUBLISHABLE_KEY_HERE";
     ```
   - In `config/config.php` (lines 28-29), replace:
     ```php
     define('STRIPE_SECRET_KEY', 'sk_test_YOUR_ACTUAL_SECRET_KEY_HERE');
     ```

   d) **Set Up Bank Account**:

   - In Stripe Dashboard: Settings > Bank accounts and scheduling
   - Add your bank account where you want to receive payments
   - Stripe will automatically transfer money to your account every 2 business days

   **📋 See `STRIPE_SETUP_GUIDE.md` for detailed step-by-step instructions**

4. **Set Permissions**:

   - Create logs directory: `mkdir logs`
   - Create cache directory: `mkdir cache`
   - Set write permissions for logs and cache folders

5. **Install Stripe PHP Library** (Optional but recommended):

   ```bash
   cd c:\xampp\htdocs\food-order
   composer require stripe/stripe-php
   ```

6. **Access Your Site**:
   - Main Site: `http://localhost/food-order`
   - Admin Panel: `http://localhost/food-order/admin`

## 📁 Project Structure

```
food-order/
├── index.html              # Main landing page
├── cart.html               # Shopping cart page
├── checkout.html           # Secure checkout page
├── categories.html         # Food categories
├── foods.html              # Food listings
├── order.html              # Order status
├── css/
│   └── style.css          # Complete styling system
├── js/
│   ├── script.js          # Core functionality
│   ├── cart.js            # Cart management
│   └── checkout.js        # Payment processing
├── api/
│   └── process-payment.php # Secure payment API
├── config/
│   └── config.php         # Configuration file
├── database/
│   └── setup.sql          # Database schema
├── admin/
│   └── index.php          # Admin panel
├── images/                # Food and UI images
└── logs/                  # Security and error logs
```

## 🔧 Configuration

### Environment Variables (Recommended for Production)

```bash
# Database
DB_HOST=localhost
DB_NAME=food_order
DB_USER=your_db_user
DB_PASS=your_secure_password

# Stripe
STRIPE_PUBLISHABLE_KEY=pk_live_your_key
STRIPE_SECRET_KEY=sk_live_your_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# Application
APP_URL=https://yourdomain.com
APP_ENV=production

# Email
SMTP_HOST=smtp.yourdomain.com
SMTP_USERNAME=orders@yourdomain.com
SMTP_PASSWORD=your_email_password
```

### Database Configuration

Edit `config/config.php` to match your database settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'food_order');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

## 💳 Payment Testing

### Test Credit Cards (Stripe Test Mode)

```
Visa: 4242 4242 4242 4242
Mastercard: 5555 5555 5555 4444
Amex: 3782 8224 6310 005
Declined: 4000 0000 0000 0002

Expiry: Any future date
CVC: Any 3-4 digits
ZIP: Any 5 digits
```

### Testing Scenarios

1. **Successful Payment**: Use `4242 4242 4242 4242`
2. **Declined Card**: Use `4000 0000 0000 0002`
3. **Insufficient Funds**: Use `4000 0000 0000 9995`
4. **Authentication Required**: Use `4000 0025 0000 3155`

## 🔒 Security Best Practices

### For Production Deployment:

1. **SSL Certificate**: Always use HTTPS
2. **Environment Variables**: Never commit API keys to version control
3. **Database Security**: Use strong passwords and limited user permissions
4. **Server Security**: Keep PHP and MySQL updated
5. **Backup Strategy**: Regular database and file backups

### Additional Security Measures:

- Change default admin credentials
- Enable database SSL connections
- Set up proper firewall rules
- Regular security updates
- Monitor logs for suspicious activity

## 📊 Database Schema

### Main Tables:

- **orders**: Customer orders and payment information
- **order_items**: Individual items in each order
- **customers**: Customer information
- **transaction_logs**: Payment transaction audit trail
- **menu_items**: Restaurant menu management
- **admin_users**: Admin panel access

### Key Features:

- **Foreign Key Constraints**: Data integrity
- **Indexes**: Optimized query performance
- **JSON Storage**: Flexible item data storage
- **Audit Trail**: Complete transaction logging

## 🎨 Customization

### Color Themes

Edit CSS custom properties in `css/style.css`:

```css
:root {
  --primary-color: #e74c3c;
  --secondary-color: #3498db;
  --success-color: #27ae60;
  --warning-color: #f39c12;
}
```

### Menu Items

Add/edit items in the database `menu_items` table or through the admin panel.

### Email Templates

Customize email templates in `api/process-payment.php` function `sendOrderConfirmationEmail()`.

## 🐛 Troubleshooting

### Common Issues:

1. **Payment Not Processing**:

   - Check Stripe API keys
   - Verify database connection
   - Check browser console for errors

2. **Database Connection Failed**:

   - Verify MySQL is running
   - Check database credentials
   - Ensure database exists

3. **Dark Mode Flash**:

   - Already fixed with inline JavaScript
   - Clear browser cache if still occurring

4. **Images Not Loading**:
   - Check file paths in HTML
   - Verify image files exist in `/images/` folder

### Debug Mode:

Set `APP_ENV=development` in config for detailed error messages.

## 📞 Support

For issues or questions:

1. Check the logs in `/logs/` directory
2. Review browser console for JavaScript errors
3. Verify database and Stripe configuration
4. Test with Stripe's test cards

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Credits

- **Stripe**: Payment processing
- **PHP**: Server-side processing
- **MySQL**: Database management
- **CSS Grid/Flexbox**: Responsive layouts
- **Vanilla JavaScript**: No framework dependencies

---

### 🚀 Ready to launch your restaurant online!

This system provides everything you need for a secure, modern food ordering platform. The codebase follows industry best practices for security, performance, and maintainability.
