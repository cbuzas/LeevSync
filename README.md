<p align="center">
  <a href="https://github.com/cbuzas/LeevSync?raw=true" target="_blank">
    <img src="https://github.com/cbuzas/LeevSync/blob/main/.github/images/icon.png?raw=true" width="125" alt="Leev Sync Logo">
  </a>
</p>

<h1 align="center">Leev Sync.</h1>
<p align="center">
  <b>NativePHP based rsync management for desktop</b><br/>
  Simplify file synchronization and backup tasks—intuitive interface, comprehensive logging, cross-platform.
</p>

<h3 align="center">Download Now</h2>
<p align="center">
  <a href="https://github.com/cbuzas/LeevSync/releases/latest">
    <img src="https://img.shields.io/badge/macOS-000000?style=for-the-badge&logo=apple&logoColor=white" />
  </a>
</p>

---

## 📦 Description

**Leev Sync** is a modern, user-friendly rsync management tool designed to simplify file synchronization and backup tasks.

The project aims to provide an intuitive interface for managing rsync operations, making it accessible to developers who want to automate their backup workflows with simplicity.

---

<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/cbuzas/LeevSync/blob/main/.github/images/homepage.png?raw=true">
        <img alt="Homepage" width="1024" src="https://github.com/cbuzas/LeevSync/blob/main/.github/images/homepage.png?raw=true">
    </picture>
</p>


---

## ✨ Features

- ✅ **Task Management**: Create, edit, and delete rsync synchronization tasks with ease
- 📊 **Execution History**: View detailed logs and status of all synchronization runs
- 🎨 **Modern Interface**: Clean, intuitive UI built with Livewire and Flux UI components
- 🌓 **Dark Mode Support**: Comfortable viewing in any lighting condition
- 🪟 **Cross-Platform**: Built with NativePHP for Windows, macOS, and Linux support
- ⚡ **Status**: Monitor running tasks and view execution details
- 📝 **Comprehensive Logging**: Detailed logs for troubleshooting and audit trails
- 💾 **100% Local**: No cloud, no registration, just local file synchronization

---

## 🛠 Technology Stack

- **Laravel 12**: Modern PHP framework
- **Livewire 3**: Dynamic, reactive components
- **Flux UI**: Livewire UI component library
- **NativePHP**: Desktop application framework
- **Tailwind CSS 4**: Utility-first CSS framework
- **SQLite**: Embedded database for desktop deployment

---

## 📥 Download & Installation

### Option 1: Download the App

Head to the [latest release](https://github.com/cbuzas/LeevSync/releases/latest) and download:

- 🍏 **macOS**: `LeevSync.dmg`


Then:

- **macOS**: Open the `.dmg`, then drag Leev Sync to your Applications folder.

---

### Option 2: Build from Source (Developers)

```bash
# Clone the repo
git clone https://github.com/cbuzas/LeevSync.git
cd LeevSync

# Install dependencies
composer install
npm install

# Copy the example environment file
cp .env.example .env

# Generate an application key
php artisan key:generate

# Run database migrations
php artisan native:migrate

# Build assets
npm run build

# Build for your platform
php artisan native:build
```
---

### Project Goals

    By building Leev Sync with NativePHP, I aim to:

- 🔍 Explore the integration between Laravel and desktop application features
- 🚀 Test the performance and user experience of NativePHP-based applications
- 💡 Demonstrate real-world use cases for NativePHP beyond simple examples

This project is both a practical tool and a learning experience, pushing the boundaries of what's possible with PHP in the desktop application space.

---

## 💕 Support & License

<b>Love Leev Sync ? ⭐ Star this project!</b><br/>
If you find Leev Sync useful and enjoy using it, please consider giving it a star on GitHub! Your support helps the project grow and motivates continued development. Every star counts and is greatly appreciated!

<p>
  <a href="https://www.buymeacoffee.com/cbuzas" target="_blank">
    <img height="28px" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" />
  </a>
</p>

This project is licensed under the [MIT License](https://github.com/cbuzas/LeevSync/blob/main/LICENSE).


