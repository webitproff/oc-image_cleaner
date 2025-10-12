<?php
/**
 * Image Cleaner Utility: Controller file
 * Filename: image_cleaner.php
 * Location: admin/controller/tool
 * Version=2.0.1
 * Date=2025-10-12
 * @package image_cleaner for ocStore 3.0.3.7 on PHP 7.3
 * @author webitproff
 * @copyright Copyright (c) 2025 webitproff | https://github.com/webitproff
 * @license BSD License
 */
 
// Определяем класс ControllerToolImageCleaner, наследующий базовый класс Controller для работы в админке
class ControllerToolImageCleaner extends Controller {
    // Метод index — точка входа для отображения страницы модуля в админ-панели
    public function index() {
        // Загружаем языковой файл модуля для локализации интерфейса
        $this->load->language('tool/image_cleaner');
        // Устанавливаем заголовок страницы в админке, используя значение из языкового файла
        $this->document->setTitle($this->language->get('heading_title'));
        // Извлекаем токен текущего пользователя из сессии для защиты ссылок от CSRF
        $data['user_token'] = $this->session->data['user_token'];
        // Формируем URL для действия сканирования изображений, добавляя токен и параметр action=scan
        $data['scan_url'] = $this->url->link('tool/image_cleaner', 'user_token=' . $data['user_token'] . '&action=scan', true);
        // Формируем URL для действия удаления изображений, добавляя токен и параметр action=delete
        $data['delete_url'] = $this->url->link('tool/image_cleaner', 'user_token=' . $data['user_token'] . '&action=delete', true);
        // Загружаем контроллер для отображения стандартного хедера страницы админки
        $data['header'] = $this->load->controller('common/header');
        // Загружаем контроллер для отображения левой колонки админ-панели
        $data['column_left'] = $this->load->controller('common/column_left');
        // Загружаем контроллер для отображения футера страницы админки
        $data['footer'] = $this->load->controller('common/footer');
        // Инициализируем пустой массив для хранения результатов сканирования изображений
        $data['results'] = [];
        // Проверяем, присутствует ли GET-параметр 'action' в запросе
        if (isset($this->request->get['action'])) {
            // Сохраняем значение параметра 'action' в переменную для дальнейшей обработки
            $action = $this->request->get['action'];
            // Если действие равно 'scan', выполняем сканирование без удаления изображений
			if ($action == 'scan') {
				// Вызываем метод сканирования без удаления файлов
				$results = $this->scanUnusedImages(false);

				// Проверяем, вернул ли метод сообщение о том, что нечего удалять
				// Используем локализованную строку из языкового файла
				if (empty($results)) {
					$data['results'] = [];
					$data['text_results_count'] = $this->language->get('text_no_results');
				} else {
					$data['results'] = $results;
					$data['text_results_count'] = sprintf($this->language->get('text_found'), count($results));
				}

			} elseif ($action == 'delete') {
				// Вызываем метод сканирования с удалением файлов
				$deleted_files = $this->scanUnusedImages(true);

				// Если после удаления не осталось файлов, выводим локализованное сообщение
				if (empty($deleted_files)) {
					$data['results'] = []; // массив пустой
					$data['text_results_count'] = $this->language->get('text_no_results'); // "Нет неиспользуемых изображений"
				} else {
					// Если есть удалённые файлы, выводим их список и количество
					$data['results'] = $deleted_files;
					$data['text_results_count'] = sprintf($this->language->get('text_deleted'), count($deleted_files));
				}
			}

        }
        // Отображаем страницу через шаблон tool/image_cleaner, передавая данные в представление
        $this->response->setOutput($this->load->view('tool/image_cleaner', $data));
    }
    // Метод scanUnusedImages для поиска и, при необходимости, удаления неиспользуемых изображений
    private function scanUnusedImages($delete = false) {
        // Инициализируем пустой массив для хранения путей используемых изображений
        $used = [];
        // Определяем массив таблиц и соответствующих полей, где хранятся пути к изображениям
        $tables = [
            // Таблица товаров, поле image
            'product' => 'image',
            // Таблица дополнительных изображений товаров, поле image
            'product_image' => 'image',
            // Таблица категорий, поле image
            'category' => 'image',
            // Таблица баннеров, поле image
            'banner_image' => 'image',
            // Таблица производителей, поле image
            'manufacturer' => 'image',
            // Таблица изображений блогов, поле image
            'oct_blogarticle_image' => 'image',
            // Таблица блогов, поле image
            'oct_blogarticle' => 'image'
        ];
        // Проверяем существование каждой таблицы в базе данных
        foreach ($tables as $table => $field) {
            // Выполняем SQL-запрос для проверки наличия таблицы с префиксом DB_PREFIX
            $check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");
            // Если таблица не существует, удаляем её из массива таблиц
            if (!$check->num_rows) {
                unset($tables[$table]);
            }
        }
        // Проходим по всем оставшимся таблицам для извлечения путей изображений
        foreach ($tables as $table => $field) {
            // Выполняем SQL-запрос для получения непустых значений поля с изображениями
            $query = $this->db->query("SELECT `" . $field . "` FROM `" . DB_PREFIX . $table . "` WHERE `" . $field . "` != '' AND `" . $field . "` IS NOT NULL");
            // Перебираем все строки результата запроса
            foreach ($query->rows as $row) {
                // Добавляем путь изображения в массив используемых
                $used[] = $row[$field];
            }
        }
        // Выполняем SQL-запрос для получения описаний из таблицы information_description
        $query = $this->db->query("SELECT `description` FROM `" . DB_PREFIX . "information_description`");
        // Перебираем все строки с описаниями
        foreach ($query->rows as $row) {
            // Сохраняем текст описания в переменную
            $desc = $row['description'];
            // Пропускаем пустые описания
            if (!$desc) continue;
            // Ищем все атрибуты src или href, содержащие пути с /catalog/ в описании
            if (preg_match_all('/(?:src|href)\s*=\s*[\"\']([^\"\']*catalog\/[^\"\']*)[\"\']/i', $desc, $matches)) {
                // Перебираем все найденные пути
                foreach ($matches[1] as $img) {
                    // Проверяем, содержит ли путь подстроку catalog/
                    if (preg_match('#catalog/.*#i', $img, $m)) {
                        // Добавляем найденный путь в массив используемых изображений
                        $used[] = $m[0];
                    }
                }
            }
            // Ищем пути вида image/catalog/... в описании
            if (preg_match_all('#image\/catalog\/[^\s"\'<>]+#i', $desc, $matches2)) {
                // Перебираем все найденные пути
                foreach ($matches2[0] as $img) {
                    // Проверяем, содержит ли путь подстроку catalog/
                    if (preg_match('#catalog/.*#i', $img, $m2)) {
                        // Добавляем найденный путь в массив используемых изображений
                        $used[] = $m2[0];
                    }
                }
            }
        }
        // Проверяем существование таблицы product_description
        $check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_description'");
        // Если таблица существует, выполняем обработку
        if ($check->num_rows) {
            // Выполняем SQL-запрос для получения описаний товаров
            $query = $this->db->query("SELECT `description` FROM `" . DB_PREFIX . "product_description`");
            // Перебираем все строки с описаниями товаров
            foreach ($query->rows as $row) {
                // Сохраняем текст описания в переменную
                $desc = $row['description'];
                // Пропускаем пустые описания
                if (!$desc) continue;
                // Ищем все атрибуты src или href, содержащие пути с /catalog/
                if (preg_match_all('/(?:src|href)\s*=\s*[\"\']([^\"\']*catalog\/[^\"\']*)[\"\']/i', $desc, $matches)) {
                    // Перебираем все найденные пути
                    foreach ($matches[1] as $img) {
                        // Проверяем, содержит ли путь подстроку catalog/
                        if (preg_match('#catalog/.*#i', $img, $m)) {
                            // Добавляем найденный путь в массив используемых изображений
                            $used[] = $m[0];
                        }
                    }
                }
                // Ищем пути вида image/catalog/... в описании
                if (preg_match_all('#image\/catalog\/[^\s"\'<>]+#i', $desc, $matches2)) {
                    // Перебираем все найденные пути
                    foreach ($matches2[0] as $img) {
                        // Проверяем, содержит ли путь подстроку catalog/
                        if (preg_match('#catalog/.*#i', $img, $m2)) {
                            // Добавляем найденный путь в массив используемых изображений
                            $used[] = $m2[0];
                        }
                    }
                }
            }
        }
        // Проверяем существование таблицы oct_blogarticle_description
        $check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "oct_blogarticle_description'");
        // Если таблица существует, выполняем обработку
        if ($check->num_rows) {
            // Выполняем SQL-запрос для получения описаний блогов
            $query = $this->db->query("SELECT `description` FROM `" . DB_PREFIX . "oct_blogarticle_description`");
            // Перебираем все строки с описаниями блогов
            foreach ($query->rows as $row) {
                // Сохраняем текст описания в переменную
                $desc = $row['description'];
                // Пропускаем пустые описания
                if (!$desc) continue;
                // Ищем все атрибуты src или href, содержащие пути с /catalog/
                if (preg_match_all('/(?:src|href)\s*=\s*[\"\']([^\"\']*catalog\/[^\"\']*)[\"\']/i', $desc, $matches)) {
                    // Перебираем все найденные пути
                    foreach ($matches[1] as $img) {
                        // Проверяем, содержит ли путь подстроку catalog/
                        if (preg_match('#catalog/.*#i', $img, $m)) {
                            // Добавляем найденный путь в массив используемых изображений
                            $used[] = $m[0];
                        }
                    }
                }
                // Ищем пути вида image/catalog/... в описании
                if (preg_match_all('#image\/catalog\/[^\s"\'<>]+#i', $desc, $matches2)) {
                    // Перебираем все найденные пути
                    foreach ($matches2[0] as $img) {
                        // Проверяем, содержит ли путь подстроку catalog/
                        if (preg_match('#catalog/.*#i', $img, $m2)) {
                            // Добавляем найденный путь в массив используемых изображений
                            $used[] = $m2[0];
                        }
                    }
                }
            }
        }
        // Приводим пути изображений к единому формату и удаляем дубликаты
        $used = array_filter(array_unique(array_map(function($p) {
            // Заменяем обратные слэши на прямые для единообразия
            $p = str_replace('\\', '/', $p);
            // Удаляем ведущий слэш, если он есть
            $p = ltrim($p, '/');
            // Если путь начинается с image/, удаляем этот префикс
            if (strpos($p, 'image/') === 0) {
                $p = substr($p, strlen('image/'));
            }
            // Возвращаем обработанный путь
            return $p;
        }, $used)));
        // Устанавливаем путь к папке с изображениями (catalog)
        $image_path = DIR_IMAGE . 'catalog/';
        // Проверяем, существует ли папка с изображениями
        if (!is_dir($image_path)) {
            // Если папка не найдена, возвращаем сообщение об ошибке
            return ['Ошибка: не найдена папка ' . $image_path];
        }
        // Инициализируем массив для хранения путей неиспользуемых изображений
        $unused = [];
        // Определяем белый список изображений, которые нельзя удалять
        $whitelist = ['catalog/placeholder.png', 'catalog/no_image.png'];

