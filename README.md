<!-- Improved compatibility of back to top link: See: https://github.com/othneildrew/Best-README-Template/pull/73 -->

<a id="readme-top"></a>

<!--
*** Thanks for checking out the Best-README-Template. If you have a suggestion
*** that would make this better, please fork the repo and create a pull request
*** or simply open an issue with the tag "enhancement".
*** Don't forget to give the project a star!
*** Thanks again! Now go create something AMAZING! :D
-->

<!-- PROJECT SHIELDS -->
<!--
*** I'm using markdown "reference style" links for readability.
*** Reference links are enclosed in brackets [ ] instead of parentheses ( ).
*** See the bottom of this document for the declaration of the reference variables
*** for contributors-url, forks-url, etc. This is an optional, concise syntax you may use.
*** https://www.markdownguide.org/basic-syntax/#reference-style-links
-->

<!-- [![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![Unlicense License][license-shield]][license-url] -->

<!-- PROJECT LOGO -->
<br />
  <p align="center">
    Humanoid BRAIL Online Examination Website — Polibatam
  </p>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
    </li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->

## About The Project

This repository implements an online examination platform built with Laravel and Tailwind CSS. The application provides a simple admin interface to prepare exams (periods), categories and questions, and a participant interface to take timed, optional password-protected exams.

Key behaviors implemented in this project:

- Questions support text and multiple-choice types and can include an uploaded image.
- Per-period duration (nullable) with a client-side countdown and server-side timestamp enforcement option.
- Per-period exam password (nullable) so administrators can protect access to a specific exam period.
- Tolerant answer checking for free-text questions: normalization (case, punctuation, whitespace) plus fuzzy similarity so minor typos won't unfairly mark answers wrong.
- Admin result pages with category breakdowns, earned/total scores and percentage displays.
- Participant flows for starting an exam, automatic submission when time expires, and viewing results.
- Images are stored using Laravel's filesystem (public disk) and shown to admins and participants when present.

This project is suitable as a foundation for classroom quizzes, certification tests, or any scenario that requires timed, invigilated-style assessments delivered via a web UI.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Built With

This is the language, tools, framework and libraries used in creating the Tracer Study application.

[![Build](https://skillicons.dev/icons?i=laravel,tailwind,js,html,css,php,vscode,github)](https://barelangfc.github.io/)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- GETTING STARTED -->

## Getting Started

There are several steps that must be taken to run the Tracer Study application.

### 1. Clone Repository
```bash
git clone https://github.com/terpalb24/pbl-trpl-206-tracer-study.git
cd pbl-trpl-206-tracer-study
```

### 2. Install Dependency
```bash
composer install
```

### 3. Salin File Environment
```bash
cp .env.example .env
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Konfigurasi `.env`
Ubah pengaturan database di `.env` sesuai konfigurasi lokal:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ujian_humanoid
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Migrasi dan Seeder Database
```bash
php artisan migrate --seed
```

### 7. Jalankan Server Lokal
```bash
php artisan serve
```

Akses aplikasi melalui: [http://127.0.0.1:8000](http://127.0.0.1:8000)

untuk custom port(contoh port 80) : 
```bash
php artisan serve --port=80
```

Akses aplikasi melalui: [http://127.0.0.1](http://127.0.0.1)

---

## 🛠 Persyaratan Sistem

- PHP >= 8.2
- Composer
- MySQL atau MariaDB

---
<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- LICENSE -->

## License

Distributed under the Unlicense License.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTACT -->

## Contact

Email - bimaadinirma.bima@gmail.com

Project Link: [https://github.com/Bimaadhinirma/websiite-ujian-online-humanoid-brail-polibatam.git](https://github.com/Bimaadhinirma/websiite-ujian-online-humanoid-brail-polibatam.git)

BarelangFC URL: [https://barelangfc.github.io/](https://barelangfc.github.io/)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->

[contributors-shield]: https://img.shields.io/github/contributors/terpalb24/pbl-trpl-206-tracer-study.svg?style=for-the-badge
[contributors-url]: https://github.com/terpalb24/pbl-trpl-206-tracer-study/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/vterpalb24/pbl-trpl-206-tracer-study.svg?style=for-the-badge
[forks-url]: https://github.com/terpalb24/pbl-trpl-206-tracer-study/network/members
[stars-shield]: https://img.shields.io/github/stars/terpalb24/pbl-trpl-206-tracer-study.svg?style=for-the-badge
[stars-url]: https://github.com/terpalb24/pbl-trpl-206-tracer-study/stargazers
[issues-shield]: https://img.shields.io/github/issues/terpalb24/pbl-trpl-206-tracer-study.svg?style=for-the-badge
[issues-url]: https://github.com/terpalb24/pbl-trpl-206-tracer-study/issues
[license-shield]: https://img.shields.io/github/license/terpalb24/pbl-trpl-206-tracer-study.svg?style=for-the-badge
[license-url]: https://https://barelangfc.github.io//LICENSE
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/othneildrew
[product-screenshot]: public/assets/images/product.png
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[vscode]: https://skillicons.dev/icons?i=vscode
[VSCODE.url]: https://code.visualstudio.com/
[JavaScript-shield]: https://img.shields.io/badge/JavaScript-323330?style=for-the-badge&logo=javascript&logoColor=F7DF1E
[JavaScript-url]: https://www.javascript.com/
[PHP-shield]: https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white
[PHP-url]: https://www.php.net/
[GitHub-shield]: https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white
[GitHub-url]: https://github.com/
[HTML5-shield]: https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white
[HTML5-url]: https://www.google.com/search?q=html&sca_esv=35aa2c76c27153e3&sxsrf=ADLYWIJHY-u2SSY7sARXtFKmLnKxgk88nw%3A1734520066865&ei=Aq1iZ6K7NO_CjuMPmoK4mAk&ved=0ahUKEwiixMPjlrGKAxVvoWMGHRoBDpMQ4dUDCBA&uact=5&oq=html&gs_lp=Egxnd3Mtd2l6LXNlcnAiBGh0bWwyChAjGIAEGCcYigUyCBAAGIAEGLEDMggQABiABBixAzILEAAYgAQYsQMYgwEyBRAAGIAEMgoQABiABBhDGIoFMgoQABiABBhDGIoFMgoQABiABBhDGIoFMgUQABiABDIFEAAYgARI8wRQuQJYuQJwAXgBkAEAmAFeoAFeqgEBMbgBA8gBAPgBAZgCAqACZ8ICChAAGLADGNYEGEfCAg0QABiABBiwAxhDGIoFmAMAiAYBkAYKkgcBMqAHtgU&sclient=gws-wiz-serp
[ionicon-shield]: https://img.shields.io/badge/Ionicons-3880FF?style=for-the-badge&logo=ionic&logoColor=white
[ionicon-url]: https://ionic.io/ionicons
[sweetalert-shield]: https://img.shields.io/badge/SweetAlert2-3880FF?style=for-the-badge
[sweetalert-url]: https://sweetalert2.github.io/
[fpdf-shield]: https://img.shields.io/badge/FPDF-3880FF?style=for-the-badge
[fpdf-url]: https://www.fpdf.org/
[tailwind]: https://skillicons.dev/icons?i=tailwind
[Tailwind-url]: https://tailwindcss.com/
[laravel]: https://skillicons.dev/icons?i=laravel
[Laravel-url]: https://laravel.com/