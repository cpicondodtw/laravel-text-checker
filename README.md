# 🌐 Webpage Text Checker

A simple Laravel application to verify the presence of specific text strings on a webpage. Users can provide a URL and a CSV file of text to check against the desktop and/or mobile version of the site, with support for basic authentication.

## 🚀 About The Project

This tool was built to automate the process of content verification on websites. It's particularly useful for QA testers and content managers who need to ensure that specific copy, translations, or keywords are present on a given webpage.

The application loads the target URL in a headless browser, simulating either a desktop or mobile viewport. It then reads a list of text strings from an uploaded CSV file and reports which strings were found and which were missing from the page's content.

### ✨ Key Features
* URL & CSV Input: Simply provide a webpage URL and a CSV file containing the text you want to find.
* Device Simulation: Check the site's content on desktop, mobile, or both viewports.
* Authentication Support: Can handle pages protected by basic HTTP authentication (username/password).
* Exact Match Option: Perform a case-sensitive search for precise text matching.
* Clear Results: View found and missing text in organized, collapsible accordion sections.
* Loading Indicator: A spinner provides feedback while the check is in progress.
* Sample File: Users can download a sample CSV file to understand the required format.

### 🛠️ Built With
* Laravel
* Symfony Panther (for browser automation)
* PHP
* JavaScript (vanilla)
* HTML5 & CSS3

## 📦 Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites
Ensure you have the following installed on your system:

* PHP (>= 8.1)
* Composer
* Node.js & NPM
* A webdriver compatible with Symfony Panther, such as chromedriver.
<!--
# Example for macOS using Homebrew
brew install chromedriver

### Installation
Clone the repository

git clone [https://github.com/your_username/your_repository_name.git](https://github.com/your_username/your_repository_name.git)
cd your_repository_name

Install PHP dependencies

composer install

Install NPM dependencies

npm install && npm run build

Set up your environment file

cp .env.example .env

Generate an application key

php artisan key:generate

Make sure your .env file is configured correctly, especially the APP_URL.

Run the development server

php artisan serve

You should now be able to access the application at http://12I7.0.0.1:8000.

## 📁 File Structure
Here are the key files that power this application:

1. Controller
Path: app/Http/Controllers/TextCheckerController.php

Purpose: This file contains the core back-end logic.

The showForm() method displays the initial page.

The check() method handles the form submission. It validates the input, parses the uploaded CSV, initializes the Panther client, navigates to the URL (handling authentication and device viewports), and searches the page content for each text string. Finally, it returns the results to the view.

2. View
Path: resources/views/checker.blade.php

Purpose: This is the main and only view for the application.

It's a Laravel Blade template that renders the HTML form for user input.

It contains the HTML structure to display errors, submission data, and the final results.

All the necessary CSS for styling and the JavaScript for the accordion and loading spinner functionality are included directly in this file.

3. Routes
Path: routes/web.php

Purpose: This file defines the URL endpoints for the application.

A GET route (/) is defined to point to the showForm method in the TextCheckerController, which displays the main page.

A POST route (/check) is defined to point to the check method in the TextCheckerController, which processes the form data when the user clicks "Check Text".

// Example routes/web.php structure
use App\Http\Controllers\TextCheckerController;

Route::get('/', [TextCheckerController::class, 'showForm'])->name('checker.form');
Route::post('/check', [TextCheckerController::class, 'check'])->name('checker.check');
-->