        // === ДОБАВЛЯЕМ ПРОВЕРКУ ЛОГОТИПА ===
        // Получаем текущий логотип из настроек для основного магазина
        $query_logo = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_logo' AND `store_id` = 0 LIMIT 1");
        // Если логотип установлен
        if ($query_logo->num_rows && $query_logo->row['value']) {
            // Сохраняем путь логотипа
            $logo = $query_logo->row['value'];
            // Приводим путь к формату catalog/... (удаляем image/ если есть)
            $logo = str_replace('\\', '/', $logo); // Заменяем обратные слэши
            $logo = ltrim($logo, '/'); // Убираем ведущий слэш
            if (strpos($logo, 'image/') === 0) {
                $logo = substr($logo, strlen('image/')); // Убираем префикс image/
            }
            // Добавляем логотип в белый список в формате catalog/...
            $whitelist[] = $logo;
        }
        // === КОНЕЦ ПРОВЕРКИ ЛОГОТИПА ===

        // Создаём итератор для рекурсивного обхода папки с изображениями, исключая точки (. и ..)
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($image_path, RecursiveDirectoryIterator::SKIP_DOTS));
        // Перебираем все файлы в папке с изображениями
        foreach ($rii as $file) {
            // Пропускаем директории, обрабатываем только файлы
            if ($file->isDir()) continue;
            // Получаем полный путь к файлу
            $fullPath = $file->getPathname();
            // Преобразуем полный путь в относительный, начиная с catalog/
            $relative = 'catalog/' . str_replace('\\', '/', substr($fullPath, strlen($image_path)));
            // Пропускаем файлы, входящие в белый список
            if (in_array($relative, $whitelist)) continue;
            // Проверяем, отсутствует ли файл в списке используемых изображений
				if (!in_array($relative, $used)) {
					if ($delete) {
						// Пытаемся удалить файл и добавляем только успешное удаление в массив
						if (is_writable($fullPath) && @unlink($fullPath)) {
							$unused[] = $relative; // только реально удалённый файл
						}
						// ошибки удаления можно логировать отдельно при необходимости
					} else {
						$unused[] = $relative; // сканирование без удаления
					}
				}
        }
		// Если не найдено неиспользуемых изображений и режим не удаления
		if (empty($unused) && !$delete) {
			return []; // возвращаем пустой массив, чтобы не было ложного элемента
		}
		// Удаляем пустые папки после удаления файлов
		if ($delete) {
			$directories = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($image_path, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($directories as $dir) {
				if ($dir->isDir()) {
					@rmdir($dir->getPathname());
				}
			}
		}
        // Возвращаем массив с путями неиспользуемых изображений или сообщениями
        return $unused;
    }
}

