/**
 * ResourceHub - Lógica del Catálogo Público
 */

$(document).ready(function() {
    let todosLosRecursos = [];
    let filtroActual = 'todos';

    // Cargar recursos al iniciar
    cargarRecursos();
    cargarEstadisticas();

    // Búsqueda
    $('#btn-search, #search-input').on('click keypress', function(e) {
        if (e.type === 'click' || e.which === 13) {
            const busqueda = $('#search-input').val().trim();
            if (busqueda) {
                buscarRecursos(busqueda);
            } else {
                cargarRecursos();
            }
        }
    });

    // Filtros
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        filtroActual = $(this).data('filter');
        filtrarRecursos(filtroActual);
    });

    function cargarRecursos() {
        $.ajax({
            url: 'backend/resource-list.php',
            type: 'GET',
            success: function(recursos) {
                todosLosRecursos = recursos;
                mostrarRecursos(recursos);
            },
            error: function() {
                $('#resources-container').html(`
                    <div class="col-12 no-resources">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h4>Error al cargar los recursos</h4>
                        <p>Por favor, intenta nuevamente más tarde</p>
                    </div>
                `);
            }
        });
    }

    function buscarRecursos(termino) {
        $.ajax({
            url: 'backend/resource-search.php',
            type: 'GET',
            data: { search: termino },
            success: function(recursos) {
                mostrarRecursos(recursos);
            }
        });
    }

    function filtrarRecursos(tipo) {
        if (tipo === 'todos') {
            mostrarRecursos(todosLosRecursos);
        } else {
            const filtrados = todosLosRecursos.filter(r => r.tipo_recurso === tipo);
            mostrarRecursos(filtrados);
        }
    }

    function mostrarRecursos(recursos) {
        const container = $('#resources-container');
        
        if (!recursos || recursos.length === 0) {
            container.html(`
                <div class="col-12 no-resources">
                    <i class="fas fa-folder-open"></i>
                    <h4>No hay recursos disponibles</h4>
                    <p>Intenta con otra búsqueda o filtro</p>
                </div>
            `);
            return;
        }

        let html = '';
        recursos.forEach(recurso => {
            const icono = obtenerIcono(recurso.tipo_recurso);
            const badgeClass = `badge-${recurso.tipo_recurso}`;
            const tamanio = formatearTamanio(recurso.archivo_tamanio);
            
            html += `
                <div class="col-md-6">
                    <div class="resource-card">
                        <div class="resource-icon">
                            <i class="${icono}"></i>
                        </div>
                        <h3 class="resource-title">${recurso.titulo}</h3>
                        <p class="resource-description">${recurso.descripcion || 'Sin descripción'}</p>
                        
                        <div class="resource-meta">
                            <span><i class="fas fa-calendar"></i> ${formatearFecha(recurso.fecha_subida)}</span>
                            ${recurso.lenguaje ? `<span><i class="fas fa-code"></i> ${recurso.lenguaje}</span>` : ''}
                            <span><i class="fas fa-file"></i> ${tamanio}</span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="badge badge-custom ${badgeClass}">
                                ${recurso.tipo_recurso}
                            </span>
                            ${recurso.tags ? recurso.tags.split(',').map(tag => 
                                `<span class="badge badge-secondary">${tag.trim()}</span>`
                            ).join(' ') : ''}
                        </div>
                        
                        <button class="btn btn-download" onclick="descargarRecurso(${recurso.id})">
                            <i class="fas fa-download"></i> Descargar
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    }

    function cargarEstadisticas() {
        $.ajax({
            url: 'backend/resource-stats.php',
            type: 'GET',
            success: function(stats) {
                $('#stat-total').text(stats.total_recursos || 0);
                $('#stat-tipos').text(Object.keys(stats.por_tipo || {}).length);
                $('#stat-lenguajes').text(Object.keys(stats.por_lenguaje || {}).length);
            }
        });
    }

    function obtenerIcono(tipo) {
        const iconos = {
            'codigo': 'fas fa-code',
            'documentacion': 'fas fa-book',
            'biblioteca': 'fas fa-box',
            'herramienta': 'fas fa-tools',
            'tutorial': 'fas fa-graduation-cap',
            'otro': 'fas fa-file'
        };
        return iconos[tipo] || 'fas fa-file';
    }

    function formatearFecha(fecha) {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    function formatearTamanio(bytes) {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Hacer global la función de descarga
    window.descargarRecurso = function(id) {
        window.location.href = `backend/resource-download.php?id=${id}`;
    };
});