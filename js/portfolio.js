// js/portfolio.js
// Este script maneja la interactividad de la página principal del portfolio,
// incluyendo la funcionalidad de "me gusta", la visualización de detalles del proyecto
// en un modal, la eliminación de proyectos, y la edición/subida de proyectos.

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM completamente cargado. Inicializando portfolio.js');

    // --- Variables globales para el formulario de subida/edición ---
    const uploadForm = document.querySelector('#uploadFormContainer form'); // El formulario de subida/edición.
    let tituloInput, descripcionTextarea, programasCheckboxes, submitButton, hiddenIdInput, fileInput;

    // Inicializa elementos que no dependen directamente de uploadForm, pero que son usados en la lógica de edición/subida.
    const currentImageContainer = document.getElementById('currentImageContainer'); // Contenedor de la imagen actual.
    const currentProjectImage = document.getElementById('currentProjectImage'); // Elemento <img> de la imagen actual.

    if (uploadForm) {
        tituloInput = uploadForm.querySelector('input[name="titulo"]'); // Campo de título.
        descripcionTextarea = uploadForm.querySelector('textarea[name="descripcion"]'); // Campo de descripción.
        programasCheckboxes = uploadForm.querySelectorAll('input[name="programas_usados[]"]'); // Checkboxes de programas.
        submitButton = uploadForm.querySelector('button[type="submit"]'); // Botón de envío del formulario.
        hiddenIdInput = uploadForm.querySelector('input[name="trabajo_id"]'); // Campo oculto para el ID del trabajo.
        fileInput = uploadForm.querySelector('input[name="archivo"]'); // Campo de selección de archivo.

        // Si el campo oculto para el ID no existe, lo crea y lo añade al formulario.
        if (!hiddenIdInput) {
            hiddenIdInput = document.createElement('input');
            hiddenIdInput.type = 'hidden';
            hiddenIdInput.name = 'trabajo_id';
            uploadForm.appendChild(hiddenIdInput);
        }

        // --- Lógica para manejar el envío del formulario de subida/edición (AJAX) ---
        uploadForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Previene el envío normal del formulario (que recargaría la página).

            const formData = new FormData(uploadForm); // Crea un objeto FormData con los datos del formulario.
            const trabajoId = hiddenIdInput.value; // Obtiene el ID del trabajo (vacío para nuevas subidas).

            let url = '/api/index.php/trabajos'; // URL para crear un nuevo trabajo.
            // let url = '/CREATINET/api/trabajos'; // URL por defecto para crear un nuevo trabajo.
            let method = 'POST'; // Método por defecto para crear.
            let headers = {}; // Cabeceras adicionales.

            if (trabajoId) {
                // Si hay un ID de trabajo, es una actualización.
                url = `/api/index.php/trabajos/${trabajoId}`; // La URL incluye el ID.
                // url = `/CREATINET/api/trabajos/${trabajoId}`; // La URL incluye el ID.
                // Para simular PUT con FormData, usamos POST y el encabezado X-HTTP-Method-Override.
                headers['X-HTTP-Method-Override'] = 'PUT';
            }

            // Realiza la solicitud fetch a la API.
            fetch(url, {
                method: method, // Método HTTP (POST).
                headers: headers, // Cabeceras adicionales (incluyendo X-HTTP-Method-Override si es actualización).
                body: formData // El cuerpo de la solicitud es el FormData.
            })
            .then(response => {
                if (response.ok) { // Si la respuesta HTTP es exitosa (2xx).
                    return response.json(); // Parsea la respuesta como JSON.
                } else {
                    // Si la respuesta no es OK, intenta leer el error del cuerpo de la respuesta.
                    return response.json().then(err => {
                        throw new Error(err.error || `HTTP ${response.status}: ${response.statusText}`);
                    });
                }
            })
            .then(data => {
                if (data.success) { // Si la operación fue exitosa según la API.
                    alert('Proyecto guardado con éxito!'); // Muestra un mensaje de éxito.
                    document.getElementById('uploadFormContainer').style.display = 'none'; // Oculta el formulario.
                    uploadForm.reset(); // Limpia los campos del formulario.
                    currentImageContainer.style.display = 'none'; // Oculta la imagen actual.
                    fileInput.setAttribute('required', 'required'); // Vuelve a hacer el campo de archivo requerido.
                    window.location.reload(); // Recarga la página para mostrar los cambios.
                } else {
                    alert('Error al guardar el proyecto: ' + (data.error || 'Error desconocido')); // Muestra un mensaje de error.
                }
            })
            .catch(error => {
                console.error('Error:', error); // Registra el error en la consola.
                alert('Error de conexión al guardar el proyecto: ' + error.message); // Muestra una alerta de error de conexión.
            });
        });
    }


    // --- Lógica para los botones de "Me gusta" ---
    const likeButtons = document.querySelectorAll('.like-btn'); // Selecciona todos los botones con la clase 'like-btn'.

    likeButtons.forEach(button => {
        // Añade un 'event listener' a cada botón de "me gusta".
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Evita que el clic se propague a la tarjeta del proyecto (que también tiene un listener).
            const trabajoId = this.dataset.id; // Obtiene el ID del trabajo desde el atributo 'data-id'.
            const likesCountSpan = this.nextElementSibling; // Obtiene el elemento span que muestra el contador de likes.

            // Realiza una solicitud POST a la API para registrar/quitar un "me gusta".
            // fetch('/CREATINET/api/like', {
            fetch('/api/like', {
                method: 'POST', // Método HTTP POST.
                headers: {
                    'Content-Type': 'application/json' // Indica que el cuerpo de la solicitud es JSON.
                },
                body: JSON.stringify({ id_trabajo: trabajoId }) // Envía el ID del trabajo en formato JSON.
            })
            .then(response => response.json()) // Parsea la respuesta como JSON.
            .then(data => {
                if (data.success) { // Si la operación fue exitosa.
                    let currentLikes = parseInt(likesCountSpan.textContent); // Obtiene el número actual de likes.
                    likesCountSpan.textContent = currentLikes + data.resultado; // Actualiza el contador de likes.
                }
            })
            .catch(error => {
                console.error('Error al dar/quitar like:', error); // Registra cualquier error en la consola.
                alert('Hubo un error al procesar tu like.'); // Muestra una alerta al usuario.
            });
        });
    });

    // --- Lógica para el modal de visualización de proyectos ---
    const projectCards = document.querySelectorAll('.trabajo-card'); // Selecciona todas las tarjetas de proyecto.
    const modal = document.getElementById('projectModal'); // El elemento modal.
    const modalImage = document.getElementById('modalImage'); // La imagen dentro del modal.
    const modalTitle = document.getElementById('modalTitle'); // El título dentro del modal.
    const modalDescription = document.getElementById('modalDescription'); // La descripción dentro del modal.
    const closeBtn = document.querySelector('.close-btn'); // El botón para cerrar el modal.

    projectCards.forEach(card => {
        // Añade un 'event listener' a cada tarjeta de proyecto para abrir el modal.
        card.addEventListener('click', function() {
            // Obtiene la información del proyecto de la tarjeta.
            const image = card.querySelector('img').src;
            const title = card.querySelector('h3').textContent;
            const description = card.querySelector('p').textContent; // Obtiene la descripción visible.

            // Rellena el contenido del modal con la información del proyecto.
            modalImage.src = 'img/trabajos/' + image.split('/').pop();
            modalTitle.textContent = title;
            modalDescription.textContent = description;

            modal.style.display = 'block'; // Muestra el modal.
        });
    });

    // Cierra el modal cuando se hace clic en el botón de cerrar.
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none'; // Oculta el modal.
    });

    // Cierra el modal cuando se hace clic fuera del contenido del modal.
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none'; // Oculta el modal.
        }
    });

    // --- Lógica para eliminar proyectos ---
    const deleteButtons = document.querySelectorAll('.delete-btn'); // Selecciona todos los botones de eliminar.
    deleteButtons.forEach(button => {
        // Añade un 'event listener' a cada botón de eliminar.
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Evita la propagación del clic.
            const trabajoId = this.dataset.id; // Obtiene el ID del trabajo a eliminar.
            if (confirm('¿Estás seguro de que quieres eliminar este proyecto?')) { // Pide confirmación al usuario.
                // Realiza una solicitud fetch para eliminar el proyecto.
                // fetch(`/CREATINET/api/trabajos/${trabajoId}`, {
                                fetch(`/api/index.php/trabajos/${trabajoId}`, {
                    method: 'DELETE' // Se utiliza el método DELETE directamente.
                })
                .then(response => {
                    if (response.ok) { // Si la respuesta HTTP es exitosa (2xx).
                        return response.json(); // Parsea la respuesta como JSON.
                    } else {
                        // Si la respuesta no es OK, intenta leer el error del cuerpo de la respuesta.
                        return response.json().then(err => {
                            throw new Error(err.error || `HTTP ${response.status}: ${response.statusText}`);
                        });
                    }
                })
                .then(data => {
                    if (data.success) { // Si la operación de eliminación fue exitosa según la API.
                        alert('Proyecto eliminado con éxito.'); // Muestra un mensaje de éxito.
                        const card = this.closest('.trabajo-card'); // Encuentra la tarjeta del proyecto.
                        if (card) {
                            card.remove(); // Elimina la tarjeta del DOM.
                        } else {
                            window.location.reload(); // Si no se encuentra la tarjeta, recarga la página.
                        }
                    } else {
                        alert('Error al eliminar el proyecto: ' + (data.error || 'Error desconocido')); // Muestra un mensaje de error.
                    }
                })
                .catch(error => {
                    console.error('Error:', error); // Registra el error en la consola.
                    alert('Error de conexión al eliminar el proyecto: ' + error.message); // Muestra una alerta de error de conexión.
                });
            }
        });
    });

    // --- Lógica para editar proyectos ---
    const editButtons = document.querySelectorAll('.edit-btn'); // Selecciona todos los botones de editar.

    editButtons.forEach(button => {
        // Añade un 'event listener' a cada botón de editar.
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Evita la propagación del clic.
            // Obtiene los datos del proyecto desde los atributos 'data-' del botón.
            const trabajoId = this.dataset.id;
            const titulo = this.dataset.titulo;
            const descripcion = this.dataset.descripcion;
            const programas = this.dataset.programas ? this.dataset.programas.split(', ') : [];
            const archivo = this.dataset.archivo; // Obtener la URL del archivo actual.

            // Rellena el formulario con los datos del proyecto.
            if (tituloInput) tituloInput.value = titulo;
            if (descripcionTextarea) descripcionTextarea.value = descripcion;
            if (hiddenIdInput) hiddenIdInput.value = trabajoId; // Establece el ID del trabajo en el campo oculto.

            // Muestra la imagen actual si existe y ajusta el campo de archivo.
            if (archivo && currentProjectImage && currentImageContainer && fileInput) {
                currentProjectImage.src = 'img/trabajos/' + archivo; // Construye la ruta completa de la imagen.
                currentImageContainer.style.display = 'block'; // Muestra el contenedor de la imagen.
                fileInput.removeAttribute('required'); // El campo de archivo no es requerido si ya hay una imagen.
            } else if (currentImageContainer && fileInput) {
                currentImageContainer.style.display = 'none'; // Oculta el contenedor de la imagen.
                fileInput.setAttribute('required', 'required'); // El campo de archivo es requerido si no hay imagen.
            }

            // Desmarca todos los checkboxes de programas.
            if (programasCheckboxes) {
                programasCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            }

            // Marca los checkboxes de los programas usados en el proyecto.
            if (programas && programasCheckboxes) {
                programas.forEach(programa => {
                    programasCheckboxes.forEach(checkbox => {
                        if (checkbox.value === programa) {
                            checkbox.checked = true;
                        }
                    });
                });
            }

            // Cambia el texto del botón de envío a "Actualizar Proyecto".
            if (submitButton) submitButton.textContent = 'Actualizar Proyecto';
            // Establece la acción del formulario para apuntar a la API de actualización.
            // if (uploadForm) uploadForm.action = `/CREATINET/api/trabajos/${trabajoId}`;
            if (uploadForm) uploadForm.action = `/api/index.php/${trabajoId}`;
            if (uploadForm) uploadForm.method = 'POST'; // Usa POST para enviar el formulario (la API lo manejará como PUT).

            // Mostrar el formulario de subida/edición
            const uploadFormContainer = document.getElementById('uploadFormContainer');
            if (uploadFormContainer) {
                uploadFormContainer.style.display = 'block';
                // Desplaza la vista al formulario de edición con una animación suave.
                uploadFormContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // --- Lógica para el botón "Subir Nuevo Proyecto" (para resetear el formulario) ---
    document.getElementById('toggleUploadForm').addEventListener('click', function() {
        console.log('Botón "Subir Nuevo Proyecto" clickeado.');
        const formContainer = document.getElementById('uploadFormContainer');
        if (formContainer && formContainer.style.display === 'none') {
            formContainer.style.display = 'block'; // Muestra el formulario.
            // Resetea el formulario cuando se abre para una nueva subida.
            if (uploadForm) uploadForm.reset(); // Limpia los campos.
            if (hiddenIdInput) hiddenIdInput.value = ''; // Limpia el ID del trabajo (para indicar nueva subida).
            if (submitButton) submitButton.textContent = 'Subir'; // Restaura el texto del botón.
            // if (uploadForm) uploadForm.action = '/CREATINET/api/trabajos'; // Restaura la acción del formulario.
            if (uploadForm) uploadForm.action = '/api/index.php'; // Restaura la acción del formulario.
            if (uploadForm) uploadForm.method = 'POST'; // Restaura el método del formulario.
            if (currentImageContainer) currentImageContainer.style.display = 'none'; // Oculta la imagen actual.
            if (fileInput) fileInput.setAttribute('required', 'required'); // Hace el campo de archivo requerido para nuevas subidas.
        } else if (formContainer) {
            formContainer.style.display = 'none'; // Oculta el formulario si ya está visible.
        }
    });
});
