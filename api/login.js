// js/login.js
// Este script maneja la interactividad de la página de login,
// específicamente la apertura y cierre de los modales de registro
// y recuperación de contraseña.

// --- Referencias a los elementos del DOM ---

// Referencias a los elementos de los modales (ventanas emergentes).
// Se obtienen por su ID.
const modalRegistro = document.getElementById('modalRegistro');
const modalRecuperar = document.getElementById('modalRecuperar');

// Referencias a los enlaces que activan la apertura de los modales.
// Se obtienen por su clase CSS.
const btnRecuperar = document.querySelector('.abrir-modal-recuperar');
const btnRegistro = document.querySelector('.abrir-modal-registro');

// Referencias a los elementos (span) que cierran los modales.
// Se obtienen por su clase CSS.
const spanRegistro = document.querySelector('.cerrarRegistro');
const spanRecuperar = document.querySelector('.cerrarRecuperar');

// --- Event Listeners para abrir modales ---

// Cuando se hace clic en el botón/enlace "Crear Nueva cuenta":
btnRegistro.addEventListener('click', () => {
    // Añade la clase 'show' al modal de registro para hacerlo visible.
    modalRegistro.classList.add('show');
});

// Cuando se hace clic en el botón/enlace "Recuperar contraseña":
btnRecuperar.addEventListener('click', () => {
    // Añade la clase 'show' al modal de recuperación para hacerlo visible.
    modalRecuperar.classList.add('show');
});

// --- Event Listeners para cerrar modales ---

// Cuando se hace clic en el span de cerrar del modal de registro:
spanRegistro.onclick = function(){
    // Elimina la clase 'show' del modal de registro para ocultarlo.
    modalRegistro.classList.remove('show');
}

// Cuando se hace clic en el span de cerrar del modal de recuperación:
spanRecuperar.addEventListener('click', () => {
    // Elimina la clase 'show' del modal de recuperación para ocultarlo.
    modalRecuperar.classList.remove('show');
});

// --- Event Listener para cerrar modales al hacer clic fuera ---

// Cuando se hace clic en cualquier parte de la ventana:
window.onclick = function(event){
    // Si el clic fue directamente sobre el fondo del modal de registro,
    // significa que el usuario hizo clic fuera del contenido del modal.
    if(event.target === modalRegistro){
        modalRegistro.classList.remove('show'); // Oculta el modal de registro.
    }
    // Si el clic fue directamente sobre el fondo del modal de recuperación,
    // significa que el usuario hizo clic fuera del contenido del modal.
    if(event.target === modalRecuperar){
        modalRecuperar.classList.remove('show'); // Oculta el modal de recuperación.
    }
}