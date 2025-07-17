document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.like-btn');

    likeButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Evita que el clic se propague a la tarjeta
            const trabajoId = this.dataset.id;
            const likesCountSpan = this.nextElementSibling;

            fetch('../api/like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id_trabajo: trabajoId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let currentLikes = parseInt(likesCountSpan.textContent);
                    likesCountSpan.textContent = currentLikes + data.resultado;
                }
            });
        });
    });

    const projectCards = document.querySelectorAll('.trabajo-card');
    const modal = document.getElementById('projectModal');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const closeBtn = document.querySelector('.close-btn');

    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const image = card.querySelector('img').src;
            const title = card.querySelector('h3').textContent;
            const description = card.querySelector('p').textContent;

            modalImage.src = image;
            modalTitle.textContent = title;
            modalDescription.textContent = description;

            modal.style.display = 'block';
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });

    // Lógica para eliminar proyectos - CORREGIDA
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation();
            const trabajoId = this.dataset.id;
            if (confirm('¿Estás seguro de que quieres eliminar este proyecto?')) {
                fetch(`/CREATINET/api/trabajos/${trabajoId}`, {
                    method: 'POST', // Se envía como POST
                    headers: {
                        'X-HTTP-Method-Override': 'DELETE' // Se simula el método DELETE
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                })
                .then(data => {
                    if (data.success) {
                        alert('Proyecto eliminado con éxito.');
                        // Remover el elemento del DOM
                        const card = this.closest('.trabajo-card');
                        if (card) {
                            card.remove();
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Error al eliminar el proyecto: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al eliminar el proyecto: ' + error.message);
                });
            }
        });
    });

    // Lógica para editar proyectos
    const editButtons = document.querySelectorAll('.edit-btn');
    const uploadForm = document.querySelector('#uploadFormContainer form');
    const tituloInput = uploadForm.querySelector('input[name="titulo"]');
    const descripcionTextarea = uploadForm.querySelector('textarea[name="descripcion"]');
    const programasCheckboxes = uploadForm.querySelectorAll('input[name="programas_usados[]"]');
    const submitButton = uploadForm.querySelector('button[type="submit"]');
    let hiddenIdInput = uploadForm.querySelector('input[name="trabajo_id"]');

    if (!hiddenIdInput) {
        hiddenIdInput = document.createElement('input');
        hiddenIdInput.type = 'hidden';
        hiddenIdInput.name = 'trabajo_id';
        uploadForm.appendChild(hiddenIdInput);
    }

    editButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation();
            const trabajoId = this.dataset.id;
            const titulo = this.dataset.titulo;
            const descripcion = this.dataset.descripcion;
            const programas = this.dataset.programas ? this.dataset.programas.split(', ') : [];

            // Rellenar el formulario
            tituloInput.value = titulo;
            descripcionTextarea.value = descripcion;
            hiddenIdInput.value = trabajoId;

            // Desmarcar todos los checkboxes de programas
            programasCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Marcar los checkboxes de los programas usados
            programas.forEach(programa => {
                programasCheckboxes.forEach(checkbox => {
                    if (checkbox.value === programa) {
                        checkbox.checked = true;
                    }
                });
            });

            // Cambiar el texto del botón y la acción del formulario
            submitButton.textContent = 'Actualizar Proyecto';
            uploadForm.action = `../api/trabajos/${trabajoId}`; // Apuntar a la API de actualización
            uploadForm.method = 'POST'; // Usar POST para enviar el formulario, la API lo manejará como PUT

            // Mostrar el formulario de subida/edición
            document.getElementById('uploadFormContainer').style.display = 'block';
        });
    });
});