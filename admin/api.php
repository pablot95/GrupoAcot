<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$contentFile = __DIR__ . '/../data/content.json';
$uploadsBase = __DIR__ . '/../uploads/';

// Leer contenido actual
function getContent() {
    global $contentFile;
    return json_decode(file_get_contents($contentFile), true);
}

// Guardar contenido
function saveContent($content) {
    global $contentFile;
    file_put_contents($contentFile, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

// Generar nombre único para archivo
function generateFileName($originalName, $folder) {
    global $uploadsBase;
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) return false;
    
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    $name = $name ?: 'image';
    $fileName = $name . '.' . $ext;
    
    $fullPath = $uploadsBase . $folder . '/' . $fileName;
    $counter = 1;
    while (file_exists($fullPath)) {
        $fileName = $name . '_' . $counter . '.' . $ext;
        $fullPath = $uploadsBase . $folder . '/' . $fileName;
        $counter++;
    }
    
    return $fileName;
}

// Subir archivos
function uploadFiles($folder) {
    global $uploadsBase;
    $uploadedPaths = [];
    
    if (!isset($_FILES['files'])) return $uploadedPaths;
    
    $dir = $uploadsBase . $folder;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $files = $_FILES['files'];
    $count = count($files['name']);
    
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        
        // Verificar tamaño máximo (10MB)
        if ($files['size'][$i] > 10 * 1024 * 1024) continue;
        
        $fileName = generateFileName($files['name'][$i], $folder);
        if (!$fileName) continue;
        
        $targetPath = $dir . '/' . $fileName;
        if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
            $uploadedPaths[] = 'uploads/' . $folder . '/' . $fileName;
        }
    }
    
    return $uploadedPaths;
}

