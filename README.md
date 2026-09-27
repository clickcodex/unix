# ClickCodex - E-commerce Platform

ClickCodex is a professional PHP-based e-commerce application built with the **MVC architecture** and **Tailwind CSS** for a modern, fast, and responsive user experience.

## Key Features

- **MVC Architecture**: Clean separation of concerns with Models, Views, and Controllers.
- **Modern UI**: Built with **Tailwind CSS**, ensuring a fast, flexible, and responsive interface.
- **User Authentication**: Secure sign-up, login, and profile management.
- **Product Management**:
  - Browse products with high-quality images.
  - Detailed product pages with specifications and reviews.
- **Search & Filtering**: Advanced search with filters for price, category, and brands.
- **Shopping Cart**: Add, update, and remove items from the cart.
- **Wishlist**: Save products for later.
- **Category System**:Hierarchical categories with dedicated pages.

## Setup & Requirements

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache or Nginx)

### Installation
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd unix
   ```

2. Install dependencies (if any):
   ```bash
   composer install
   ```

3. Configure the database:
   - Copy `.env.example` to `.env`
   - Update database credentials in `.env`:
     ```env
     DB_HOST=localhost
     DB_NAME=unix
     DB_USER=root
     DB_PASS=your_password
     ```

4. Run database migrations:
   ```bash
   php spark db:migrate
   ```

5. Start the development server:
   ```bash
   php spark serve
   ```

## Technologies Used
- **Framework**: CodeIgniter 4
- **Styling**: Tailwind CSS
- **Database**: MySQL
- **Frontend**: Vanilla JavaScript, HTML5, CSS3

## Folder Structure
```
app/
├── Controllers/     # Application controllers
├── Models/          # Database models
├── Views/           # UI templates
│   ├── front/       # Frontend views
│   └── admin/       # Admin panel views
└── Config/          # Application configuration
public/              # Publicly accessible files
```

## License
MIT License

Admin Login:
admin@clickcodex.in


User Login:
user@clickcodex.in
user123
