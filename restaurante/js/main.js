// Filtrado de mesas
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const mesaCards = document.querySelectorAll('.mesa-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Filter mesa cards
            mesaCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-estado') === filter) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
    
    // Modal functionality
    const addMesaBtn = document.getElementById('addMesaBtn');
    const addMesaModal = document.getElementById('addMesaModal');
    const editMesaModal = document.getElementById('editMesaModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    
    if (addMesaBtn && addMesaModal) {
        addMesaBtn.addEventListener('click', function() {
            addMesaModal.classList.add('active');
            // Limpiar el formulario
            const form = addMesaModal.querySelector('form');
            if (form) form.reset();
        });
    }
    
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = btn.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });
    
    // Close modal when clicking outside
    if (addMesaModal) {
        addMesaModal.addEventListener('click', function(e) {
            if (e.target === addMesaModal) {
                addMesaModal.classList.remove('active');
            }
        });
    }
    
    if (editMesaModal) {
        editMesaModal.addEventListener('click', function(e) {
            if (e.target === editMesaModal) {
                editMesaModal.classList.remove('active');
            }
        });
    }
    
    // Manejar el submit del formulario de agregar mesa
    const addMesaForm = document.querySelector('#addMesaModal form');
    if (addMesaForm) {
        addMesaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const datos = {
                numero_mesa: formData.get('numero_mesa'),
                capacidad: parseInt(formData.get('capacidad')),
                ubicacion: formData.get('ubicacion')
            };
            
            // Validar que los campos no estén vacíos
            if (!datos.numero_mesa || !datos.capacidad || !datos.ubicacion) {
                if (window.restaurantApp) {
                    window.restaurantApp.showNotification('Por favor completa todos los campos', 'error');
                }
                return;
            }
            
            // URL correcta para la API
            const apiUrl = 'api/mesas.php';
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            
            // Enviar POST vía AJAX
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || `Error ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (window.restaurantApp) {
                        window.restaurantApp.showNotification('Mesa agregada exitosamente', 'success');
                    }
                    addMesaModal.classList.remove('active');
                    // Recargar la página para mostrar la nueva mesa
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (window.restaurantApp) {
                        window.restaurantApp.showNotification('Error al agregar mesa: ' + (data.error || 'Error desconocido'), 'error');
                    }
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.restaurantApp) {
                    window.restaurantApp.showNotification('Error: ' + error.message, 'error');
                }
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Manejar el submit del formulario de editar mesa
    const editMesaForm = document.getElementById('editMesaForm');
    if (editMesaForm) {
        editMesaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const mesaId = editMesaModal.getAttribute('data-mesa-id');
            if (!mesaId) {
                if (window.restaurantApp) {
                    window.restaurantApp.showNotification('Error: ID de mesa no encontrado', 'error');
                }
                return;
            }
            
            const formData = new FormData(this);
            const datos = {
                numero_mesa: formData.get('numero_mesa'),
                capacidad: parseInt(formData.get('capacidad')),
                ubicacion: formData.get('ubicacion')
            };
            
            // Validar que los campos no estén vacíos
            if (!datos.numero_mesa || !datos.capacidad || !datos.ubicacion) {
                if (window.restaurantApp) {
                    window.restaurantApp.showNotification('Por favor completa todos los campos', 'error');
                }
                return;
            }
            
            // URL correcta para la API
            const apiUrl = `api/mesas.php/${mesaId}`;
            
            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            submitBtn.disabled = true;
            
            // Enviar PUT vía AJAX
            fetch(apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || `Error ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (window.restaurantApp) {
                        window.restaurantApp.showNotification('Mesa actualizada exitosamente', 'success');
                    }
                    editMesaModal.classList.remove('active');
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (window.restaurantApp) {
                        window.restaurantApp.showNotification('Error al actualizar mesa: ' + (data.error || 'Error desconocido'), 'error');
                    }
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.restaurantApp) {
                    window.restaurantApp.showNotification('Error: ' + error.message, 'error');
                }
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

function editarMesa(idMesa) {
    // Encontrar el select de la mesa
    const selectElement = document.querySelector(`.estado-select[data-mesa-id="${idMesa}"]`);
    if (!selectElement) {
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Mesa no encontrada', 'error');
        }
        return;
    }
    
    // Encontrar la tarjeta de la mesa
    const mesaCard = selectElement.closest('.mesa-card');
    if (!mesaCard) {
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Mesa no encontrada', 'error');
        }
        return;
    }
    
    // Obtener datos de la mesa
    const numeroMesa = mesaCard.querySelector('.mesa-numero')?.textContent.replace('Mesa ', '') || '';
    const capacidadText = mesaCard.querySelector('.mesa-capacidad')?.textContent || '';
    const capacidad = capacidadText.match(/\d+/)?.[0] || '4';
    const ubicacion = mesaCard.querySelector('.mesa-ubicacion')?.textContent || 'Sala Principal';
    
    // Abrir modal con los datos
    const modal = document.getElementById('editMesaModal');
    if (!modal) {
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Modal de edición no encontrado', 'error');
        }
        return;
    }
    
    const numeroInput = modal.querySelector('input[name="numero_mesa"]');
    const capacidadInput = modal.querySelector('input[name="capacidad"]');
    const ubicacionSelect = modal.querySelector('select[name="ubicacion"]');
    
    if (numeroInput) numeroInput.value = numeroMesa;
    if (capacidadInput) capacidadInput.value = capacidad;
    if (ubicacionSelect) ubicacionSelect.value = ubicacion;
    
    modal.setAttribute('data-mesa-id', idMesa);
    modal.classList.add('active');
}

