// js/restablecer.js
// Este script se encarga de la validación del lado del cliente
// para el formulario de restablecimiento de contraseña.
// Asegura que las contraseñas ingresadas coincidan antes de enviar el formulario.

// Añade un 'event listener' al formulario con el ID 'formRestablecer'.
// Se activa cuando el formulario intenta ser enviado (evento 'submit').
document.getElementById('formRestablecer').addEventListener('submit', (e) => {
    // Obtiene el valor del campo "Nueva Contraseña".
    const nueva = document.getElementById('nuevaPassword').value;
    // Obtiene el valor del campo "Confirmar Nueva Contraseña".
    const confirmar = document.getElementById('confirmarPassword').value;
    // Obtiene la referencia al elemento donde se mostrarán los mensajes al cliente.
    const mensaje = document.getElementById('mensaje_cliente');

    // Comprueba si las contraseñas no coinciden.
    if (nueva !== confirmar) {
        e.preventDefault(); // Previene el envío del formulario si las contraseñas no coinciden.
        mensaje.textContent = "Las contraseñas no coinciden"; // Establece el mensaje de error.
        mensaje.style.display = 'block'; // Hace visible el mensaje de error.
    } else {
        // Si las contraseñas coinciden, asegura que el mensaje de error esté oculto.
        // Esto es útil si el usuario corrigió un error previo.
        mensaje.textContent = "";
        mensaje.style.display = 'none';
    }
});