// Eliminar archivo físico
function deleteFile($path) {
    $fullPath = __DIR__ . '/../' . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

$action = $_POST['action'] ?? '';
$content = getContent();

switch ($action) {

    // ==================== OBRAS ====================
    
    case 'upload_obras':
        $categoryIndex = intval($_POST['category_index'] ?? -1);
        if (!isset($content['obras']['categories'][$categoryIndex])) {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            exit;
        }
        
        $uploadedPaths = uploadFiles('obras');
        if (empty($uploadedPaths)) {
            echo json_encode(['success' => false, 'message' => 'No se pudieron subir las imágenes. Formatos permitidos: JPG, PNG, GIF, WEBP. Máximo 10MB.']);
            exit;
        }
        
        foreach ($uploadedPaths as $path) {
            $content['obras']['categories'][$categoryIndex]['images'][] = $path;
        }
        saveContent($content);
        echo json_encode(['success' => true, 'message' => count($uploadedPaths) . ' imagen(es) subida(s)']);
        break;
    
    case 'delete_obras_image':
        $catIdx = intval($_POST['category_index'] ?? -1);
        $imgIdx = intval($_POST['image_index'] ?? -1);
        
        if (!isset($content['obras']['categories'][$catIdx]['images'][$imgIdx])) {
            echo json_encode(['success' => false, 'message' => 'Imagen no encontrada']);
            exit;
        }
        
        $imgPath = $content['obras']['categories'][$catIdx]['images'][$imgIdx];
        deleteFile($imgPath);
        array_splice($content['obras']['categories'][$catIdx]['images'], $imgIdx, 1);
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Imagen eliminada']);
        break;
    
    case 'delete_obras_category':
        $catIdx = intval($_POST['category_index'] ?? -1);
        if (!isset($content['obras']['categories'][$catIdx])) {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            exit;
        }
        
        // Eliminar archivos de la categoría
        foreach ($content['obras']['categories'][$catIdx]['images'] as $img) {
            deleteFile($img);
        }
        array_splice($content['obras']['categories'], $catIdx, 1);
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Categoría eliminada']);
        break;
    
    case 'add_obras_category':
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'El título es requerido']);
            exit;
        }
        
        $id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $content['obras']['categories'][] = [
            'id' => $id,
            'title' => $title,
            'images' => []
        ];
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Categoría creada']);
        break;
    
    case 'update_category_title':
        $catIdx = intval($_POST['category_index'] ?? -1);
        $title = trim($_POST['title'] ?? '');
        
        if (!isset($content['obras']['categories'][$catIdx])) {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            exit;
        }
        
        $content['obras']['categories'][$catIdx]['title'] = $title;
        $content['obras']['categories'][$catIdx]['id'] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Título actualizado']);
        break;

    // ==================== MANTENIMIENTO - IMPERMEABILIZACIÓN ====================
    
    case 'save_imp_texts':
        $content['mantenimiento']['impermeabilizacion']['title'] = trim($_POST['title'] ?? '');
        $content['mantenimiento']['impermeabilizacion']['subtitle'] = trim($_POST['subtitle'] ?? '');
        $content['mantenimiento']['impermeabilizacion']['description'] = trim($_POST['description'] ?? '');
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Textos guardados']);
        break;
    
    case 'save_alumanation':
        $content['mantenimiento']['impermeabilizacion']['alumanation']['title'] = trim($_POST['title'] ?? '');
        $content['mantenimiento']['impermeabilizacion']['alumanation']['tag'] = trim($_POST['tag'] ?? '');
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Alumanation actualizado']);
        break;
    
    case 'save_microgoma':
        $content['mantenimiento']['impermeabilizacion']['microgoma']['title'] = trim($_POST['title'] ?? '');
        $content['mantenimiento']['impermeabilizacion']['microgoma']['tag'] = trim($_POST['tag'] ?? '');
        $content['mantenimiento']['impermeabilizacion']['microgoma']['leadText'] = trim($_POST['leadText'] ?? '');
        $content['mantenimiento']['impermeabilizacion']['microgoma']['description'] = trim($_POST['description'] ?? '');
        
        $advantages = trim($_POST['advantages'] ?? '');
        $content['mantenimiento']['impermeabilizacion']['microgoma']['advantages'] = array_filter(array_map('trim', explode("\n", $advantages)));
        
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Microgoma actualizado']);
        break;
    
    case 'save_work_gallery_title':
        $content['mantenimiento']['impermeabilizacion']['workGallery']['title'] = trim($_POST['title'] ?? '');
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Título actualizado']);
        break;
    
    case 'upload_mant_images':
        $subsection = $_POST['subsection'] ?? '';
        $uploadedPaths = uploadFiles('mantenimiento');
        
        if (empty($uploadedPaths)) {
            echo json_encode(['success' => false, 'message' => 'No se pudieron subir las imágenes']);
            exit;
        }
        
        if ($subsection === 'alumanation') {
            foreach ($uploadedPaths as $path) {
                $content['mantenimiento']['impermeabilizacion']['alumanation']['images'][] = $path;
            }
        } elseif ($subsection === 'workGallery') {
            foreach ($uploadedPaths as $path) {
                $content['mantenimiento']['impermeabilizacion']['workGallery']['images'][] = $path;
            }
        }
        
        saveContent($content);
        echo json_encode(['success' => true, 'message' => count($uploadedPaths) . ' imagen(es) subida(s)']);
        break;
    
    case 'delete_mant_image':
        $subsection = $_POST['subsection'] ?? '';
        $imgIdx = intval($_POST['image_index'] ?? -1);
        
        if ($subsection === 'alumanation') {
            if (isset($content['mantenimiento']['impermeabilizacion']['alumanation']['images'][$imgIdx])) {
                deleteFile($content['mantenimiento']['impermeabilizacion']['alumanation']['images'][$imgIdx]);
                array_splice($content['mantenimiento']['impermeabilizacion']['alumanation']['images'], $imgIdx, 1);
            }
        } elseif ($subsection === 'workGallery') {
            if (isset($content['mantenimiento']['impermeabilizacion']['workGallery']['images'][$imgIdx])) {
                deleteFile($content['mantenimiento']['impermeabilizacion']['workGallery']['images'][$imgIdx]);
                array_splice($content['mantenimiento']['impermeabilizacion']['workGallery']['images'], $imgIdx, 1);
            }
        }
        
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Imagen eliminada']);
        break;
    
    case 'upload_video':
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Error al recibir el video']);
            exit;
        }
        
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'mp4') {
            echo json_encode(['success' => false, 'message' => 'Solo se permiten videos MP4']);
            exit;
        }
        
        // Máximo 50MB para videos
        if ($_FILES['file']['size'] > 50 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'El video no puede superar los 50MB']);
            exit;
        }
        
        // Eliminar video anterior si existe
        if (!empty($content['mantenimiento']['impermeabilizacion']['workGallery']['video'])) {
            deleteFile($content['mantenimiento']['impermeabilizacion']['workGallery']['video']);
        }
        
        $dir = $uploadsBase . 'mantenimiento';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        $videoName = 'video_' . time() . '.mp4';
        $targetPath = $dir . '/' . $videoName;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            $content['mantenimiento']['impermeabilizacion']['workGallery']['video'] = 'uploads/mantenimiento/' . $videoName;
            saveContent($content);
            echo json_encode(['success' => true, 'message' => 'Video subido correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el video']);
        }
        break;
    
    case 'delete_video':
        if (!empty($content['mantenimiento']['impermeabilizacion']['workGallery']['video'])) {
            deleteFile($content['mantenimiento']['impermeabilizacion']['workGallery']['video']);
            $content['mantenimiento']['impermeabilizacion']['workGallery']['video'] = '';
            saveContent($content);
        }
        echo json_encode(['success' => true, 'message' => 'Video eliminado']);
        break;

    // ==================== MANTENIMIENTO - PISOS ====================
    
    case 'save_pisos':
        $content['mantenimiento']['pisos']['title'] = trim($_POST['title'] ?? '');
        $content['mantenimiento']['pisos']['subtitle'] = trim($_POST['subtitle'] ?? '');
        $content['mantenimiento']['pisos']['description'] = trim($_POST['description'] ?? '');
        
        $services = trim($_POST['services'] ?? '');
        $content['mantenimiento']['pisos']['services'] = array_values(array_filter(array_map('trim', explode("\n", $services))));
        
        saveContent($content);
        echo json_encode(['success' => true, 'message' => 'Pisos actualizado']);
        break;
    
    case 'upload_piso_images':
        $uploadedPaths = uploadFiles('mantenimiento');
        
        if (empty($uploadedPaths)) {
            echo json_encode(['success' => false, 'message' => 'No se pudieron subir las imágenes']);
            exit;
        }
        
        foreach ($uploadedPaths as $path) {
            $content['mantenimiento']['pisos']['images'][] = $path;
        }
        saveContent($content);
        echo json_encode(['success' => true, 'message' => count($uploadedPaths) . ' imagen(es) subida(s)']);
        break;
    
    case 'delete_piso_image':
        $imgIdx = intval($_POST['image_index'] ?? -1);
        if (isset($content['mantenimiento']['pisos']['images'][$imgIdx])) {
            deleteFile($content['mantenimiento']['pisos']['images'][$imgIdx]);
            array_splice($content['mantenimiento']['pisos']['images'], $imgIdx, 1);
            saveContent($content);
        }
        echo json_encode(['success' => true, 'message' => 'Imagen eliminada']);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        break;
}
