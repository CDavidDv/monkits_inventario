# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is **MonKits Inventory System** - a comprehensive Laravel 8 + Vue.js 3 inventory management system for electronic components and kit assembly. The system manages individual components, nested kits, stock control with automated alerts, and includes role-based access control.

## Tech Stack Architecture

### Backend (Laravel 8.83.29)
- **Framework**: Laravel 8.x with PHP 7.3+/8.0+
- **Authentication**: Laravel Jetstream + Fortify (Sanctum for API tokens)
- **Permissions**: Spatie Laravel Permission package for role-based access
- **Database**: MySQL with Eloquent ORM
- **API Pattern**: Inertia.js for seamless SPA experience

### Frontend (Vue.js 3)
- **Framework**: Vue.js 3 with Composition API
- **Router**: Inertia.js (no Vue Router - server-side routing)
- **UI Framework**: Tailwind CSS
- **State Management**: Pinia store
- **Charts**: Chart.js with vue-chartjs
- **Icons**: Heroicons + Lucide Vue
- **Notifications**: SweetAlert2

### Build Tools
- **Bundler**: Vite 5.x
- **CSS**: Tailwind CSS with PostCSS
- **HMR**: Configured for local network development (IP: 192.168.1.71)

## Key Development Commands

### Backend Commands
```bash
# Start development server
php artisan serve

# Run migrations and seeders
php artisan migrate
php artisan db:seed

# Clear all caches
php artisan optimize:clear

# Run tests
php artisan test

# Check system status
php artisan about

# Generate application key
php artisan key:generate

# Check inventory alerts
php artisan inventory:check-alerts
```

### Frontend Commands
```bash
# Development with hot reload
npm run dev

# Production build
npm run build

# Install dependencies
npm install
```

## Database Architecture

### Core Models Hierarchy
- **User** → Spatie roles/permissions integration
- **Category** → Item categories with color coding
- **Item** → Central model for both components and kits
  - `type` field differentiates: 'component', 'kit', 'element'
  - Supports nested hierarchies via `ItemItems` pivot model
- **Supplier** → Vendor management with price tracking
- **InventoryMovement** → Complete audit trail of stock changes
- **StockAlert** & **InventoryAlert** → Automated low/high stock notifications

### Critical Relationships
- Items have self-referential many-to-many relationships through `ItemItems` for kit composition
- Role-based permissions control access to different system areas
- All inventory changes are logged in `InventoryMovement` for complete traceability

## Application Structure

### Route Organization
Routes are organized by user role and functionality:
- `/dashboard` - Main dashboard (DashboardController)
- `/inventario/*` - Inventory management (InventoryDashboardController)
- `/items/*` - Item CRUD operations (ItemController)  
- `/admin/*` - Admin-only functions (AdminController) - requires `admin_access` permission
- `/worker/*` - Worker dashboard and stock operations (WorkerController) - requires `worker_access` permission
- `/supervisor/*` - Supervisor analytics and limits (SupervisorController) - requires `supervisor_access` permission
- `/elements/*` - Advanced inventory elements (ElementController)
- `/api/inventory/*` - AJAX/API endpoints for Vue components

### Controller Responsibilities
- **ItemController**: Main inventory CRUD, hierarchical assignments, stock adjustments
- **InventoryDashboardController**: Dashboard stats, charts, alert management
- **AdminController**: User management, bulk operations, system administration
- **WorkerController**: Day-to-day inventory operations, stock movements
- **SupervisorController**: Analytics, reporting, stock limit management
- **ElementController**: Advanced element management with supplier integration

### Vue.js Component Architecture
- **Pages/**: Inertia.js page components organized by feature
- **Components/**: Reusable UI components (modals, charts, forms)
- **Layouts/AppLayout.vue**: Main application shell with navigation
- Uses Composition API throughout with `<script setup>` syntax

## Permission System

### Roles
- **admin**: Full system access, user management
- **manager**: Inventory management, reporting  
- **user**: Read-only inventory access
- **inventory**: Specialized inventory operations

### Key Permissions
- `admin_access`, `worker_access`, `supervisor_access` - Role-based dashboard access
- `manage_inventory` - Required for ItemController actions
- `manage elements stock` - Stock adjustment permissions
- `assemble kits` - Kit assembly operations
- `view reports`, `view analytics` - Reporting access

## Development Guidelines

### Laravel Conventions
- Follow PSR-12 coding standards
- Use Resource Controllers for CRUD operations
- Middleware for permission checks on routes
- Form Request classes for complex validation
- Database transactions for multi-table operations (especially inventory movements)

### Vue.js Patterns
- Use Composition API with `<script setup>`
- Leverage Inertia.js props for data passing from controllers
- Use Pinia for complex state management
- Component props validation with TypeScript-style definitions
- Emit events for parent-child communication

### Database Best Practices
- Always log inventory movements in `InventoryMovement` when changing stock
- Use database transactions for stock adjustments to maintain consistency
- Soft deletes where appropriate (items, categories)
- Foreign key constraints with proper cascade rules

## Testing Strategy
- Feature tests for inventory operations
- Unit tests for complex business logic (kit availability calculations)
- Permission tests for role-based access control
- Database tests for model relationships and constraints

## Key Business Logic

### Stock Management
- Items have `min_stock`, `max_stock`, and `current_stock` fields
- System automatically generates alerts when stock falls below/above thresholds
- All stock changes must be logged with movement type, quantity, and responsible user

### Kit System
- Kits can contain other kits (nested hierarchies)
- Kit availability calculated based on component stock levels
- Assembly process decrements component stock and increments kit stock

### Alert System
- Automated stock alerts generated via scheduled commands
- Alerts can be marked as read or resolved
- Different alert types: low_stock, high_stock, kit_unavailable

### Role-Based Dashboards
- Each role has customized dashboard with relevant metrics
- Workers focus on daily operations
- Supervisors see analytics and trend data
- Admins have full system oversight