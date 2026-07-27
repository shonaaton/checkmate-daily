# Checkmate Daily — WordPress Theme v2.0

## What's Fixed in v2.0
1. Logo: properly scaled, responsive
2. News page: dynamic category aggregation (auto-adds new categories)
3. Category structure: Chess State taxonomy + dynamic subcategories
4. Empty state: never blank — shows trending posts + message
5. Advanced filtering: AJAX filter by format + state on Chess News India
6. Cross-category suggestions: "Explore More Categories" on every page
7. Sidebar: single clean structure, no duplicates
8. Tutorials: removed from nav (still accessible as /category/tutorials/)
9. Auto-populate notice: hidden from frontend (backend only)
10. SEO: unique meta titles + descriptions + JSON-LD schema on all pages
11. UI: no emojis, clean professional typography throughout
12. Dynamic system: all new categories auto-appear without code changes

## Installation
1. Upload `checkmate-daily` folder to `/wp-content/themes/`
2. Activate in Appearance > Themes
3. Follow setup below

## Required Setup

### Step 1: Create Categories
Posts > Categories:
- news, blitz-rating, rapid-rating, classical, chess960, fide-rating, announcements

### Step 2: Create Pages
- Title: "Chess News India", Slug: chess-news-india, Template: Chess News India Hub
- Title: "News", Slug: news, Template: News Hub

### Step 3: Navigation Menu
Appearance > Menus — add: Home, News, Blitz, Rapid, Classical, Chess960, Chess News India
Assign to "Primary Navigation"
NOTE: Do NOT add Tutorials to menu — accessible via /category/tutorials/ only (Fix #8)

### Step 4: Chess State Taxonomy
Posts > Chess States — add all 28 Indian states with their slugs

### Step 5: Upcoming Events
Upcoming Events > Add New
Custom fields: event_date (YYYY-MM-DD), event_location (text)

### SEO URL Structure
| Page | URL | Target Keyword |
|------|-----|----------------|
| Home | / | chess news india |
| News | /news/ | chess news today india |
| Blitz | /category/blitz-rating/ | blitz chess india |
| Rapid | /category/rapid-rating/ | rapid chess india |
| Classical | /category/classical/ | classical chess india |
| Chess960 | /category/chess960/ | chess960 india |
| India Hub | /chess-news-india/ | chess news india |
| State | /chess-news-india/west-bengal/ | chess news west bengal |
