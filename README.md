# 🏦 FranckBank - Cyber-Banking Terminal v1.0.0 🚀

![Angular](https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PHP Slim](https://img.shields.io/badge/PHP_Slim-8892BF?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![OAuth2](https://img.shields.io/badge/Security-OAuth2.0-green?style=for-the-badge&logo=json-web-tokens)
![Bitcoin](https://img.shields.io/badge/Crypto-Bitcoin-orange?style=for-the-badge&logo=bitcoin)

An ultra-modern, cyberpunk-themed digital banking web application featuring high-end reactive layouts, neon aesthetic accents, secure real-time state synchronization, and a terminal-inspired architecture.

---

## 🛠️ Tech Stack & Dependencies

### Frontend Architecture
* **Framework:** Angular (v17+) – Component-driven frontend structure with standalone components.
* **Styling Engine:** Tailwind CSS – Fluid util-first styling framework with dynamic theme configurations.
* **Typography:** Fira Code (Google Fonts) – Monospaced developer-centric layout.

### Backend & Database Engine
* **Database:** MySQL / MariaDB (InnoDB Engine)
* **Collation:** `utf8mb4_general_ci` (Full multibyte character support for internationalization and media strings).

---

## ⚡ Core Features & Services

* **Secure Authentication (Google OAuth):** Passwordless decentralized authentication infrastructure bound to unique node entities.
* **Global Account Hub:** Comprehensive asset profile displaying the system terminal status, unique `user_name`, linked communication routes (`email`), and localized account timestamps.
* **Real-time Liquidity Tracking:** Instant balance computational system displaying available fiat assets (`EUR`) synchronized with network ledger databases.
* **Transactional Ledger Processing:** Relational transaction accounting processing dual transaction mechanisms:
    * `deposit` – Financial node expansion routing incoming cashflows.
    * `withdrawal` – Direct capital liquidation.
* **Responsive Fluid Interfacing:** Borderless UI architecture scaling from 4K ultra-wide monitors down to Dynamic Viewport Heights (`100dvh`) on mobile terminals.

---

## 🔒 Security Architecture

* **Relational Cascading Safeguards:** Foreign key structures enforce absolute data integrity. Dropping an account automatically triggers an instant structural purge (`ON DELETE CASCADE`) preventing orphan records inside the transactional ledger.
* **Read-Only Client State Guards:** Sensitive data forms are structurally isolated from consumer injection fields using secure read-only DOM tags and native Angular data binding configurations (`{{...}}`).
* **Background Environment Isolation:** Peripheral CSS ambient animations run on decoupled hardware layers (`will-change: transform`, `pointer-events: none`, negative `z-index`), completely protecting interface fields from user interception or clickjacking exploits.

---

## 💻 Local Deployment & Environment Setup

Follow these procedures to launch your local node instance.

### 1. Database Initialization
Ensure your MySQL server instance is online. Launch your terminal database panel or chosen GUI manager (e.g., phpMyAdmin, DBeaver) and run the following script to deploy the database blueprint:

```sql
-- Create Database (Optional Initialization)
CREATE DATABASE IF NOT EXISTS franck_bank_db;
USE franck_bank_db;

-- Section 1: Core Accounts Ledger
CREATE TABLE `accounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255),  
  `profile_image` VARCHAR(500),                
  `currency` CHAR(3) NOT NULL DEFAULT 'EUR',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `google_id` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Section 2: Relational Transactions Database
CREATE TABLE `transactions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) NOT NULL,
  `description` VARCHAR(255) NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `type` ENUM('deposit', 'withdrawal') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
  `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
