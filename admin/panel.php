<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$contentFile = __DIR__ . '/../data/content.json';
$content = json_decode(file_get_contents($contentFile), true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin — Grupo ACOT</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="admin-nav-inner">
            <div class="admin-brand">
                <h2>ACOT S.A.</h2>
                <span class="admin-badge">Admin</span>
            </div>
            <div class="admin-nav-links">
                <a href="#obras" class="nav-tab active" data-tab="obras">Obras</a>
                <a href="#mantenimiento" class="nav-tab" data-tab="mantenimiento">Mantenimiento</a>
            </div>
            <div class="admin-nav-right">
                <span class="admin-user">
                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?php echo htmlspecialchars($_SESSION['admin_user']); ?>
                </span>
                <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="admin-main">
        <!-- ==================== OBRAS TAB ==================== -->
        <div id="tab-obras" class="tab-content active">
            <div class="admin-header">
                <h1>Gestión de Obras</h1>
                <p>Administre las imágenes de la galería de obras por categoría.</p>
                <button class="btn btn-primary" onclick="addCategory()">
                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nueva Categoría
                </button>
            </div>

            <div id="obras-categories" class="categories-grid">
                <?php foreach ($content['obras']['categories'] as $index => $cat): ?>
                <div class="category-card" data-category-index="<?php echo $index; ?>">
                    <div class="category-header">
                        <input type="text" class="category-title-input" value="<?php echo htmlspecialchars($cat['title']); ?>" data-field="title" data-section="obras" data-index="<?php echo $index; ?>">
                        <div class="category-actions">
                            <button class="btn-icon btn-danger" onclick="deleteCategory('obras', <?php echo $index; ?>)" title="Eliminar categoría">
                                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="images-grid" id="obras-images-<?php echo $index; ?>">
                        <?php foreach ($cat['images'] as $imgIndex => $img): ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <img src="../<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($cat['title']); ?>">
                            </div>
                            <div class="image-actions">
                                <button class="btn-icon btn-danger" onclick="deleteImage('obras', <?php echo $index; ?>, <?php echo $imgIndex; ?>)" title="Eliminar imagen">
                                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="upload-zone" id="upload-obras-<?php echo $index; ?>">
                        <form class="upload-form" data-section="obras" data-category="<?php echo $index; ?>">
                            <label class="upload-label">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>Subir imágenes</span>
                                <input type="file" name="images[]" multiple accept="image/*" onchange="uploadImages(this, 'obras', <?php echo $index; ?>)" hidden>
                            </label>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ==================== MANTENIMIENTO TAB ==================== -->
        <div id="tab-mantenimiento" class="tab-content">
            <div class="admin-header">
                <h1>Gestión de Mantenimiento</h1>
                <p>Administre las imágenes y textos de la sección de mantenimiento.</p>
            </div>

            <!-- IMPERMEABILIZACIÓN -->
            <div class="mant-section">
                <h2 class="mant-section-title">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    Impermeabilización
                </h2>

                <!-- Textos generales -->
                <div class="edit-block">
                    <h3>Textos Generales</h3>
                    <div class="form-group">
                        <label>Título de la sección</label>
                        <input type="text" id="imp-title" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['title']); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Subtítulo</label>
                        <input type="text" id="imp-subtitle" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['subtitle']); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="imp-description" class="form-input" rows="3"><?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['description']); ?></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="saveTexts('impermeabilizacion')">Guardar Textos</button>
                </div>

                <!-- Alumanation 301 -->
                <div class="edit-block">
                    <h3>Alumanation 301</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Título del producto</label>
                            <input type="text" id="alum-title" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['alumanation']['title']); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Etiqueta</label>
                            <input type="text" id="alum-tag" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['alumanation']['tag']); ?>" class="form-input">
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="saveAlumanation()">Guardar Textos Alumanation</button>
                    
                    <div class="images-grid" id="alum-images">
                        <?php foreach ($content['mantenimiento']['impermeabilizacion']['alumanation']['images'] as $imgIndex => $img): ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <img src="../<?php echo htmlspecialchars($img); ?>" alt="Alumanation 301">
                            </div>
                            <div class="image-actions">
                                <button class="btn-icon btn-danger" onclick="deleteMantImage('alumanation', <?php echo $imgIndex; ?>)" title="Eliminar">
                                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="upload-zone">
                        <form class="upload-form">
                            <label class="upload-label">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>Subir imágenes Alumanation</span>
                                <input type="file" name="images[]" multiple accept="image/*" onchange="uploadMantImages(this, 'alumanation')" hidden>
                            </label>
                        </form>
                    </div>
                </div>

                <!-- Microgoma -->
                <div class="edit-block">
                    <h3>Microgoma</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Título del producto</label>
                            <input type="text" id="micro-title" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['microgoma']['title']); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Etiqueta</label>
                            <input type="text" id="micro-tag" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['microgoma']['tag']); ?>" class="form-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Texto principal</label>
                        <input type="text" id="micro-lead" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['microgoma']['leadText']); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Descripción completa</label>
                        <textarea id="micro-description" class="form-input" rows="4"><?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['microgoma']['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Ventajas (una por línea)</label>
                        <textarea id="micro-advantages" class="form-input" rows="5"><?php echo htmlspecialchars(implode("\n", $content['mantenimiento']['impermeabilizacion']['microgoma']['advantages'])); ?></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="saveMicrogoma()">Guardar Textos Microgoma</button>
                </div>

                <!-- Galería de trabajo -->
                <div class="edit-block">
                    <h3>Galería de Trabajo (Equipo en acción)</h3>
                    <div class="form-group">
                        <label>Título de la galería</label>
                        <input type="text" id="work-gallery-title" value="<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['workGallery']['title']); ?>" class="form-input">
                    </div>
                    <button class="btn btn-primary" onclick="saveWorkGalleryTitle()">Guardar Título</button>

                    <div class="images-grid" id="work-gallery-images">
                        <?php foreach ($content['mantenimiento']['impermeabilizacion']['workGallery']['images'] as $imgIndex => $img): ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <img src="../<?php echo htmlspecialchars($img); ?>" alt="Equipo trabajando">
                            </div>
                            <div class="image-actions">
                                <button class="btn-icon btn-danger" onclick="deleteMantImage('workGallery', <?php echo $imgIndex; ?>)" title="Eliminar">
                                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (!empty($content['mantenimiento']['impermeabilizacion']['workGallery']['video'])): ?>
                        <div class="image-card video-card">
                            <div class="image-preview">
                                <video controls style="width:100%;height:100%;object-fit:cover;">
                                    <source src="../<?php echo htmlspecialchars($content['mantenimiento']['impermeabilizacion']['workGallery']['video']); ?>" type="video/mp4">
                                </video>
                            </div>
                            <div class="image-actions">
                                <span class="video-badge">VIDEO</span>
                                <button class="btn-icon btn-danger" onclick="deleteVideo()" title="Eliminar video">
                                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="upload-zone">
                        <form class="upload-form">
                            <label class="upload-label">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>Subir fotos del equipo</span>
                                <input type="file" name="images[]" multiple accept="image/*" onchange="uploadMantImages(this, 'workGallery')" hidden>
                            </label>
                        </form>
                    </div>
                    <div class="upload-zone">
                        <form class="upload-form">
                            <label class="upload-label">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                                <span>Subir video</span>
                                <input type="file" name="video" accept="video/mp4" onchange="uploadVideo(this)" hidden>
                            </label>
                        </form>
                    </div>
                </div>
            </div>

            <!-- PISOS INDUSTRIALES -->
            <div class="mant-section">
                <h2 class="mant-section-title">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><rect x="1" y="6" width="22" height="12" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Pisos Industriales
                </h2>

                <div class="edit-block">
                    <h3>Textos</h3>
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" id="pisos-title" value="<?php echo htmlspecialchars($content['mantenimiento']['pisos']['title']); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Subtítulo</label>
                        <input type="text" id="pisos-subtitle" value="<?php echo htmlspecialchars($content['mantenimiento']['pisos']['subtitle']); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="pisos-description" class="form-input" rows="3"><?php echo htmlspecialchars($content['mantenimiento']['pisos']['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Servicios (uno por línea)</label>
                        <textarea id="pisos-services" class="form-input" rows="6"><?php echo htmlspecialchars(implode("\n", $content['mantenimiento']['pisos']['services'])); ?></textarea>
                    </div>
                    <button class="btn btn-primary" onclick="savePisos()">Guardar Textos Pisos</button>
                </div>

                <div class="edit-block">
                    <h3>Imágenes Antes/Después</h3>
                    <div class="images-grid" id="pisos-images">
                        <?php foreach ($content['mantenimiento']['pisos']['images'] as $imgIndex => $img): ?>
                        <div class="image-card">
                            <div class="image-preview">
                                <img src="../<?php echo htmlspecialchars($img); ?>" alt="Piso industrial">
                            </div>
                            <div class="image-actions">
                                <button class="btn-icon btn-danger" onclick="deletePisoImage(<?php echo $imgIndex; ?>)" title="Eliminar">
                                    <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="upload-zone">
                        <form class="upload-form">
                            <label class="upload-label">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>Subir imágenes de pisos</span>
                                <input type="file" name="images[]" multiple accept="image/*" onchange="uploadPisoImages(this)" hidden>
                            </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Modal de confirmación -->
    <div id="confirm-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <h3>¿Está seguro?</h3>
            <p id="confirm-message">Esta acción no se puede deshacer.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-danger" id="confirm-btn" onclick="confirmAction()">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
    // ==================== NAVIGATION ====================
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
        });
    });

    // ==================== TOAST ====================
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast toast-' + type + ' show';
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // ==================== CONFIRMATION MODAL ====================
    let pendingAction = null;

    function openModal(message, action) {
        document.getElementById('confirm-message').textContent = message;
        document.getElementById('confirm-modal').style.display = 'flex';
        pendingAction = action;
    }

    function closeModal() {
        document.getElementById('confirm-modal').style.display = 'none';
        pendingAction = null;
    }

    function confirmAction() {
        if (pendingAction) pendingAction();
        closeModal();
    }

    // ==================== API HELPER ====================
    async function apiCall(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        for (const [key, value] of Object.entries(data)) {
            if (value instanceof FileList) {
                for (let i = 0; i < value.length; i++) {
                    formData.append('files[]', value[i]);
                }
            } else {
                formData.append(key, value);
            }
        }
        
        const response = await fetch('api.php', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message || 'Operación exitosa');
        } else {
            showToast(result.message || 'Error en la operación', 'error');
        }
        
        return result;
    }

    // ==================== OBRAS ====================
    async function uploadImages(input, section, categoryIndex) {
        if (!input.files.length) return;
        
        const formData = new FormData();
        formData.append('action', 'upload_obras');
        formData.append('category_index', categoryIndex);
        for (let i = 0; i < input.files.length; i++) {
            formData.append('files[]', input.files[i]);
        }

        const response = await fetch('api.php', { method: 'POST', body: formData });
        const result = await response.json();

        if (result.success) {
            showToast('Imágenes subidas correctamente');
            location.reload();
        } else {
            showToast(result.message || 'Error al subir imágenes', 'error');
        }
    }

    function deleteImage(section, categoryIndex, imageIndex) {
        openModal('¿Eliminar esta imagen?', async () => {
            const result = await apiCall('delete_obras_image', {
                category_index: categoryIndex,
                image_index: imageIndex
            });
            if (result.success) location.reload();
        });
    }

    function deleteCategory(section, categoryIndex) {
        openModal('¿Eliminar esta categoría completa y todas sus imágenes?', async () => {
            const result = await apiCall('delete_obras_category', {
                category_index: categoryIndex
            });
            if (result.success) location.reload();
        });
    }

    function addCategory() {
        const title = prompt('Nombre de la nueva categoría:');
        if (!title) return;
        apiCall('add_obras_category', { title: title }).then(result => {
            if (result.success) location.reload();
        });
    }

    // Save category title on blur
    document.querySelectorAll('.category-title-input').forEach(input => {
        input.addEventListener('change', async function() {
            await apiCall('update_category_title', {
                category_index: this.dataset.index,
                title: this.value
            });
        });
    });

    // ==================== MANTENIMIENTO - IMPERMEABILIZACIÓN ====================
    async function saveTexts(section) {
        await apiCall('save_imp_texts', {
            title: document.getElementById('imp-title').value,
            subtitle: document.getElementById('imp-subtitle').value,
            description: document.getElementById('imp-description').value
        });
    }

    async function saveAlumanation() {
        await apiCall('save_alumanation', {
            title: document.getElementById('alum-title').value,
            tag: document.getElementById('alum-tag').value
        });
    }

    async function saveMicrogoma() {
        await apiCall('save_microgoma', {
            title: document.getElementById('micro-title').value,
            tag: document.getElementById('micro-tag').value,
            leadText: document.getElementById('micro-lead').value,
            description: document.getElementById('micro-description').value,
            advantages: document.getElementById('micro-advantages').value
        });
    }

    async function saveWorkGalleryTitle() {
        await apiCall('save_work_gallery_title', {
            title: document.getElementById('work-gallery-title').value
        });
    }

    async function uploadMantImages(input, subsection) {
        if (!input.files.length) return;
        const formData = new FormData();
        formData.append('action', 'upload_mant_images');
        formData.append('subsection', subsection);
        for (let i = 0; i < input.files.length; i++) {
            formData.append('files[]', input.files[i]);
        }
        const response = await fetch('api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showToast('Imágenes subidas correctamente');
            location.reload();
        } else {
            showToast(result.message || 'Error al subir', 'error');
        }
    }

    function deleteMantImage(subsection, imageIndex) {
        openModal('¿Eliminar esta imagen?', async () => {
            const result = await apiCall('delete_mant_image', {
                subsection: subsection,
                image_index: imageIndex
            });
            if (result.success) location.reload();
        });
    }

    async function uploadVideo(input) {
        if (!input.files.length) return;
        const formData = new FormData();
        formData.append('action', 'upload_video');
        formData.append('file', input.files[0]);
        const response = await fetch('api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showToast('Video subido correctamente');
            location.reload();
        } else {
            showToast(result.message || 'Error al subir video', 'error');
        }
    }

    function deleteVideo() {
        openModal('¿Eliminar el video?', async () => {
            const result = await apiCall('delete_video');
            if (result.success) location.reload();
        });
    }

    // ==================== MANTENIMIENTO - PISOS ====================
    async function savePisos() {
        await apiCall('save_pisos', {
            title: document.getElementById('pisos-title').value,
            subtitle: document.getElementById('pisos-subtitle').value,
            description: document.getElementById('pisos-description').value,
            services: document.getElementById('pisos-services').value
        });
    }

    async function uploadPisoImages(input) {
        if (!input.files.length) return;
        const formData = new FormData();
        formData.append('action', 'upload_piso_images');
        for (let i = 0; i < input.files.length; i++) {
            formData.append('files[]', input.files[i]);
        }
        const response = await fetch('api.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) {
            showToast('Imágenes subidas correctamente');
            location.reload();
        } else {
            showToast(result.message || 'Error al subir', 'error');
        }
    }

    function deletePisoImage(imageIndex) {
        openModal('¿Eliminar esta imagen?', async () => {
            const result = await apiCall('delete_piso_image', {
                image_index: imageIndex
            });
            if (result.success) location.reload();
        });
    }
    </script>
</body>
</html>
