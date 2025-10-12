# Image Cleaner Utility for ocStore / OpenCart
## Unused Image Cleanup Module for ocStore
[![License](https://img.shields.io/badge/license-BSD-blue.svg)](https://github.com/webitproff/oc-image_cleaner/blob/main/LICENSE)
[![Version](https://img.shields.io/badge/version-2.0.1-green.svg)](https://github.com/webitproff/oc-image_cleaner/releases)
[![ocStore Compatibility](https://img.shields.io/badge/ocStore-3.0.3.7-orange.svg)](https://ocstore.com/)
[![PHP](https://img.shields.io/badge/PHP-7.3-blueviolet.svg)](https://www.php.net/releases/7_3_0.php)

## Overview
**Image Cleaner** is a free module for ocStore/OpenCart designed to locate and delete unused images from the `/image/catalog/` directory. This module helps keep your website optimized by freeing up disk space, removing images that are not linked to products, categories, banners, manufacturers, informational pages, or blogs. 

The module has been thoroughly tested on a live website running on a local server and virtual hosting with **ocStore 3.0.3.7 + PHP 7.3**. It integrates into the admin panel, adding a menu item called "Image Cleanup" to initiate the scanning and deletion of unused images.

<img src="https://raw.githubusercontent.com/webitproff/oc-image_cleaner/refs/heads/main/Image-Cleaner-Utility.webp" alt="Image Cleaner Utility for ocStore / OpenCart">


## Introduction for Beginners
**Image Cleaner Utility** is a straightforward module for ocStore (or OpenCart) online stores, designed to find and remove unnecessary images from the `/image/catalog/` directory.

If you're unfamiliar with PHP, databases, FTP, or OCMOD, this document should guide you. Feel free to ask questions or consult a coding specialist.

**Why is this needed?** When you add products, categories, or banners to your store, images are saved in the `/image/catalog/` directory. If you delete a product, its images may remain, becoming "junk" that takes up server space. Image Cleaner identifies these files and allows you to delete them via the admin panel without diving into code.

**Important for beginners**: **Always back up your website** (all files + database) before using this module! Use FileZilla for files and phpMyAdmin for the database to ensure you can recover if something goes wrong.

### Why It Matters
Over time, online stores accumulate images that are no longer used. These files consume server space and can slow down or complicate backups. Image Cleaner safely removes such files, keeping your store efficient.

## Key Features
- Scans the `/image/catalog/` directory for unused images.
- Deletes identified files with action confirmation.
- Maintains a whitelist of critical files that cannot be deleted (`placeholder.png`, `no_image.png`, store logo).
- Checks HTML descriptions of products, pages, and blogs for used images.
- Displays scan and deletion results directly in the admin panel.
- Includes CSRF token protection (`user_token`).
- Verifies file permissions before deletion.
- Multilingual support, adding a menu item named:
  - "Image Cleanup" (English).
  - "Уборка в картинках" (Russian).
  - "Очищення зображень" (Ukrainian).
- **OCMOD**: The module currently **does not use OCMOD** (ocStore/OpenCart’s modifier system). Installation requires manually copying files and making a simple code edit in one file (see "Detailed Installation Instructions").
- Future OCMOD support is not planned; basic code-reading skills are sufficient, or consult someone with coding knowledge.

## Module File Structure
```
admin/
├── controller/
│   └── tool/
│       └── image_cleaner.php # Module logic
├── language/
│   ├── en-gb/tool/image_cleaner.php # English localization
│   ├── ru-ru/tool/image_cleaner.php # Russian localization
│   └── uk-ua/tool/image_cleaner.php # Ukrainian localization
└── view/
    └── template/
        └── tool/
            └── image_cleaner.twig # Interface display template
```

## Requirements (Check Before Installation)
Ensure your store meets these requirements for the module to work:
- **ocStore/OpenCart**: Version 3.0.3.7. If using a different version (e.g., OpenCart 3.x), test on a site copy.
- **PHP**: Version 7.3. Check in the admin panel: "System" → "Server Info" → locate "PHP Version."
- **File Access**: Requires FTP (via FileZilla) or access through your hosting panel (cPanel, DirectAdmin, etc.).
- **File Permissions**: The `/image/catalog/` directory must be readable and writable (permissions 755 for folders, 644 for files, sometimes 777).
- **Database**: MySQL or MariaDB, accessible via phpMyAdmin.
- **Blog Module (Optional)**: If using OCTemplates blog (tables `oct_blogarticle_***`), the module will check its images.

**How to check PHP version?**
1. Log into the admin panel.
2. Navigate to "System" → "Server Info."
3. Look for "PHP Version." If it’s not 7.3, ask your hosting provider to update.

## Detailed Installation Instructions
Follow these steps to ensure the module works correctly, even if you're a complete beginner.

### 1. Downloading the Module
- Visit the project page on GitHub: [https://github.com/webitproff/oc-image_cleaner](https://github.com/webitproff/oc-image_cleaner).
- Click "Code" → "Download ZIP."
- Extract the archive on your computer.
- Advanced users can use Git: `git clone https://github.com/webitproff/oc-image_cleaner.git`.

### 2. Copying Files
- Copy the contents of the `upload/` folder from the archive to the root directory of your ocStore/OpenCart site.
- Simply copy the `admin` folder into your site’s root directory.
- **Note**: Copying does not overwrite existing engine files.

### 3. Adding the Menu Item to the Admin Panel
- Open the file `admin/controller/common/column_left.php`.

<img src="https://raw.githubusercontent.com/webitproff/oc-image_cleaner/refs/heads/main/module-image-cleaner-utility-ocstore-opencart_2025-10-12_006.webp" alt="Image Cleaner Utility for ocStore / OpenCart">

- Locate the line:
  ```php
  $this->load->language('common/column_left');
  ```
- After it, add:
  ```php
  $this->load->language('tool/image_cleaner'); // Loads language translations for the module
  ```
- Find the `$data['menus'][]` array (the admin menu list). After the first menu item (usually Dashboard):
  ```php
  // Menu
  $data['menus'][] = array(
      'id' => 'menu-dashboard',
      'icon' => 'fa-dashboard',
      'name' => $this->language->get('text_dashboard'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      'children' => array()
  );
  ```
- Add the following code after it:
  ```php
  // Start Image Cleaner menu item
  $data['menus'][] = array(
      'id' => 'menu-tool-image-cleaner',
      'icon' => 'fa fa-trash-o',
      'name' => $this->language->get('image_cleaner_title'),
      'href' => $this->url->link('tool/image_cleaner', 'user_token=' . $this->session->data['user_token'], true),
      'children' => array()
  );
  // End Image Cleaner menu item
  ```
- This adds the "Image Cleanup" menu item with a trash icon to the admin panel’s left column.

### 4. Granting Module Access Permissions
To make the module visible and functional, assign permissions:
1. In the admin panel, go to **System → Users → User Groups**.
2. Find the group to grant access to (usually `Administrator`) and click **Edit**.
3. In the tabs:
   - **Access Permission**
   - **Modify Permission**
4. Check the box for:
   ```
   tool/image_cleaner
   ```
   in both lists.
5. Click **Save**. Users in this group will now see and use the module.

### 5. Checking Image Folder Permissions
- Ensure the `/image/catalog/` folder has read and write permissions.
- Typically, permissions of `755` are sufficient. If the module cannot see or delete files, temporarily set to `777`.

### 6. Clearing Cache
- In the admin panel, go to **System → Tools → Cache Manager**.
- Clear both the system cache and template cache.

## Using the Module
### 1. Accessing the Module
- In the admin panel, locate the "Image Cleanup" menu item.
- Click it to open the module interface.

### 2. Scanning Images
- Click the "Check" button.
- The module scans the `/image/catalog/` directory and analyzes the database to identify unused images.
- A list of unused images will appear after scanning.

### 3. Deleting Images
- If you’re sure you want to delete unused images, click the "Delete" button.
- A warning will appear, as deletion is irreversible.
- After confirmation, files not in use (and not in the whitelist) will be deleted.
- Results will display: successfully deleted files and any that couldn’t be deleted.

### 4. Backing Up
- Before deleting, always back up the `/image/catalog/` folder.
- This ensures you can restore files if important images are accidentally deleted.

## How the Module Works
- Checks database tables: `product`, `product_image`, `category`, `banner_image`, `manufacturer`, `information_description`, `product_description`, `oct_blogarticle`, `oct_blogarticle_description`.
- Extracts image paths from `image` fields.
- Analyzes HTML descriptions of products, pages, and blogs for `<img src>` and `<a href>` tags containing `/catalog/`.
- Creates a list of used images, removing duplicates.
- Compares files in `/image/catalog/` with this list.
- Files not in the database and not in the whitelist are considered unused.
- Upon deletion confirmation, files are removed if write permissions are available.

## Whitelist
Files that are never deleted:
- `catalog/placeholder.png`
- `catalog/no_image.png`
- Store logo (`config_logo`)

## Technical Details
- **Version**: 2.0.1
- **Compatibility**: ocStore 3.0.3.7, OpenCart 3.x
- **PHP**: 7.3
- Uses CSRF token (`user_token`) for security.
- Checks file permissions before deletion.
- Verifies table existence before SQL queries.

## Limitations
- Deletion is irreversible—always create a backup.
- Custom modules with images may not be accounted for.
- OCT Blog support requires `oct_blogarticle` and `oct_blogarticle_description` tables.

## Code Structure
### 1. Controller: `admin/controller/tool/image_cleaner.php`
The core logic, written in PHP.

#### `index()`
- Loads translations: `$this->load->language('tool/image_cleaner')`.
- Sets page title: "Cleanup Unused Images."
- Creates button links:
  - "Check" → `action=scan`
  - "Delete" → `action=delete`
- Loads the page template: `image_cleaner.twig`.

#### `scanUnusedImages($delete)`
- Collects all used images from the database:
  - Tables: `product`, `product_image`, `category`, `banner_image`, `manufacturer`, `oct_blogarticle`, `oct_blogarticle_image`.
  - Fields: `image` (e.g., `catalog/product.jpg`).
  - Descriptions: `information_description`, `product_description`, `oct_blogarticle_description` (searches for `<img src="catalog/...">`).
- Compares with files in `/image/catalog/`.
- If `$delete = true`, deletes unused files.
- Returns a list of results (file paths or messages).

#### How It Works
1. Queries database tables, e.g.:
   ```sql
   SELECT image FROM oc_product;
   ```
2. Parses HTML descriptions using regular expressions (`preg_match_all`) to find paths (`src`, `href`).
3. Normalizes paths:
   ```php
   $p = str_replace('\\', '/', $p); // Replaces \ with /
   $p = ltrim($p, '/'); // Removes leading /
   if (strpos($p, 'image/') === 0) {
       $p = substr($p, strlen('image/')); // Removes image/
   }
   ```
4. Traverses `/image/catalog/` using `RecursiveIteratorIterator`.
5. Ignores whitelisted files (`placeholder.png`, `no_image.png`, logo).

### 2. Language Files
Located in: `admin/language/*/tool/image_cleaner.php` (`ru-ru`, `en-gb`, `uk-ua`).

Example (English):
```php
$_['image_cleaner_title'] = 'Image Cleanup'; // Menu title
$_['heading_title'] = 'Cleanup Unused Images'; // Page title
$_['text_check'] = 'Check'; // Button
$_['text_delete'] = 'Delete'; // Button
$_['text_warning'] = 'Delete all unused images? This action is irreversible!';
```

For other languages, similar files exist. To add a new language, create a file in `admin/language/language_code/tool/`.

### 3. Template: `admin/view/template/tool/image_cleaner.twig`
- Title: `{{ heading_title }}`
- Buttons:
  - "Check": `{{ scan_url }}`
  - "Delete": `{{ delete_url }}` with confirmation
- Results area: `<pre>` with scrolling
- Backup warning: `{{ text_backup }}`
- GitHub link: `{{ image_cleaner_github }}`

### 4. Menu Integration: `admin/controller/common/column_left.php`
- Adds the menu item to the admin panel.
- Loads translations and creates a link with a trash icon (`fa-trash-o`).
- Requires manual file editing since OCMOD is not used.

## Technical Details
### Scanning
1. Checks database tables:
   - Standard: `product`, `product_image`, `category`, `banner_image`, `manufacturer`
   - OCTemplates Blog: `oct_blogarticle`, `oct_blogarticle_image`
2. Extracts image paths (e.g., `catalog/product.jpg`) from `image` fields.
3. Parses HTML in descriptions:
   - Fields: `information_description`, `product_description`, `oct_blogarticle_description`
   - Searches for `<img src="catalog/...">` and `href="catalog/..."` using `preg_match_all`
   - Also searches for paths like `image/catalog/...`
4. Removes duplicates and normalizes paths.
5. Compares with files in `/image/catalog/` using `RecursiveIteratorIterator`.

### Whitelist
- Protected files: `catalog/placeholder.png`, `catalog/no_image.png`
- Store logo checked via:
  ```php
  $query_logo = $this->db->query(
      "SELECT value FROM oc_setting WHERE key = 'config_logo' AND store_id = 0 LIMIT 1"
  );
  ```
- Logo is added to the whitelist if it exists.

### Deletion
- Checks file permissions: `is_writable`
- Deletes via `unlink`
- Status:
  - ✅ — Success
  - ❌ — Error

### Security
- Uses `user_token` for CSRF protection.
- Checks table existence before queries:
  ```php
  $check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "table'");
  ```
- Ignores empty descriptions and nonexistent folders.

## Requirements
- ocStore/OpenCart 3.0.3.7
- PHP 7.3 or higher
- Readable and writable `/image/catalog/` folder
- Required database tables

## Commands
- Scan: `tool/image_cleaner?action=scan&user_token=...`
- Delete: `tool/image_cleaner?action=delete&user_token=...`

## Author
- webitproff — [GitHub](https://github.com/webitproff)
- Date: October 12, 2025
- Project: [oc-image_cleaner](https://github.com/webitproff/oc-image_cleaner)

## License
BSD License

## Support and Contributions
- Create issues: [GitHub Issues](https://github.com/webitproff/oc-image_cleaner/issues)
- Pull requests are welcome.

___

# Image Cleaner Utility для ocStore / OpenCart
## Модуль Очистки Неиспользуемых Изображений для ocStore
[![Лицензия](https://img.shields.io/badge/лицензия-BSD-blue.svg)](https://github.com/webitproff/oc-image_cleaner/blob/main/LICENSE)
[![Версия](https://img.shields.io/badge/версия-2.0.1-green.svg)](https://github.com/webitproff/oc-image_cleaner/releases)
[![Совместимость с ocStore](https://img.shields.io/badge/ocStore-3.0.3.7-orange.svg)](https://ocstore.com/)
[![PHP](https://img.shields.io/badge/PHP-7.3-blueviolet.svg)](https://www.php.net/releases/7_3_0.php)

## Общая информация

**Image Cleaner** — это бесплатный модуль для ocStore/OpenCart, предназначенный для поиска и удаления неиспользуемых изображений из папки `/image/catalog/`. Этот модуль помогает поддерживать ваш сайт оптимизированным, освобождая дисковое пространство, удаляя изображения, которые не используются на сайте, например, не привязанные к товарам, категориям, баннерам, производителям, информационным страницам или блогам. 
Модуль десятки раз проверялся на работем сайте на локальном сервере и виртуальном хостинге в паре **ocStore 3.0.3.7 + PHP 7.3**.

Модуль интегрируется в административную панель и добавляет пункт меню «Уборка в картинках», через который можно запускать проверку и удаление неиспользуемых изображений.

## Введение для новичков

**Image Cleaner Utility** — это простой модуль для интернет-магазина на ocStore (или OpenCart), который помогает найти и удалить ненужные картинки из папки `/image/catalog/`. 
	Если вы не знаете, что такое PHP, база данных, FTP или OCMOD — этот документ должен помочь. Всегда можно задать вопрос или обратиться к любому специалисту-кодеру.

**Зачем это нужно?** Когда вы добавляете товары, категории или баннеры в магазин, изображения сохраняются в папке `/image/catalog/`. Если вы удаляете товар, его картинки могут остаться — это "мусор", который занимает место на сервере. Image Cleaner находит такие файлы и позволяет их удалить через админ-панель, не копаясь в коде.

**Важно для новичков**: **Сделайте резервную копию сайта** (все файлы + база данных) перед использованием! Используйте FileZilla для файлов и phpMyAdmin для базы данных. Это спасёт, если что-то пойдёт не так.


### Почему это важно

При работе с интернет-магазином со временем накапливаются изображения, которые больше нигде не используются. Они занимают место на сервере и могут замедлять или делать невозможным резервное копирование сайта. Image Cleaner позволяет безопасно удалить такие файлы.

## Основные возможности

* Сканирование папки `/image/catalog/` на наличие неиспользуемых изображений.
* Удаление найденных файлов с подтверждением действия.
* Белый список критически важных файлов, которые нельзя удалять (`placeholder.png`, `no_image.png`, логотип магазина).
* Проверка HTML-описаний товаров, страниц и блогов для поиска используемых изображений.
* Отображение результатов проверки и удаления прямо в админ-панели.
* Защита с помощью CSRF-токена (`user_token`).
* Проверка прав на файлы перед удалением.
* Многоязычная поддержка. Добавляет в админ-панель пункт меню с названием:
  - "Уборка в картинках" (русский).
  - "Image Cleanup" (английский).
  - "Очищення зображень" (украинский).
* **OCMOD**: На данный момент модуль **не использует OCMOD** (систему модификаторов ocStore/OpenCart).
	Установка требует ручного копирования файлов и простейшей правки кода в одном файле (см. раздел "Подробная инструкция по установке").
	В будущем, для **Image Cleaner Utility** поддержка OCMOD также не планируется, достаточно, если вы хоть не много можете читать код, а если нет - то стоит обратиться к такому человеку.


## Структура файлов модуля

```
admin/
├── controller/
│   └── tool/
│       └── image_cleaner.php   # Логика модуля
├── language/
│   ├── en-gb/tool/image_cleaner.php // английская локализация модуля
│   ├── ru-ru/tool/image_cleaner.php // русская локализация модуля
│   └── uk-ua/tool/image_cleaner.php // украинская локализация модуля
└── view/
    └── template/
        └── tool/
            └── image_cleaner.twig  # Шаблон отображения интерфейса управления утилитой
```


## Требования (проверьте перед установкой)

Чтобы модуль работал, ваш магазин должен соответствовать этим требованиям:
- **ocStore/OpenCart**: Версия 3.0.3.7. Если у вас другая версия (например, OpenCart 3.x), протестируйте на копии сайта.
- **PHP**: Версия 7.3. Проверьте в админке: "Система" → "Информация о сервере" → найдите "PHP Version".
- **Доступ к файлам**: Нужен FTP (через FileZilla) или доступ через панель хостинга (cPanel, DirectAdmin и т.д.).
- **Права на файлы**: Папка `/image/catalog/` должна быть доступна для чтения и записи (права 755 для папок, 644 для файлов, иногда 777).
- **База данных**: MySQL или MariaDB, доступ через phpMyAdmin.
- **Модуль блога (опционально)**: Если используете OCTemplates блог (таблицы `oct_blogarticle_***`), модуль проверит и его изображения.

**Как узнать версию PHP?**
1. Зайдите в админку.
2. Перейдите: "Система" → "Информация о сервере".
3. Найдите строку "PHP Version". Если не 7.3, попросите хостинг обновить.

   
## Подробная инструкция по установке

Ниже описаны шаги для абсолютного новичка, чтобы модуль заработал корректно.

### 1. Скачивание модуля

* Перейдите на страницу проекта на GitHub: [https://github.com/webitproff/oc-image_cleaner](https://github.com/webitproff/oc-image_cleaner)
* Нажмите кнопку "Code" → "Download ZIP".
* Распакуйте архив на вашем компьютере.
* Либо для продвинутых можно использовать Git: `git clone https://github.com/webitproff/oc-image_cleaner.git`

### 2. Копирование файлов

* Скопируйте содержимое папки `upload/` из архива в корневую директорию вашего сайта, где установлен ocStore/OpenCart. Просто тупо папку `admin` копируем в папку сайта.
* **При копировании, текущие файлы движка не перезаписываются!**


### 3. Добавление пункта меню в админку

* Откройте файл `admin/controller/common/column_left.php`.
* Найдите строку:

```php
$this->load->language('common/column_left');
```

* После неё добавьте следующий код:
```php
$this->load->language('tool/image_cleaner'); // строка загрузки языковой локализации image_cleaner (Загружает переводы для модуля) 
```
* Найдите массив $data['menus'][] (это список пунктов меню админки). После первого пункта (обычно Dashboard, Панель стану, Панель состояния)::

```php
// Menu
$data['menus'][] = array(
	'id'       => 'menu-dashboard',
	'icon'	   => 'fa-dashboard',
	'name'	   => $this->language->get('text_dashboard'),
	'href'     => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
	'children' => array()
);
```

* После неё добавьте следующий код:
```php

// start пункт меню утилиты image_cleaner
$data['menus'][] = array(
	'id'       => 'menu-tool-image-cleaner',
	'icon'     => 'fa fa-trash-o',
	'name'	   => $this->language->get('image_cleaner_title'),
	'href'     => $this->url->link('tool/image_cleaner', 'user_token=' . $this->session->data['user_token'], true),
	'children' => array()
);
// end пункт меню утилиты image_cleaner
```

* Это добавит пункт меню "Уборка в картинках" с иконкой корзины в левую колонку админки.

### 4. Проверка прав доступа к модулю

Чтобы модуль был виден и функционален, нужно выдать права пользователям:

1. В админ-панели перейдите в **Система → Пользователи → Группы пользователей**.
2. Найдите группу, которой нужно дать доступ (обычно `Администратор`), и нажмите **Изменить**.
3. Вкладки:

   * **Доступ к модулям (Access Permission)**
   * **Права на изменение (Modify Permission)**
4. В обоих списках поставьте галочку напротив:

```
tool/image_cleaner
```

5. Нажмите **Сохранить**. После этого пользователи группы смогут видеть модуль в меню и использовать его функционал.

### 5. Проверка прав на папку с изображениями

* Убедитесь, что папка `/image/catalog/` имеет права на чтение и запись.
* Обычно достаточно прав `755`. Если модуль не видит файлы или не может их удалить, можно временно выставить `777`.

### 6. Очистка кэша

* В админ-панели ocStore перейдите в **Система → Инструменты → Очистка кэша**.
* Нажмите кнопки очистки кэша системы и кэша шаблонов.

## Использование модуля

### 1. Открытие модуля

* В админ-панели найдите пункт меню «Уборка в картинках».
* Кликните по нему, откроется интерфейс модуля.

### 2. Сканирование изображений

* Нажмите кнопку «Проверить».
* Модуль просканирует папку `/image/catalog/` и проанализирует базу данных, чтобы определить, какие изображения не используются.
* После завершения сканирования появится список неиспользуемых изображений.

### 3. Удаление изображений

* Если вы уверены, что хотите удалить неиспользуемые изображения, нажмите кнопку «Удалить».
* Появится предупреждение, так как удаление необратимо.
* После подтверждения удалятся файлы, которые не используются, кроме тех, что находятся в белом списке.
* Результаты удаления отобразятся на экране: файлы, удаленные успешно, и файлы, которые не удалось удалить.

### 4. Резервное копирование

* Перед удалением обязательно сделайте резервную копию папки `/image/catalog/`.
* Это позволит восстановить файлы, если случайно будут удалены нужные изображения.

## Принцип работы модуля

* Модуль проверяет наличие таблиц базы данных: `product`, `product_image`, `category`, `banner_image`, `manufacturer`, `information_description`, `product_description`, `oct_blogarticle`, `oct_blogarticle_description`.
* Извлекает все пути изображений из полей `image`.
* Анализирует HTML-описания товаров, страниц и блогов для поиска всех `<img src>` и `<a href>` с `/catalog/`.
* Создает список всех используемых изображений и удаляет дубликаты.
* Сравнивает файлы в папке `/image/catalog/` с этим списком.
* Файлы, отсутствующие в базе и не входящие в белый список, считаются неиспользуемыми.
* При подтверждении удаления файлы удаляются, если есть права на запись.

## Белый список

Файлы, которые никогда не удаляются:

* `catalog/placeholder.png`
* `catalog/no_image.png`
* Логотип магазина (`config_logo`)

## Технические детали

* Версия: 2.0.1
* Совместимость: ocStore 3.0.3.7, OpenCart 3.x
* PHP: 7.3
* Использует CSRF-токен (`user_token`) для защиты от атак
* Проверяет права на файлы перед удалением
* Проверяет наличие таблиц перед выполнением SQL-запросов

## Ограничения

* Удаление необратимо — обязательно создавайте резервную копию
* Кастомные модули с изображениями могут не учитываться
* Поддержка OCT Blog только при наличии таблиц `oct_blogarticle` и `oct_blogarticle_description`


## Структура кода

### 1. Контроллер: `admin/controller/tool/image_cleaner.php`

Это "мозг" модуля, написан на PHP.

#### `index()`
- Загружает переводы: `$this->load->language('tool/image_cleaner')`.
- Устанавливает заголовок страницы: `Очистка неиспользуемых изображений`.
- Создаёт ссылки для кнопок:
  - "Проверить" → `action=scan`
  - "Удалить" → `action=delete`
- Загружает шаблон страницы: `image_cleaner.twig`.

#### `scanUnusedImages($delete)`
- Собирает все используемые изображения из базы данных:
  Таблицы: `product`, `product_image`, `category`, `banner_image`, `manufacturer`, `oct_blogarticle`, `oct_blogarticle_image`.  
  Поля: `image` (например, `catalog/product.jpg`).  
  Описания: `information_description`, `product_description`, `oct_blogarticle_description` (ищет `<img src="catalog/...">`).
- Сравнивает с файлами в `/image/catalog/`.
- Если `$delete = true`, удаляет ненужные файлы.
- Возвращает список результатов (пути файлов или сообщения).

#### Как работает
1. Проверяет таблицы базы данных, например:
   ```sql
   SELECT image FROM oc_product;
   ```
2. Парсит HTML-описания с помощью регулярных выражений (`preg_match_all`) для поиска путей (`src`, `href`).
3. Нормализует пути:
   ```php
   $p = str_replace('\\', '/', $p); // Заменяет \ на /
   $p = ltrim($p, '/');             // Убирает ведущий /
   if (strpos($p, 'image/') === 0) {
       $p = substr($p, strlen('image/')); // Убирает image/
   }
   ```
4. Обходит папку `/image/catalog/` с помощью `RecursiveIteratorIterator`.
5. Игнорирует файлы из белого списка (`placeholder.png`, `no_image.png`, логотип).

---

### 2. Языковые файлы
Расположение: `admin/language/*/tool/image_cleaner.php` (`ru-ru`, `en-gb`, `uk-ua`).

Пример (русский):
```php
$_['image_cleaner_title'] = 'Уборка в картинках'; // Название в меню
$_['heading_title'] = 'Очистка неиспользуемых изображений'; // Заголовок
$_['text_check'] = 'Проверить'; // Кнопка
$_['text_delete'] = 'Удалить'; // Кнопка
$_['text_warning'] = 'Удалить все неиспользуемые изображения? Это действие необратимо!';
```

> Для английского и украинского языков аналогично.
> При добавлении нового языка создайте файл в `admin/language/код_языка/tool/`.

---

### 3. Шаблон: `admin/view/template/tool/image_cleaner.twig`
- Заголовок: `{{ heading_title }}`
- Кнопки:
  - "Проверить": `{{ scan_url }}`
  - "Удалить": `{{ delete_url }}` с подтверждением
- Область результатов: `<pre>` с прокруткой
- Предупреждение о бэкапе: `{{ text_backup }}`
- Ссылка на GitHub: `{{ image_cleaner_github }}`

---

### 4. Интеграция в меню: `admin/controller/common/column_left.php`
- Добавляет пункт меню в админку.
- Загружает переводы и создаёт ссылку с иконкой корзины (`fa-trash-o`).

> Если модуль не использует OCMOD, файл редактируется вручную.

---

## Технические детали

### Сканирование
1. Проверяет таблицы базы данных:
   - Стандартные: `product`, `product_image`, `category`, `banner_image`, `manufacturer`
   - Блог OCTemplates: `oct_blogarticle`, `oct_blogarticle_image`
2. Извлекает пути изображений (например, `catalog/product.jpg`) из полей `image`.
3. Парсит HTML в описаниях:
   - Поля: `information_description`, `product_description`, `oct_blogarticle_description`
   - Ищет `<img src="catalog/...">` и `href="catalog/..."` с помощью `preg_match_all`
   - Также ищет пути вида `image/catalog/...`
4. Убирает дубликаты и нормализует пути (см. выше).
5. Сравнивает с файлами в `/image/catalog/` с помощью `RecursiveIteratorIterator`.

### Белый список
- Файлы, которые нельзя удалять: `catalog/placeholder.png`, `catalog/no_image.png`
- Логотип магазина проверяется так:
  ```php
  $query_logo = $this->db->query(
      "SELECT value FROM oc_setting WHERE key = 'config_logo' AND store_id = 0 LIMIT 1"
  );
  ```
- Логотип добавляется в белый список, если он существует.

### Удаление
- Проверяет права файла: `is_writable`
- Удаляет через `unlink`
- Статус:
  - ✅ — успешно
  - ❌ — ошибка

### Безопасность
- Использует `user_token` для защиты от CSRF
- Проверяет существование таблиц перед запросами:
  ```php
  $check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "table'");
  ```
- Игнорирует пустые описания и несуществующие папки

## Требования

* ocStore/OpenCart 3.0.3.7
* PHP 7.3 или выше
* Папка `/image/catalog/` с правами на чтение и запись
* Необходимые таблицы в базе данных

## Команды

* Сканирование: `tool/image_cleaner?action=scan&user_token=...`
* Удаление: `tool/image_cleaner?action=delete&user_token=...`

## Автор

* webitproff — [GitHub](https://github.com/webitproff)
* Дата: 12 октября 2025
* Проект: [oc-image_cleaner](https://github.com/webitproff/oc-image_cleaner)

## Лицензия

BSD License

## Поддержка и вклад

* Создавайте issue: [GitHub Issues](https://github.com/webitproff/oc-image_cleaner/issues)
* Pull request приветствуются
___

# Как работает модуль?

Модуль ищет файлы в папке `catalog` (место, где хранятся картинки для товаров, категорий, баннеров и т.д.). Он проверяет, используются ли эти файлы на сайте (например, в товарах, статьях или настройках). Если файл нигде не нужен, скрипт может его удалить, а заодно убрать пустые папки, где эти файлы лежали.

**Важно:** Скрипт работает с *любыми* файлами в папке `catalog` (не только с картинками, такими как `.jpg` или `.png`, но и с другими файлами — `.pdf`, `.txt`, и т.д.). Однако на практике в папке `catalog` обычно хранятся только изображения, так как это стандартная папка для картинок в ocStore.

---
## Как он работает?
### Где ищет файлы?
- Скрипт заходит в папку `catalog`, которая обычно находится по пути вроде: `/путь_к_сайту/image/catalog/`.
- Если этой папки нет, он выдаёт сообщение: `"Папка catalog не найдена, я не могу ничего сделать!"` — и останавливается.
---
### Как проверяет, нужен ли файл?
1. Скрипт собирает список используемых файлов (в основном — картинок), которые упомянуты на сайте.
2. Он смотрит в базе данных и проверяет:
   - Картинки товаров (фото в карточках товаров).
   - Картинки категорий (фото для разделов, типа "Одежда").
   - Картинки баннеров, производителей, блогов (если они есть).
   - Ссылки на файлы в текстах — например: `<img src="catalog/photo.jpg">` или `<a href="catalog/file.pdf">`.
3. Все найденные пути (`catalog/photo.jpg`, `catalog/file.pdf` и т.д.) записываются в список **нужных**.
4. Есть **белый список** — файлы, которые нельзя удалять:
   - `catalog/placeholder.png`
   - `catalog/no_image.png`
   - Логотип сайта (например, `catalog/logo.png`), если он указан в настройках.
---
### Как находит ненужные файлы?
- Скрипт проходит по всем файлам в папке `catalog` и её подпапках.
- Для каждого файла проверяется:
  1. Есть ли его путь в списке нужных?
  2. Входит ли файл в белый список?
  3. Если нет — файл считается **ненужным**.
---
### Удаляет только картинки или любые файлы?
**Ответ:** Скрипт может удалить *любой файл* в папке `catalog`, если он не используется на сайте и не входит в белый список. Это могут быть не только картинки (`.jpg`, `.png`, `.gif`), но и:
- `.pdf`
- `.txt`
- `.mp4`
- `.zip`
- и т.д.
На практике там почти всегда только изображения, но если в папке лежит что-то другое — оно тоже будет проверено.
---
### Как удаляет ненужные файлы?
- **Режим "просто проверить":**
  - Скрипт составляет список ненужных файлов (например, `catalog/photo.jpg`, `catalog/random.txt`) и показывает его пользователю, ничего не удаляя.
- **Режим "удалить":**
  - Скрипт пытается удалить ненужные файлы.
  - Проверяет права на удаление.
  - Успешно удалённые файлы добавляются в список "удалённых".
  - Если удалить не получилось (например, файл защищён) — он пропускается.
---
### Что с папками?
После удаления ненужных файлов:
- Скрипт проверяет подпапки в `catalog`.
- Если какая-то подпапка оказалась пустой — она тоже удаляется. (Например, `catalog/banners/` без файлов будет удалена.)
---
## Какие файлы и папки трогает?
### Папки
- Работает **только** с `catalog` (обычно `/путь_к_сайту/image/catalog/`) и её подпапками.
- В режиме удаления может удалить **пустые подпапки**.
### Файлы
- Проверяются **все файлы** в `catalog` и подпапках:
  - Картинки (`.jpg`, `.png`, `.gif`, и т.д.)
  - Любые другие файлы (`.pdf`, `.txt`, `.mp4`, `.zip`, и т.д.)
- **Исключения (не удаляются):**
  - `catalog/placeholder.png`
  - `catalog/no_image.png`
  - `catalog/logo.png` (если указан в настройках)
Все остальные, неиспользуемые файлы считаются ненужными и могут быть удалены.
---
## Пример, как это выглядит
**Структура:**
```
catalog/
  photo1.jpg
  document.pdf
  banners/
    banner1.jpg
    random.txt
```
**Сценарий:**
- В базе данных и текстах сайта найдены ссылки только на `photo1.jpg` и `banner1.jpg`.
- Файлы `document.pdf` и `random.txt` нигде не упоминаются.
**Если выбран режим проверки:**
```
Ненужные файлы:
catalog/document.pdf
catalog/banners/random.txt
```
**Если выбран режим удаления:**
- Удаляются `document.pdf` и `random.txt`.
- Если после этого папка `banners` пуста — она тоже удаляется.
---
## Удаляет только картинки или любые файлы?
Скрипт проверяет **все файлы**, не только изображения. Удаляет любые, если они:
- Не используются на сайте,
- Не входят в белый список.
---
## Что важно знать
- Скрипт **"аккуратный"** — не удалит нужные файлы или защищённые, **но удаляет всё, что не прикреплено к товарам**, статьям и т.д. в в `catalog` и в подпапках.
- В режиме проверки **ничего не удаляет**.
- Если папка `catalog` не найдена, скрипт выдаёт ошибку и завершает работу.