function verDetallesMesa(idMesa) {
    // Encontrar el select de la mesa
    const selectElement = document.querySelector(`.estado-select[data-mesa-id="${idMesa}"]`);
    if (!selectElement) {
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Mesa no encontrada', 'error');
        }
        return;
    }
    
    // Encontrar la tarjeta de la mesa
    const mesaCard = selectElement.closest('.mesa-card');
    if (!mesaCard) {
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Mesa no encontrada', 'error');
        }
        return;
    }
    
    // Obtener datos de la mesa
    const numeroMesa = mesaCard.querySelector('.mesa-numero')?.textContent || '';
    const capacidad = mesaCard.querySelector('.mesa-capacidad')?.textContent || '';
    const ubicacion = mesaCard.querySelector('.mesa-ubicacion')?.textContent || '';
    const estado = mesaCard.querySelector('.mesa-estado')?.textContent || '';
    
    // Mostrar detalles en notificación
    const mensaje = `
    <strong>Detalles de la Mesa:</strong><br>
    ${numeroMesa}<br>
    ${capacidad}<br>
    ${ubicacion}<br>
    Estado: ${estado}
    `;
    
    if (window.restaurantApp) {
        window.restaurantApp.showNotification(mensaje, 'info');
    }
}

function eliminarMesa(idMesa) {
    // Confirmar eliminación
    if (!confirm('¿Está seguro que desea eliminar esta mesa? Esta acción no puede ser revertida.')) {
        return;
    }
    
    // URL correcta para la API
    const apiUrl = `api/mesas.php/${idMesa}`;
    
    // Enviar eliminación vía AJAX
    fetch(apiUrl, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || `Error ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (window.restaurantApp) {
                window.restaurantApp.showNotification('Mesa eliminada exitosamente', 'success');
            }
            // Remover la tarjeta de la mesa del DOM
            const selectElement = document.querySelector(`.estado-select[data-mesa-id="${idMesa}"]`);
            if (selectElement) {
                const mesaCard = selectElement.closest('.mesa-card');
                if (mesaCard) {
                    mesaCard.style.animation = 'fadeOut 0.3s ease-out forwards';
                    setTimeout(() => {
                        mesaCard.remove();
                    }, 300);
                }
            }
        } else {
            if (window.restaurantApp) {
                window.restaurantApp.showNotification('Error al eliminar mesa: ' + (data.error || 'Error desconocido'), 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Error: ' + error.message, 'error');
        }
    });
}

function cambiarEstadoMesa(selectElement) {
    const mesaId = selectElement.getAttribute('data-mesa-id');
    const nuevoEstado = selectElement.value;
    
    if (!mesaId || !nuevoEstado) {
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Error: Datos incompletos', 'error');
        }
        return;
    }
    
    // Guardar el valor anterior para revertir en caso de error
    const oldValue = selectElement.getAttribute('data-old-value') || selectElement.value;
    selectElement.setAttribute('data-old-value', oldValue);
    
    // URL correcta para la API
    const apiUrl = `api/mesas.php/${mesaId}`;
    
    // Enviar cambio vía AJAX
    fetch(apiUrl, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            estado: nuevoEstado
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || `Error ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (window.restaurantApp) {
                window.restaurantApp.showNotification('Estado de mesa actualizado', 'success');
            }
            // Actualizar la clase de la tarjeta
            const mesaCard = selectElement.closest('.mesa-card');
            if (mesaCard) {
                // Remover todas las clases de estado
                mesaCard.classList.remove('disponible', 'ocupada', 'reservada', 'mantenimiento');
                // Agregar la nueva clase
                mesaCard.classList.add(nuevoEstado);
                mesaCard.setAttribute('data-estado', nuevoEstado);
                
                // Actualizar el texto del estado
                const estadoElement = mesaCard.querySelector('.mesa-estado');
                if (estadoElement) {
                    estadoElement.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
                }
            }
        } else {
            if (window.restaurantApp) {
                window.restaurantApp.showNotification('Error al actualizar estado: ' + (data.error || 'Error desconocido'), 'error');
            }
            // Revertir el select al valor anterior
            selectElement.value = oldValue;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.restaurantApp) {
            window.restaurantApp.showNotification('Error: ' + error.message, 'error');
        }
        // Revertir el select al valor anterior
        selectElement.value = oldValue;
    });
}
// Función para cargar productos
function cargarProductos() {
    $.ajax({
        url: 'includes/actions.php',
        type: 'GET',
        data: { action: 'obtener_productos' },
        success: function(response) {
            let data = JSON.parse(response);
            if (data.success) {
                mostrarProductos(data.data);
            } else {
                console.error('Error:', data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar productos:', error);
        }
    });
}

// Función para cargar categorías
function cargarCategorias() {
    $.ajax({
        url: 'includes/actions.php',
        type: 'GET',
        data: { action: 'obtener_categorias' },
        success: function(response) {
            let data = JSON.parse(response);
            if (data.success) {
                mostrarCategorias(data.data);
            } else {
                console.error('Error:', data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar categorías:', error);
        }
    });
}

// Llamar estas funciones cuando se cargue la página
$(document).ready(function() {
    cargarProductos();
    cargarCategorias();
});
// Función para cargar y mostrar categorías
function cargarCategorias() {
    $.ajax({
        url: 'includes/actions.php',
        type: 'GET',
        data: { action: 'obtener_categorias' },
        success: function(response) {
            try {
                let data = JSON.parse(response);
                if (data.success && data.data) {
                    mostrarCategorias(data.data);
                } else {
                    console.error('Error al cargar categorías:', data.message);
                    mostrarCategoriasPorDefecto();
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
                mostrarCategoriasPorDefecto();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX al cargar categorías:', error);
            mostrarCategoriasPorDefecto();
        }
    });
}

// Función para mostrar las categorías en la página
function mostrarCategorias(categorias) {
    console.log('Categorías cargadas:', categorias); // Para debug
    
    // Limpiar el contenedor de categorías existente
    $('.categorias-container').empty();
    
    if (categorias.length === 0) {
        $('.categorias-container').html('<p>No hay categorías disponibles</p>');
        return;
    }
    
    // Crear HTML para cada categoría
    categorias.forEach(function(categoria) {
        let categoriaHTML = `
            <div class="categoria-item" data-categoria-id="${categoria.id_categoria}">
                <h3 class="categoria-titulo">${categoria.nombre}</h3>
                <div class="productos-grid" id="productos-${categoria.id_categoria}">
                    <!-- Los productos se cargarán aquí -->
                </div>
            </div>
        `;
        $('.categorias-container').append(categoriaHTML);
        
        // Cargar productos para esta categoría
        cargarProductosPorCategoria(categoria.id_categoria);
    });
}

// Función para cargar productos por categoría
function cargarProductosPorCategoria(idCategoria) {
    $.ajax({
        url: 'includes/actions.php',
        type: 'GET',
        data: { 
            action: 'obtener_productos_por_categoria',
            id_categoria: idCategoria
        },
        success: function(response) {
            try {
                let data = JSON.parse(response);
                if (data.success && data.data) {
                    mostrarProductosEnCategoria(idCategoria, data.data);
                }
            } catch (e) {
                console.error('Error parsing productos:', e);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar productos:', error);
        }
    });
}

// Función para mostrar productos en su categoría
function mostrarProductosEnCategoria(idCategoria, productos) {
    const container = $(`#productos-${idCategoria}`);
    container.empty();
    
    if (productos.length === 0) {
        container.html('<p class="no-productos">No hay productos en esta categoría</p>');
        return;
    }
    
    productos.forEach(function(producto) {
        let productoHTML = `
            <div class="producto-card" data-producto-id="${producto.id_producto}">
                ${producto.imagen ? 
                    `<img src="${producto.imagen}" alt="${producto.nombre}" class="producto-imagen">` : 
                    '<div class="producto-sin-imagen">Sin imagen</div>'
                }
                <div class="producto-info">
                    <h4 class="producto-nombre">${producto.nombre}</h4>
                    <p class="producto-descripcion">${producto.descripcion || 'Sin descripción'}</p>
                    <div class="producto-precio">S/.${parseFloat(producto.precio).toFixed(2)}</div>
                    <div class="producto-tiempo">${producto.tiempo_preparacion || '0'} MIN</div>
                </div>
            </div>
        `;
        container.append(productoHTML);
    });
}

// Categorías por defecto en caso de error
function mostrarCategoriasPorDefecto() {
    const categoriasDefault = [
        { id_categoria: 1, nombre: 'Entradas' },
        { id_categoria: 2, nombre: 'Platos Fuertes' },
        { id_categoria: 3, nombre: 'Postres' },
        { id_categoria: 4, nombre: 'Bebidas' }
    ];
    mostrarCategorias(categoriasDefault);
}

// Cargar categorías cuando la página esté lista
$(document).ready(function() {
    cargarCategorias();
    
    // Recargar categorías después de agregar una nueva
    $(document).on('categoriaAgregada', function() {
        cargarCategorias();
    });
});